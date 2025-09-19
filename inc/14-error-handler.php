<?php
/**
 * GI Error Handler & Fallback System
 * 
 * 高度なエラーハンドリングとフォールバック機能
 * - 階層的エラー処理
 * - 自動復旧機能
 * - フォールバック応答システム
 * - エラー分析・レポート
 * 
 * @package Grant_Insight_ErrorHandler
 * @version 1.0.0
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

/**
 * GI Error Handler Class
 */
class GI_Error_Handler {
    
    private static $instance = null;
    private $error_log = array();
    private $fallback_responses = array();
    private $retry_config = array();
    private $circuit_breakers = array();
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_error_handling();
        $this->load_fallback_responses();
        $this->init_retry_config();
        $this->init_circuit_breakers();
    }
    
    /**
     * エラーハンドリングの初期化
     */
    private function init_error_handling() {
        // PHPエラーハンドラーの設定
        set_error_handler(array($this, 'handle_php_error'));
        set_exception_handler(array($this, 'handle_exception'));
        
        // WordPressフック
        add_action('wp_ajax_gi_ai_request', array($this, 'handle_ai_request_with_fallback'));
        add_action('wp_ajax_nopriv_gi_ai_request', array($this, 'handle_ai_request_with_fallback'));
        
        // シャットダウンハンドラー
        register_shutdown_function(array($this, 'handle_shutdown'));
    }
    
    /**
     * フォールバック応答の読み込み
     */
    private function load_fallback_responses() {
        $this->fallback_responses = array(
            'consultation' => array(
                'general' => '申し訳ございません。現在AIサービスが一時的に利用できません。しばらく経ってから再度お試しください。緊急の場合は、お電話でお問い合わせください。',
                'search_help' => '検索機能が一時的に利用できません。以下の人気カテゴリから探してみてください：製造業、IT・テクノロジー、医療・介護、サービス業',
                'grant_info' => '助成金情報の取得に問題が発生しています。最新の助成金情報については、公式サイトをご確認いただくか、直接お問い合わせください。',
                'technical_support' => 'システムに技術的な問題が発生しています。開発チームに報告済みです。ご不便をおかけして申し訳ありません。'
            ),
            'search' => array(
                'results' => array(),
                'message' => '検索サービスが一時的に利用できません。',
                'suggestions' => array(
                    'IT導入補助金',
                    '小規模事業者持続化補助金',
                    'ものづくり補助金',
                    '事業再構築補助金'
                )
            ),
            'error_codes' => array(
                '500' => 'サーバー内部エラーが発生しました。',
                '502' => 'サーバーの応答に問題があります。',
                '503' => 'サービスが一時的に利用できません。',
                '504' => 'サーバーの応答時間が遅延しています。',
                'timeout' => '処理がタイムアウトしました。',
                'network' => 'ネットワーク接続に問題があります。',
                'rate_limit' => 'リクエスト数の制限に達しました。しばらく待ってから再度お試しください。'
            )
        );
    }
    
    /**
     * リトライ設定の初期化
     */
    private function init_retry_config() {
        $this->retry_config = array(
            'max_retries' => 3,
            'base_delay' => 1000, // ミリ秒
            'max_delay' => 10000,  // ミリ秒
            'backoff_multiplier' => 2,
            'jitter' => true
        );
    }
    
    /**
     * サーキットブレーカーの初期化
     */
    private function init_circuit_breakers() {
        $this->circuit_breakers = array(
            'ai_api' => array(
                'state' => 'closed', // closed, open, half-open
                'failure_count' => 0,
                'failure_threshold' => 5,
                'recovery_timeout' => 60, // 秒
                'last_failure_time' => 0,
                'success_count' => 0,
                'success_threshold' => 3
            ),
            'database' => array(
                'state' => 'closed',
                'failure_count' => 0,
                'failure_threshold' => 3,
                'recovery_timeout' => 30,
                'last_failure_time' => 0,
                'success_count' => 0,
                'success_threshold' => 2
            )
        );
    }
    
    /**
     * 堅牢なAJAXリクエスト処理
     */
    public function make_robust_ajax_request($request_data, $type, $retries = null) {
        $retries = $retries ?? $this->retry_config['max_retries'];
        $delay = $this->retry_config['base_delay'];
        
        for ($attempt = 1; $attempt <= $retries; $attempt++) {
            try {
                // サーキットブレーカーのチェック
                if (!$this->is_circuit_breaker_open($type)) {
                    $response = $this->execute_request($request_data, $type);
                    
                    if ($response && isset($response['success']) && $response['success']) {
                        $this->record_success($type);
                        return $response;
                    } else {
                        throw new Exception($response['data'] ?? 'Request failed');
                    }
                } else {
                    throw new Exception('Circuit breaker is open for ' . $type);
                }
                
            } catch (Exception $error) {
                $this->record_failure($type, $error->getMessage());
                
                if ($attempt === $retries) {
                    // 最終フォールバック
                    return $this->get_fallback_response($request_data, $type, $error->getMessage());
                }
                
                // 指数バックオフ with ジッター
                $actual_delay = $this->calculate_backoff_delay($delay, $attempt);
                usleep($actual_delay * 1000); // マイクロ秒に変換
                
                $delay *= $this->retry_config['backoff_multiplier'];
                if ($delay > $this->retry_config['max_delay']) {
                    $delay = $this->retry_config['max_delay'];
                }
            }
        }
        
        return $this->get_fallback_response($request_data, $type, 'Max retries exceeded');
    }
    
    /**
     * バックオフ遅延の計算
     */
    private function calculate_backoff_delay($base_delay, $attempt) {
        $delay = $base_delay * pow($this->retry_config['backoff_multiplier'], $attempt - 1);
        
        if ($this->retry_config['jitter']) {
            // ジッターを追加 (±25%)
            $jitter = ($delay * 0.25) * (mt_rand() / mt_getrandmax() * 2 - 1);
            $delay += $jitter;
        }
        
        return min($delay, $this->retry_config['max_delay']);
    }
    
    /**
     * リクエストの実行
     */
    private function execute_request($request_data, $type) {
        // タイプに応じた処理
        switch ($type) {
            case 'ai_consultation':
                return $this->process_ai_consultation($request_data);
            case 'ai_search':
                return $this->process_ai_search($request_data);
            case 'ai_analysis':
                return $this->process_ai_analysis($request_data);
            default:
                throw new Exception('Unknown request type: ' . $type);
        }
    }
    
    /**
     * AI相談処理（仮実装）
     */
    private function process_ai_consultation($request_data) {
        // 現在は安全なフォールバック応答を返す
        $message = sanitize_textarea_field($request_data['message'] ?? '');
        
        if (empty($message)) {
            throw new Exception('Empty message');
        }
        
        // 簡単なキーワードベースの応答
        $response = $this->generate_keyword_response($message);
        
        return array(
            'success' => true,
            'data' => array(
                'response' => $response,
                'timestamp' => time(),
                'method' => 'keyword_based'
            )
        );
    }
    
    /**
     * AI検索処理（仮実装）
     */
    private function process_ai_search($request_data) {
        $query = sanitize_text_field($request_data['query'] ?? '');
        
        if (empty($query)) {
            throw new Exception('Empty search query');
        }
        
        // 基本的なデータベース検索
        $results = $this->search_grants_database($query);
        
        return array(
            'success' => true,
            'data' => array(
                'results' => $results,
                'query' => $query,
                'method' => 'database_search'
            )
        );
    }
    
    /**
     * キーワードベース応答生成
     */
    private function generate_keyword_response($message) {
        $message = mb_strtolower($message);
        
        $keyword_responses = array(
            '補助金' => 'IT導入補助金や事業再構築補助金など、多数の補助金制度があります。貴社の事業内容に応じて最適なものをご提案できます。',
            '助成金' => '雇用関連の助成金から技術開発助成金まで、様々な助成金制度が利用可能です。詳細な要件をお聞かせください。',
            'IT' => 'IT導入補助金が最適です。ソフトウェア導入やクラウド利用料の一部が補助対象となります。',
            '製造業' => 'ものづくり補助金や設備投資関連の支援制度が豊富にあります。',
            '起業' => '創業支援補助金や新規事業支援制度をご検討ください。',
            '雇用' => 'キャリアアップ助成金や人材確保等支援助成金などがあります。'
        );
        
        foreach ($keyword_responses as $keyword => $response) {
            if (strpos($message, $keyword) !== false) {
                return $response;
            }
        }
        
        return 'ご質問の内容について、詳しくお聞かせください。より具体的なアドバイスを提供できます。';
    }
    
    /**
     * 助成金データベース検索
     */
    private function search_grants_database($query) {
        global $wpdb;
        
        try {
            $table_name = $wpdb->prefix . 'posts';
            $query_sql = $wpdb->prepare(
                "SELECT ID, post_title, post_excerpt FROM {$table_name} 
                WHERE post_type = 'grant' 
                AND post_status = 'publish' 
                AND (post_title LIKE %s OR post_content LIKE %s) 
                LIMIT 10",
                '%' . $wpdb->esc_like($query) . '%',
                '%' . $wpdb->esc_like($query) . '%'
            );
            
            $results = $wpdb->get_results($query_sql, ARRAY_A);
            
            if (!$results) {
                return array();
            }
            
            return array_map(function($row) {
                return array(
                    'id' => $row['ID'],
                    'title' => $row['post_title'],
                    'excerpt' => $row['post_excerpt'] ?: wp_trim_words(get_post_field('post_content', $row['ID']), 30),
                    'url' => get_permalink($row['ID'])
                );
            }, $results);
            
        } catch (Exception $e) {
            throw new Exception('Database search failed: ' . $e->getMessage());
        }
    }
    
    /**
     * サーキットブレーカーの状態チェック
     */
    private function is_circuit_breaker_open($type) {
        if (!isset($this->circuit_breakers[$type])) {
            return false;
        }
        
        $breaker = &$this->circuit_breakers[$type];
        $current_time = time();
        
        switch ($breaker['state']) {
            case 'open':
                if ($current_time - $breaker['last_failure_time'] >= $breaker['recovery_timeout']) {
                    $breaker['state'] = 'half-open';
                    $breaker['success_count'] = 0;
                    return false;
                }
                return true;
                
            case 'half-open':
                return false;
                
            case 'closed':
            default:
                return false;
        }
    }
    
    /**
     * 成功の記録
     */
    private function record_success($type) {
        if (!isset($this->circuit_breakers[$type])) {
            return;
        }
        
        $breaker = &$this->circuit_breakers[$type];
        
        switch ($breaker['state']) {
            case 'half-open':
                $breaker['success_count']++;
                if ($breaker['success_count'] >= $breaker['success_threshold']) {
                    $breaker['state'] = 'closed';
                    $breaker['failure_count'] = 0;
                }
                break;
                
            case 'closed':
                $breaker['failure_count'] = max(0, $breaker['failure_count'] - 1);
                break;
        }
    }
    
    /**
     * 失敗の記録
     */
    private function record_failure($type, $error_message) {
        // エラーログに記録
        $this->log_error($type, $error_message);
        
        // サーキットブレーカーの更新
        if (!isset($this->circuit_breakers[$type])) {
            return;
        }
        
        $breaker = &$this->circuit_breakers[$type];
        $breaker['failure_count']++;
        $breaker['last_failure_time'] = time();
        
        if ($breaker['failure_count'] >= $breaker['failure_threshold']) {
            $breaker['state'] = 'open';
        }
        
        if ($breaker['state'] === 'half-open') {
            $breaker['state'] = 'open';
        }
    }
    
    /**
     * フォールバック応答の取得
     */
    public function get_fallback_response($request_data, $type, $error_message = '') {
        $fallback = array(
            'success' => true,
            'data' => array(),
            'fallback' => true,
            'error_type' => $type,
            'timestamp' => time()
        );
        
        switch ($type) {
            case 'ai_consultation':
                $fallback['data'] = array(
                    'response' => $this->fallback_responses['consultation']['general'],
                    'suggestions' => array(
                        '具体的な業種や事業内容をお聞かせください',
                        '予算規模はどのくらいでしょうか',
                        'どのような用途での利用をお考えですか'
                    )
                );
                break;
                
            case 'ai_search':
                $fallback['data'] = $this->fallback_responses['search'];
                break;
                
            case 'rate_limit':
                $fallback['data'] = array(
                    'response' => $this->fallback_responses['error_codes']['rate_limit']
                );
                break;
                
            default:
                $fallback['data'] = array(
                    'response' => $this->fallback_responses['consultation']['technical_support']
                );
        }
        
        return $fallback;
    }
    
    /**
     * エラーログ記録
     */
    private function log_error($type, $message, $context = array()) {
        $error_entry = array(
            'type' => $type,
            'message' => $message,
            'context' => $context,
            'timestamp' => time(),
            'user_id' => get_current_user_id(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? ''
        );
        
        $this->error_log[] = $error_entry;
        
        // WordPressエラーログに記録
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[GI Error Handler] ' . $type . ': ' . $message . ' | Context: ' . json_encode($context));
        }
        
        // 重要なエラーは即座に保存
        if (in_array($type, array('ai_consultation', 'ai_search', 'database'))) {
            $this->save_error_logs();
        }
    }
    
    /**
     * PHPエラーハンドラー
     */
    public function handle_php_error($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        $error_types = array(
            E_ERROR => 'Fatal Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict Notice',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated'
        );
        
        $error_type = $error_types[$errno] ?? 'Unknown Error';
        
        $this->log_error('php_error', $errstr, array(
            'type' => $error_type,
            'file' => $errfile,
            'line' => $errline,
            'errno' => $errno
        ));
        
        return false; // PHPの内部エラーハンドラーも実行させる
    }
    
    /**
     * 例外ハンドラー
     */
    public function handle_exception($exception) {
        $this->log_error('exception', $exception->getMessage(), array(
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ));
    }
    
    /**
     * シャットダウンハンドラー
     */
    public function handle_shutdown() {
        $error = error_get_last();
        if ($error && in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING))) {
            $this->log_error('fatal_error', $error['message'], array(
                'file' => $error['file'],
                'line' => $error['line']
            ));
        }
        
        // 終了時にエラーログを保存
        $this->save_error_logs();
    }
    
    /**
     * エラーログの保存
     */
    private function save_error_logs() {
        if (!empty($this->error_log)) {
            $existing_logs = get_option('gi_error_logs', array());
            $all_logs = array_merge($existing_logs, $this->error_log);
            
            // 最新の500件のみ保持
            if (count($all_logs) > 500) {
                $all_logs = array_slice($all_logs, -500);
            }
            
            update_option('gi_error_logs', $all_logs);
            $this->error_log = array(); // リセット
        }
    }
    
    /**
     * AIリクエストのフォールバック処理
     */
    public function handle_ai_request_with_fallback() {
        try {
            // セキュリティチェック
            $security = gi_get_security_manager();
            if (!$security->verify_csrf_token()) {
                throw new Exception('Security check failed');
            }
            
            if (!$security->validate_api_rate_limit()) {
                wp_send_json_error($this->get_fallback_response(array(), 'rate_limit'));
                return;
            }
            
            // リクエストデータの取得
            $message = $security->sanitize_ai_input($_POST['message'] ?? '');
            $type = sanitize_text_field($_POST['type'] ?? 'consultation');
            
            $request_data = array(
                'message' => $message,
                'type' => $type,
                'user_id' => get_current_user_id(),
                'timestamp' => time()
            );
            
            // 堅牢なリクエスト処理
            $response = $this->make_robust_ajax_request($request_data, 'ai_' . $type);
            
            wp_send_json_success($response['data']);
            
        } catch (Exception $e) {
            $fallback = $this->get_fallback_response(array(), 'ai_consultation', $e->getMessage());
            wp_send_json_success($fallback['data']);
        }
    }
    
    /**
     * エラー統計の取得
     */
    public function get_error_stats() {
        $logs = get_option('gi_error_logs', array());
        $stats = array(
            'total_errors' => count($logs),
            'errors_today' => 0,
            'top_error_types' => array(),
            'circuit_breakers' => $this->circuit_breakers
        );
        
        $today = date('Y-m-d');
        $error_counts = array();
        
        foreach ($logs as $log) {
            if (date('Y-m-d', $log['timestamp']) === $today) {
                $stats['errors_today']++;
            }
            
            $error_counts[$log['type']] = ($error_counts[$log['type']] ?? 0) + 1;
        }
        
        arsort($error_counts);
        $stats['top_error_types'] = array_slice($error_counts, 0, 5, true);
        
        return $stats;
    }
}

/**
 * グローバル関数
 */
if (!function_exists('gi_get_error_handler')) {
    function gi_get_error_handler() {
        return GI_Error_Handler::getInstance();
    }
}

// 初期化
add_action('init', function() {
    GI_Error_Handler::getInstance();
});