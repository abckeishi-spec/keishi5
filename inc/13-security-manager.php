<?php
/**
 * GI Security Manager - Advanced Security & Privacy Protection
 * 
 * 高度なセキュリティ・プライバシー保護システム
 * - XSS、SQLインジェクション対策
 * - API レート制限
 * - 個人情報暗号化
 * - CSRF トークン検証
 * - セッション管理強化
 * 
 * @package Grant_Insight_Security
 * @version 1.0.0
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

/**
 * GI Security Manager Class
 */
class GI_Security_Manager {
    
    private static $instance = null;
    private $rate_limits = array();
    private $encryption_key = null;
    private $blocked_ips = array();
    private $security_logs = array();
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_security_hooks();
        $this->load_encryption_key();
        $this->load_blocked_ips();
    }
    
    /**
     * セキュリティフックの初期化
     */
    private function init_security_hooks() {
        add_action('init', array($this, 'validate_request_security'));
        add_action('wp_ajax_gi_ai_consultation', array($this, 'secure_ajax_handler'));
        add_action('wp_ajax_nopriv_gi_ai_consultation', array($this, 'secure_ajax_handler'));
        add_action('wp_ajax_gi_ai_search', array($this, 'secure_ajax_handler'));
        add_action('wp_ajax_nopriv_gi_ai_search', array($this, 'secure_ajax_handler'));
        
        // セキュリティヘッダーの設定
        add_action('send_headers', array($this, 'add_security_headers'));
        
        // 失敗ログイン監視
        add_action('wp_login_failed', array($this, 'log_failed_login'));
    }
    
    /**
     * セキュリティヘッダーの追加
     */
    public function add_security_headers() {
        if (!is_admin()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' cdn.jsdelivr.net fonts.googleapis.com; style-src \'self\' \'unsafe-inline\' fonts.googleapis.com; font-src fonts.gstatic.com; img-src \'self\' data: https:;');
        }
    }
    
    /**
     * AI入力のサニタイズ処理
     */
    public function sanitize_ai_input($input, $type = 'message') {
        if (empty($input)) {
            return '';
        }
        
        // 基本的なサニタイズ
        $sanitized = wp_strip_all_tags($input);
        $sanitized = sanitize_textarea_field($sanitized);
        
        // タイプ別の追加サニタイズ
        switch ($type) {
            case 'message':
                // チャットメッセージの場合
                $sanitized = $this->remove_malicious_patterns($sanitized);
                $sanitized = $this->limit_message_length($sanitized, 2000);
                break;
            case 'search':
                // 検索クエリの場合
                $sanitized = $this->sanitize_search_query($sanitized);
                $sanitized = $this->limit_message_length($sanitized, 500);
                break;
            case 'user_data':
                // ユーザーデータの場合
                $sanitized = sanitize_text_field($sanitized);
                break;
        }
        
        // SQLインジェクション対策
        $sanitized = $this->prevent_sql_injection($sanitized);
        
        // XSS対策の強化
        $sanitized = wp_kses($sanitized, array());
        
        return $sanitized;
    }
    
    /**
     * 悪意のあるパターンの除去
     */
    private function remove_malicious_patterns($input) {
        $malicious_patterns = array(
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
            '/onclick\s*=/i',
            '/expression\s*\(/i',
            '/eval\s*\(/i',
            '/setTimeout\s*\(/i',
            '/setInterval\s*\(/i',
        );
        
        return preg_replace($malicious_patterns, '', $input);
    }
    
    /**
     * メッセージ長の制限
     */
    private function limit_message_length($message, $max_length) {
        if (mb_strlen($message, 'UTF-8') > $max_length) {
            return mb_substr($message, 0, $max_length, 'UTF-8');
        }
        return $message;
    }
    
    /**
     * 検索クエリのサニタイズ
     */
    private function sanitize_search_query($query) {
        // 特殊文字の処理
        $query = preg_replace('/[^\w\s\p{Hiragana}\p{Katakana}\p{Han}]/u', '', $query);
        return trim($query);
    }
    
    /**
     * SQLインジェクション対策
     */
    private function prevent_sql_injection($input) {
        $sql_patterns = array(
            '/(\s|^)(union|select|insert|update|delete|drop|create|alter|exec|execute)\s/i',
            '/(\s|^)(or|and)\s+\d+\s*=\s*\d+/i',
            '/\'\s*(or|and)\s+\'/i',
            '/;\s*(drop|delete|update|insert)/i'
        );
        
        foreach ($sql_patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $this->log_security_incident('sql_injection_attempt', $input);
                return '';
            }
        }
        
        return $input;
    }
    
    /**
     * APIレート制限のチェック
     */
    public function validate_api_rate_limit($user_identifier = null, $action = 'ai_request', $limit = 30, $window = 60) {
        if (!$user_identifier) {
            $user_identifier = $this->get_user_identifier();
        }
        
        $key = $action . '_' . $user_identifier;
        $current_time = time();
        
        // 現在の制限状況を取得
        if (!isset($this->rate_limits[$key])) {
            $this->rate_limits[$key] = array(
                'requests' => array(),
                'blocked_until' => 0
            );
        }
        
        // ブロック期間中かチェック
        if ($this->rate_limits[$key]['blocked_until'] > $current_time) {
            $this->log_security_incident('rate_limit_blocked', array(
                'user_identifier' => $user_identifier,
                'action' => $action,
                'blocked_until' => $this->rate_limits[$key]['blocked_until']
            ));
            return false;
        }
        
        // 古いリクエストを削除
        $this->rate_limits[$key]['requests'] = array_filter(
            $this->rate_limits[$key]['requests'],
            function($timestamp) use ($current_time, $window) {
                return ($current_time - $timestamp) < $window;
            }
        );
        
        // リクエスト数をチェック
        if (count($this->rate_limits[$key]['requests']) >= $limit) {
            $this->rate_limits[$key]['blocked_until'] = $current_time + ($window * 2); // 2倍の期間ブロック
            $this->log_security_incident('rate_limit_exceeded', array(
                'user_identifier' => $user_identifier,
                'action' => $action,
                'requests_count' => count($this->rate_limits[$key]['requests'])
            ));
            return false;
        }
        
        // 新しいリクエストを記録
        $this->rate_limits[$key]['requests'][] = $current_time;
        return true;
    }
    
    /**
     * ユーザー識別子の取得
     */
    private function get_user_identifier() {
        if (is_user_logged_in()) {
            return 'user_' . get_current_user_id();
        } else {
            // IPアドレスとUser-Agentの組み合わせ
            $ip = $this->get_client_ip();
            $user_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '');
            return 'guest_' . md5($ip . $user_agent);
        }
    }
    
    /**
     * クライアントIPアドレスの取得
     */
    private function get_client_ip() {
        $ip_headers = array(
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );
        
        foreach ($ip_headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    
    /**
     * ユーザーデータの暗号化
     */
    public function encrypt_user_data($data) {
        if (empty($data) || !$this->encryption_key) {
            return $data;
        }
        
        try {
            if (function_exists('sodium_crypto_secretbox')) {
                $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
                $encrypted = sodium_crypto_secretbox($data, $nonce, $this->encryption_key);
                return base64_encode($nonce . $encrypted);
            } else {
                // フォールバック: OpenSSL
                $iv = openssl_random_pseudo_bytes(16);
                $encrypted = openssl_encrypt($data, 'AES-256-CBC', $this->encryption_key, 0, $iv);
                return base64_encode($iv . $encrypted);
            }
        } catch (Exception $e) {
            $this->log_security_incident('encryption_error', $e->getMessage());
            return $data; // 暗号化に失敗した場合はそのまま返す
        }
    }
    
    /**
     * ユーザーデータの復号化
     */
    public function decrypt_user_data($encrypted_data) {
        if (empty($encrypted_data) || !$this->encryption_key) {
            return $encrypted_data;
        }
        
        try {
            $data = base64_decode($encrypted_data);
            
            if (function_exists('sodium_crypto_secretbox_open')) {
                $nonce = substr($data, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
                $encrypted = substr($data, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
                return sodium_crypto_secretbox_open($encrypted, $nonce, $this->encryption_key);
            } else {
                // フォールバック: OpenSSL
                $iv = substr($data, 0, 16);
                $encrypted = substr($data, 16);
                return openssl_decrypt($encrypted, 'AES-256-CBC', $this->encryption_key, 0, $iv);
            }
        } catch (Exception $e) {
            $this->log_security_incident('decryption_error', $e->getMessage());
            return $encrypted_data;
        }
    }
    
    /**
     * CSRF トークンの検証
     */
    public function verify_csrf_token($action = 'gi_ai_action') {
        if (!isset($_POST['gi_nonce']) || !wp_verify_nonce($_POST['gi_nonce'], $action)) {
            $this->log_security_incident('csrf_token_invalid', array(
                'action' => $action,
                'user_id' => get_current_user_id(),
                'ip' => $this->get_client_ip()
            ));
            return false;
        }
        return true;
    }
    
    /**
     * セキュアなAJAXハンドラー
     */
    public function secure_ajax_handler() {
        // CSRFトークン検証
        if (!$this->verify_csrf_token()) {
            wp_send_json_error('Security check failed', 403);
            return;
        }
        
        // レート制限チェック
        if (!$this->validate_api_rate_limit()) {
            wp_send_json_error('Rate limit exceeded. Please try again later.', 429);
            return;
        }
        
        // IPブロックチェック
        if ($this->is_ip_blocked($this->get_client_ip())) {
            wp_send_json_error('Access denied', 403);
            return;
        }
        
        // 入力データのサニタイズ
        $message = $this->sanitize_ai_input($_POST['message'] ?? '', 'message');
        if (empty($message)) {
            wp_send_json_error('Invalid input', 400);
            return;
        }
        
        // ここで実際のAI処理を呼び出し
        // 現在は安全な確認メッセージを返す
        wp_send_json_success(array(
            'response' => 'セキュリティチェックが完了しました。AIシステムは正常に動作しています。',
            'timestamp' => time(),
            'secure' => true
        ));
    }
    
    /**
     * 暗号化キーの読み込み
     */
    private function load_encryption_key() {
        $key = get_option('gi_encryption_key');
        if (!$key) {
            if (function_exists('sodium_crypto_secretbox_keygen')) {
                $key = sodium_crypto_secretbox_keygen();
            } else {
                $key = openssl_random_pseudo_bytes(32);
            }
            update_option('gi_encryption_key', base64_encode($key));
        } else {
            $key = base64_decode($key);
        }
        $this->encryption_key = $key;
    }
    
    /**
     * ブロックIPの読み込み
     */
    private function load_blocked_ips() {
        $this->blocked_ips = get_option('gi_blocked_ips', array());
    }
    
    /**
     * IPブロック状況のチェック
     */
    private function is_ip_blocked($ip) {
        return in_array($ip, $this->blocked_ips);
    }
    
    /**
     * セキュリティインシデントのログ記録
     */
    private function log_security_incident($type, $data) {
        $incident = array(
            'type' => $type,
            'data' => $data,
            'timestamp' => time(),
            'ip' => $this->get_client_ip(),
            'user_id' => get_current_user_id(),
            'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'request_uri' => sanitize_text_field($_SERVER['REQUEST_URI'] ?? '')
        );
        
        $this->security_logs[] = $incident;
        
        // 重要なインシデントは即座に保存
        if (in_array($type, array('sql_injection_attempt', 'rate_limit_exceeded', 'csrf_token_invalid'))) {
            $this->save_security_logs();
        }
        
        // WordPressエラーログに記録
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[GI Security] ' . $type . ': ' . json_encode($data));
        }
    }
    
    /**
     * セキュリティログの保存
     */
    private function save_security_logs() {
        if (!empty($this->security_logs)) {
            $existing_logs = get_option('gi_security_logs', array());
            $all_logs = array_merge($existing_logs, $this->security_logs);
            
            // 最新の1000件のみ保持
            if (count($all_logs) > 1000) {
                $all_logs = array_slice($all_logs, -1000);
            }
            
            update_option('gi_security_logs', $all_logs);
            $this->security_logs = array(); // リセット
        }
    }
    
    /**
     * 失敗ログインの記録
     */
    public function log_failed_login($username) {
        $this->log_security_incident('failed_login', array(
            'username' => sanitize_user($username),
            'attempts_today' => $this->get_failed_login_count(date('Y-m-d'))
        ));
    }
    
    /**
     * 日別の失敗ログイン数取得
     */
    private function get_failed_login_count($date) {
        $logs = get_option('gi_security_logs', array());
        $count = 0;
        
        foreach ($logs as $log) {
            if ($log['type'] === 'failed_login' && 
                date('Y-m-d', $log['timestamp']) === $date) {
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * リクエストセキュリティの検証
     */
    public function validate_request_security() {
        $ip = $this->get_client_ip();
        
        // ブロックIPチェック
        if ($this->is_ip_blocked($ip)) {
            wp_die('Access denied', 'Security Error', array('response' => 403));
        }
        
        // 怪しいリクエストパターンのチェック
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $suspicious_patterns = array(
            '/\.\.\//',
            '/eval\(/i',
            '/base64_decode/i',
            '/phpinfo\(/i',
            '/system\(/i',
            '/exec\(/i',
            '/shell_exec/i'
        );
        
        foreach ($suspicious_patterns as $pattern) {
            if (preg_match($pattern, $request_uri)) {
                $this->log_security_incident('suspicious_request', array(
                    'uri' => $request_uri,
                    'pattern' => $pattern
                ));
                wp_die('Suspicious request detected', 'Security Error', array('response' => 403));
            }
        }
    }
    
    /**
     * セキュリティ統計の取得
     */
    public function get_security_stats() {
        $logs = get_option('gi_security_logs', array());
        $stats = array(
            'total_incidents' => count($logs),
            'incidents_today' => 0,
            'blocked_requests' => 0,
            'top_threats' => array()
        );
        
        $today = date('Y-m-d');
        $threat_counts = array();
        
        foreach ($logs as $log) {
            if (date('Y-m-d', $log['timestamp']) === $today) {
                $stats['incidents_today']++;
            }
            
            if (in_array($log['type'], array('rate_limit_exceeded', 'suspicious_request', 'sql_injection_attempt'))) {
                $stats['blocked_requests']++;
            }
            
            $threat_counts[$log['type']] = ($threat_counts[$log['type']] ?? 0) + 1;
        }
        
        arsort($threat_counts);
        $stats['top_threats'] = array_slice($threat_counts, 0, 5, true);
        
        return $stats;
    }
}

/**
 * グローバル関数
 */
if (!function_exists('gi_get_security_manager')) {
    function gi_get_security_manager() {
        return GI_Security_Manager::getInstance();
    }
}

// 初期化
add_action('init', function() {
    GI_Security_Manager::getInstance();
});