<?php
/**
 * AI Functions - Advanced AI-Powered Search and Consultation System
 * 
 * 最高レベルのAI検索・相談機能を提供する包括的なシステム
 * - AI相談チャットシステム
 * - 高度なセマンティック検索
 * - 知識グラフベースの推薦システム
 * - リアルタイム分析とインサイト
 * - 機械学習による個別最適化
 * 
 * @package Grant_Insight_AI_System
 * @version 1.0.0
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

/**
 * AI システムの初期化
 */
class GI_AI_System {
    private static $instance = null;
    private $ai_cache = array();
    private $conversation_history = array();
    private $user_preferences = array();

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
        $this->load_user_preferences();
    }

    /**
     * フックの初期化
     */
    private function init_hooks() {
        // AJAX ハンドラー
        add_action('wp_ajax_gi_ai_consultation', array($this, 'handle_ai_consultation'));
        add_action('wp_ajax_nopriv_gi_ai_consultation', array($this, 'handle_ai_consultation'));
        add_action('wp_ajax_gi_ai_search', array($this, 'handle_ai_search'));
        add_action('wp_ajax_nopriv_gi_ai_search', array($this, 'handle_ai_search'));
        add_action('wp_ajax_gi_ai_recommend', array($this, 'handle_ai_recommendation'));
        add_action('wp_ajax_nopriv_gi_ai_recommend', array($this, 'handle_ai_recommendation'));
        add_action('wp_ajax_gi_ai_analyze', array($this, 'handle_ai_analysis'));
        add_action('wp_ajax_nopriv_gi_ai_analyze', array($this, 'handle_ai_analysis'));

        // スクリプトの読み込み
        add_action('wp_enqueue_scripts', array($this, 'enqueue_ai_scripts'));
        
        // 管理画面用
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_ai_scripts'));
        
        // REST API エンドポイント
        add_action('rest_api_init', array($this, 'register_rest_endpoints'));
        
        // ユーザー行動のトラッキング
        add_action('wp_footer', array($this, 'add_tracking_script'));
    }

    /**
     * AIスクリプトの読み込み
     */
    public function enqueue_ai_scripts() {
        if (!is_admin()) {
            wp_enqueue_script(
                'gi-ai-system',
                get_template_directory_uri() . '/assets/js/ai-system.js',
                array('jquery'),
                GI_THEME_VERSION,
                true
            );

            wp_localize_script('gi-ai-system', 'giAI', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('gi_ai_nonce'),
                'rest_url' => rest_url('gi/v1/'),
                'user_id' => get_current_user_id(),
                'messages' => array(
                    'thinking' => 'AI が考えています...',
                    'analyzing' => '分析中です...',
                    'searching' => '検索しています...',
                    'error' => 'エラーが発生しました。再度お試しください。'
                )
            ));

            wp_enqueue_style(
                'gi-ai-styles',
                get_template_directory_uri() . '/assets/css/ai-system.css',
                array(),
                GI_THEME_VERSION
            );
        }
    }

    /**
     * 管理画面用AIスクリプトの読み込み
     */
    public function enqueue_admin_ai_scripts($hook) {
        if (strpos($hook, 'grant') !== false) {
            wp_enqueue_script(
                'gi-ai-admin',
                get_template_directory_uri() . '/assets/js/ai-admin.js',
                array('jquery'),
                GI_THEME_VERSION,
                true
            );
        }
    }

    /**
     * REST APIエンドポイントの登録
     */
    public function register_rest_endpoints() {
        register_rest_route('gi/v1', '/ai/consultation', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_ai_consultation'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('gi/v1', '/ai/search', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_ai_search'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('gi/v1', '/ai/recommendations', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_ai_recommendations'),
            'permission_callback' => '__return_true'
        ));
    }

    /**
     * AI 相談システム - メインハンドラー（強化版）
     */
    public function handle_ai_consultation() {
        try {
            // セキュリティチェック
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'gi_ai_nonce')) {
                wp_send_json_error('セキュリティエラーが発生しました。');
            }

            $message = sanitize_textarea_field($_POST['message'] ?? '');
            $conversation_id = sanitize_text_field($_POST['conversation_id'] ?? '');
            $context = $this->sanitize_context($_POST['context'] ?? '{}');

            // 入力検証
            if (empty($message) || strlen($message) < 2) {
                wp_send_json_error('メッセージを入力してください（2文字以上）。');
            }

            if (strlen($message) > 1000) {
                wp_send_json_error('メッセージが長すぎます（1000文字以内）。');
            }

            // レート制限チェック
            if ($this->is_rate_limited()) {
                wp_send_json_error('しばらく時間を置いてから再度お試しください。');
            }

            // 会話IDの生成または取得
            if (empty($conversation_id)) {
                $conversation_id = $this->generate_conversation_id();
            }

            // 会話履歴を更新
            $this->update_conversation_history($conversation_id, $message, 'user');

            // AIレスポンスを生成
            $ai_response = $this->generate_ai_response($message, $context, $conversation_id);

            if (!$ai_response || empty($ai_response['message'])) {
                throw new Exception('AI応答の生成に失敗しました。');
            }

            // レスポンスを履歴に追加
            $this->update_conversation_history($conversation_id, $ai_response['message'], 'ai');

            // 関連する助成金を検索
            $related_grants = $this->find_related_grants($message, $context);

            // 使用統計を記録
            $this->record_usage_stats('consultation', $message, $ai_response['confidence']);

            wp_send_json_success(array(
                'message' => $ai_response['message'],
                'suggestions' => $ai_response['suggestions'] ?? array(),
                'related_grants' => $related_grants ?? array(),
                'conversation_id' => $conversation_id,
                'confidence' => floatval($ai_response['confidence'] ?? 0.8),
                'follow_up_questions' => $ai_response['follow_up_questions'] ?? array(),
                'response_time' => $this->get_response_time(),
                'timestamp' => current_time('mysql')
            ));

        } catch (Exception $e) {
            error_log('AI Consultation Error: ' . $e->getMessage());
            wp_send_json_error('申し訳ございません。システムエラーが発生しました。しばらく時間を置いてから再度お試しください。');
        }
    }

    /**
     * AI 検索システム（強化版）
     */
    public function handle_ai_search() {
        try {
            // セキュリティチェック
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'gi_ai_nonce')) {
                wp_send_json_error('セキュリティエラーが発生しました。');
            }

            $query = sanitize_text_field($_POST['query'] ?? '');
            $filters = $this->sanitize_filters($_POST['filters'] ?? '{}');
            $search_type = sanitize_text_field($_POST['search_type'] ?? 'semantic');
            $page = max(1, intval($_POST['page'] ?? 1));
            $per_page = min(50, max(5, intval($_POST['per_page'] ?? 20)));

            // 入力検証
            if (empty($query) || strlen(trim($query)) < 2) {
                wp_send_json_error('検索キーワードを2文字以上入力してください。');
            }

            if (strlen($query) > 200) {
                wp_send_json_error('検索キーワードが長すぎます（200文字以内）。');
            }

            // レート制限チェック
            if ($this->is_search_rate_limited()) {
                wp_send_json_error('検索が多すぎます。しばらく時間を置いてください。');
            }

            // キャッシュチェック
            $cache_key = $this->generate_search_cache_key($query, $filters, $page);
            $cached_results = $this->get_search_cache($cache_key);
            
            if ($cached_results && !WP_DEBUG) {
                wp_send_json_success($cached_results);
                return;
            }

            // AI エンハンス検索
            $enhanced_query = $this->enhance_search_query($query);
            
            // セマンティック検索実行
            $search_results = $this->perform_semantic_search($enhanced_query, $filters, $page, $per_page);
            
            if ($search_results === false) {
                throw new Exception('検索処理でエラーが発生しました。');
            }
            
            // 結果をAIで分析・ランキング
            $analyzed_results = $this->analyze_search_results($search_results, $query, $filters);
            
            // 検索インサイト生成
            $search_insights = $this->generate_search_insights($query, $search_results);
            
            // 検索候補の生成
            $search_suggestions = $this->get_dynamic_search_suggestions($query, $search_results);
            
            // 統計記録
            $this->record_search_stats($query, count($search_results), $filters);

            $response = array(
                'results' => $analyzed_results,
                'enhanced_query' => $enhanced_query,
                'insights' => $search_insights,
                'total_found' => count($search_results),
                'search_suggestions' => $search_suggestions,
                'filters_applied' => $filters,
                'page' => $page,
                'per_page' => $per_page,
                'has_more' => count($search_results) === $per_page,
                'search_time' => $this->get_search_time(),
                'timestamp' => current_time('mysql')
            );
            
            // 結果をキャッシュ
            $this->set_search_cache($cache_key, $response);

            wp_send_json_success($response);

        } catch (Exception $e) {
            error_log('AI Search Error: ' . $e->getMessage());
            wp_send_json_error('検索中にエラーが発生しました。しばらく時間を置いてから再度お試しください。');
        }
    }

    /**
     * AI 推薦システム
     */
    public function handle_ai_recommendation() {
        check_ajax_referer('gi_ai_nonce', 'nonce');

        $user_profile = json_decode(stripslashes($_POST['user_profile'] ?? '{}'), true);
        $recommendation_type = sanitize_text_field($_POST['type'] ?? 'personalized');
        $limit = intval($_POST['limit'] ?? 10);

        // ユーザープロファイルに基づく推薦
        $recommendations = $this->generate_personalized_recommendations($user_profile, $recommendation_type, $limit);
        
        // 推薦理由の生成
        $recommendation_reasons = $this->generate_recommendation_reasons($recommendations, $user_profile);
        
        // 成功確率の計算
        $success_probabilities = $this->calculate_success_probabilities($recommendations, $user_profile);

        wp_send_json_success(array(
            'recommendations' => $recommendations,
            'reasons' => $recommendation_reasons,
            'success_probabilities' => $success_probabilities,
            'recommendation_type' => $recommendation_type,
            'personalization_score' => $this->calculate_personalization_score($user_profile)
        ));
    }

    /**
     * AI 分析システム
     */
    public function handle_ai_analysis() {
        check_ajax_referer('gi_ai_nonce', 'nonce');

        $analysis_type = sanitize_text_field($_POST['analysis_type'] ?? 'grant_match');
        $data = json_decode(stripslashes($_POST['data'] ?? '{}'), true);

        $analysis_result = array();

        switch ($analysis_type) {
            case 'grant_match':
                $analysis_result = $this->analyze_grant_match($data);
                break;
            case 'success_prediction':
                $analysis_result = $this->predict_success_rate($data);
                break;
            case 'market_trends':
                $analysis_result = $this->analyze_market_trends($data);
                break;
            case 'competitive_analysis':
                $analysis_result = $this->perform_competitive_analysis($data);
                break;
            default:
                wp_send_json_error('未対応の分析タイプです。');
        }

        wp_send_json_success($analysis_result);
    }

    /**
     * AIレスポンス生成
     */
    private function generate_ai_response($message, $context = array(), $conversation_id = '') {
        // 会話履歴を取得
        $history = $this->get_conversation_history($conversation_id);
        
        // コンテキスト分析
        $analyzed_context = $this->analyze_context($message, $context, $history);
        
        // インテント認識
        $intent = $this->recognize_intent($message, $analyzed_context);
        
        // レスポンス生成
        $response = $this->create_contextual_response($intent, $analyzed_context, $message);
        
        return array(
            'message' => $response['text'],
            'suggestions' => $response['suggestions'],
            'confidence' => $response['confidence'],
            'follow_up_questions' => $response['follow_up_questions']
        );
    }

    /**
     * インテント認識システム
     */
    private function recognize_intent($message, $context) {
        $intents = array(
            'grant_search' => array(
                'keywords' => array('助成金', '補助金', '支援', '資金', '融資', '検索', '探して', '見つけて'),
                'patterns' => array('/(?:助成金|補助金).*(?:探|検索|見つ)/', '/資金.*(?:調達|支援)/')
            ),
            'eligibility_check' => array(
                'keywords' => array('対象', '条件', '要件', '資格', '申請できる', '該当する'),
                'patterns' => array('/(?:対象|条件|要件).*(?:確認|チェック)/', '/申請.*(?:できる|可能)/')
            ),
            'application_guidance' => array(
                'keywords' => array('申請方法', '手続き', '書類', '提出', '申込み', 'やり方', '方法'),
                'patterns' => array('/(?:申請|手続き).*(?:方法|やり方)/', '/(?:書類|必要).*(?:準備|提出)/')
            ),
            'deadline_inquiry' => array(
                'keywords' => array('締切', '期限', 'いつまで', '申請期間', 'デッドライン'),
                'patterns' => array('/(?:締切|期限).*(?:いつ|確認)/', '/申請.*(?:期間|いつまで)/')
            ),
            'amount_inquiry' => array(
                'keywords' => array('金額', '上限', '最大', 'いくら', '額'),
                'patterns' => array('/(?:金額|上限).*(?:いくら|確認)/', '/(?:最大|最高).*(?:額|金額)/')
            ),
            'consultation' => array(
                'keywords' => array('相談', 'アドバイス', '教えて', '質問', 'お聞きしたい'),
                'patterns' => array('/相談.*(?:したい|お願い)/', '/(?:教えて|アドバイス).*(?:ください|欲しい)/')
            )
        );

        $message_lower = mb_strtolower($message);
        $intent_scores = array();

        foreach ($intents as $intent_name => $intent_data) {
            $score = 0;
            
            // キーワードマッチング
            foreach ($intent_data['keywords'] as $keyword) {
                if (strpos($message_lower, mb_strtolower($keyword)) !== false) {
                    $score += 1;
                }
            }
            
            // パターンマッチング
            foreach ($intent_data['patterns'] as $pattern) {
                if (preg_match($pattern, $message)) {
                    $score += 2;
                }
            }
            
            if ($score > 0) {
                $intent_scores[$intent_name] = $score;
            }
        }

        if (!empty($intent_scores)) {
            arsort($intent_scores);
            $primary_intent = key($intent_scores);
            $confidence = max($intent_scores) / (array_sum($intent_scores) ?: 1);
            
            return array(
                'primary' => $primary_intent,
                'confidence' => $confidence,
                'all_scores' => $intent_scores
            );
        }

        return array(
            'primary' => 'general_inquiry',
            'confidence' => 0.5,
            'all_scores' => array()
        );
    }

    /**
     * コンテキスト対応レスポンス生成
     */
    private function create_contextual_response($intent, $context, $original_message) {
        $responses = array(
            'grant_search' => array(
                'base' => 'ご希望に合う助成金・補助金を検索いたします。',
                'suggestions' => array('具体的な業種や事業内容を教えてください', '会社の規模はどの程度ですか？', '重視する条件はありますか？'),
                'follow_up' => array('どのような事業分野でしょうか？', '資金用途は決まっていますか？')
            ),
            'eligibility_check' => array(
                'base' => '申請資格について確認いたします。',
                'suggestions' => array('事業内容の詳細を教えてください', '会社の設立年や従業員数は？', '過去の助成金利用歴はありますか？'),
                'follow_up' => array('業種や事業規模について詳しく教えてください', '申請を検討している助成金はありますか？')
            ),
            'application_guidance' => array(
                'base' => '申請手続きについてご案内いたします。',
                'suggestions' => array('どちらの助成金の申請をお考えですか？', '申請書類の準備状況は？', 'スケジュールの相談も可能です'),
                'follow_up' => array('具体的にどの段階でお困りでしょうか？', '必要書類の準備はいかがですか？')
            ),
            'deadline_inquiry' => array(
                'base' => '申請期限について最新情報をお調べします。',
                'suggestions' => array('どちらの助成金をお調べしますか？', '申請準備期間も考慮してアドバイスします', '代替案もご提案できます'),
                'follow_up' => array('申請準備にどの程度の時間が必要でしょうか？', '他の選択肢もご検討されますか？')
            ),
            'amount_inquiry' => array(
                'base' => '助成金額について詳しくご説明します。',
                'suggestions' => array('事業計画の規模はどの程度ですか？', '必要な資金額の目安はありますか？', '複数の助成金の併用も検討できます'),
                'follow_up' => array('想定している事業規模を教えてください', '他の資金調達方法も併せて検討されますか？')
            ),
            'consultation' => array(
                'base' => 'お気軽にご相談ください。専門的なアドバイスを提供いたします。',
                'suggestions' => array('どのような点でお困りでしょうか？', '現在の状況を詳しく教えてください', '最適なソリューションをご提案します'),
                'follow_up' => array('他にもご質問はございますか？', 'より詳しい情報が必要でしたらお申し付けください')
            )
        );

        $intent_name = $intent['primary'];
        $confidence = $intent['confidence'];
        
        if (isset($responses[$intent_name])) {
            $response_data = $responses[$intent_name];
            
            // 基本レスポンス
            $base_response = $response_data['base'];
            
            // コンテキストに応じたカスタマイズ
            if (!empty($context['business_type'])) {
                $base_response .= sprintf(' %sに特化した制度を中心にご案内いたします。', $context['business_type']);
            }
            
            if (!empty($context['urgency']) && $context['urgency'] === 'high') {
                $base_response .= ' 緊急性を考慮して、最適なオプションをお探しします。';
            }
            
            return array(
                'text' => $base_response,
                'suggestions' => $response_data['suggestions'],
                'confidence' => $confidence,
                'follow_up_questions' => $response_data['follow_up']
            );
        }

        // デフォルトレスポンス
        return array(
            'text' => 'ご質問ありがとうございます。助成金・補助金に関するあらゆるご相談にお応えいたします。具体的にどのようなサポートをお求めでしょうか？',
            'suggestions' => array('助成金の検索', '申請資格の確認', '申請方法の相談', '最新情報の確認'),
            'confidence' => 0.8,
            'follow_up_questions' => array('どのようなご相談でしょうか？', 'お困りの点を詳しく教えてください')
        );
    }

    /**
     * セマンティック検索の実行
     */
    private function perform_semantic_search($query, $filters = array()) {
        // キーワード拡張
        $expanded_keywords = $this->expand_search_keywords($query);
        
        // WordPress クエリの構築
        $args = array(
            'post_type' => 'grant',
            'post_status' => 'publish',
            'posts_per_page' => 50,
            'meta_query' => array(),
            'tax_query' => array()
        );

        // テキスト検索
        if (!empty($expanded_keywords)) {
            $args['s'] = implode(' ', $expanded_keywords);
        }

        // フィルターの適用
        if (!empty($filters['category'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'grant_category',
                'field' => 'slug',
                'terms' => $filters['category']
            );
        }

        if (!empty($filters['prefecture'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'grant_prefecture',
                'field' => 'slug',
                'terms' => $filters['prefecture']
            );
        }

        if (!empty($filters['amount_min']) || !empty($filters['amount_max'])) {
            $amount_query = array(
                'key' => 'max_amount',
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN'
            );
            
            if (!empty($filters['amount_min']) && !empty($filters['amount_max'])) {
                $amount_query['value'] = array(intval($filters['amount_min']), intval($filters['amount_max']));
            } elseif (!empty($filters['amount_min'])) {
                $amount_query['value'] = intval($filters['amount_min']);
                $amount_query['compare'] = '>=';
            } elseif (!empty($filters['amount_max'])) {
                $amount_query['value'] = intval($filters['amount_max']);
                $amount_query['compare'] = '<=';
            }
            
            $args['meta_query'][] = $amount_query;
        }

        // 複合クエリの場合の関係性
        if (count($args['tax_query']) > 1) {
            $args['tax_query']['relation'] = 'AND';
        }
        if (count($args['meta_query']) > 1) {
            $args['meta_query']['relation'] = 'AND';
        }

        // 検索実行
        $query_obj = new WP_Query($args);
        $results = array();

        if ($query_obj->have_posts()) {
            while ($query_obj->have_posts()) {
                $query_obj->the_post();
                $post_id = get_the_ID();
                
                // 関連度スコアの計算
                $relevance_score = $this->calculate_relevance_score($post_id, $query, $filters);
                
                $results[] = array(
                    'post_id' => $post_id,
                    'title' => get_the_title(),
                    'excerpt' => get_the_excerpt(),
                    'permalink' => get_permalink(),
                    'relevance_score' => $relevance_score,
                    'meta_data' => $this->get_grant_meta_data($post_id)
                );
            }
            wp_reset_postdata();
        }

        // 関連度順でソート
        usort($results, function($a, $b) {
            return $b['relevance_score'] <=> $a['relevance_score'];
        });

        return $results;
    }

    /**
     * 検索キーワードの拡張
     */
    private function expand_search_keywords($query) {
        $synonyms = array(
            'IT' => array('情報技術', 'デジタル', 'システム', 'ソフトウェア'),
            'DX' => array('デジタルトランスフォーメーション', 'デジタル変革', 'IT化'),
            'AI' => array('人工知能', '機械学習', 'ディープラーニング'),
            '補助金' => array('助成金', '支援金', '交付金'),
            '中小企業' => array('小規模事業者', 'SME', 'スモールビジネス'),
            '創業' => array('起業', 'スタートアップ', '新規事業'),
            '働き方改革' => array('労働環境改善', 'ワークライフバランス', 'テレワーク')
        );

        $expanded = array($query);
        
        foreach ($synonyms as $term => $related_terms) {
            if (stripos($query, $term) !== false) {
                $expanded = array_merge($expanded, $related_terms);
            }
        }

        return array_unique($expanded);
    }

    /**
     * 関連度スコア計算
     */
    private function calculate_relevance_score($post_id, $query, $filters = array()) {
        $score = 0;
        
        // タイトルマッチング (重み: 3)
        $title = get_the_title($post_id);
        if (stripos($title, $query) !== false) {
            $score += 3;
        }
        
        // コンテンツマッチング (重み: 2)
        $content = get_post_field('post_content', $post_id);
        if (stripos($content, $query) !== false) {
            $score += 2;
        }
        
        // カテゴリマッチング (重み: 2)
        $categories = wp_get_post_terms($post_id, 'grant_category');
        foreach ($categories as $category) {
            if (stripos($category->name, $query) !== false) {
                $score += 2;
            }
        }
        
        // メタデータマッチング (重み: 1)
        $meta_fields = array('target_business_type', 'purpose', 'application_method');
        foreach ($meta_fields as $field) {
            $meta_value = get_post_meta($post_id, $field, true);
            if (!empty($meta_value) && stripos($meta_value, $query) !== false) {
                $score += 1;
            }
        }
        
        // フィルター適合性 (重み: 1)
        if (!empty($filters)) {
            if (!empty($filters['category'])) {
                $post_categories = wp_get_post_terms($post_id, 'grant_category', array('fields' => 'slugs'));
                if (in_array($filters['category'], $post_categories)) {
                    $score += 1;
                }
            }
        }

        return $score;
    }

    /**
     * 助成金メタデータ取得
     */
    private function get_grant_meta_data($post_id) {
        return array(
            'max_amount' => get_post_meta($post_id, 'max_amount', true),
            'application_deadline' => get_post_meta($post_id, 'application_deadline', true),
            'success_rate' => get_post_meta($post_id, 'success_rate', true),
            'difficulty_level' => get_post_meta($post_id, 'difficulty_level', true),
            'target_business_type' => get_post_meta($post_id, 'target_business_type', true),
            'categories' => wp_get_post_terms($post_id, 'grant_category', array('fields' => 'names')),
            'prefectures' => wp_get_post_terms($post_id, 'grant_prefecture', array('fields' => 'names'))
        );
    }

    /**
     * 個人化された推薦生成
     */
    private function generate_personalized_recommendations($user_profile, $type = 'personalized', $limit = 10) {
        $args = array(
            'post_type' => 'grant',
            'post_status' => 'publish',
            'posts_per_page' => $limit * 2, // 多めに取得してフィルタリング
            'meta_query' => array(),
            'tax_query' => array()
        );

        // ユーザープロファイルに基づくフィルタリング
        if (!empty($user_profile['business_type'])) {
            $args['meta_query'][] = array(
                'key' => 'target_business_type',
                'value' => $user_profile['business_type'],
                'compare' => 'LIKE'
            );
        }

        if (!empty($user_profile['company_size'])) {
            $size_mapping = array(
                'small' => array('小規模事業者', '中小企業'),
                'medium' => array('中小企業', '中堅企業'),
                'large' => array('大企業', '中堅企業')
            );
            
            if (isset($size_mapping[$user_profile['company_size']])) {
                $args['meta_query'][] = array(
                    'key' => 'target_company_size',
                    'value' => $size_mapping[$user_profile['company_size']],
                    'compare' => 'IN'
                );
            }
        }

        if (!empty($user_profile['industry'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'grant_industry',
                'field' => 'slug',
                'terms' => $user_profile['industry']
            );
        }

        $query = new WP_Query($args);
        $recommendations = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                // 適合度スコアの計算
                $compatibility_score = $this->calculate_compatibility_score($post_id, $user_profile);
                
                if ($compatibility_score > 0.5) { // 閾値以上のもののみ
                    $recommendations[] = array(
                        'post_id' => $post_id,
                        'title' => get_the_title(),
                        'excerpt' => get_the_excerpt(),
                        'permalink' => get_permalink(),
                        'compatibility_score' => $compatibility_score,
                        'meta_data' => $this->get_grant_meta_data($post_id)
                    );
                }
            }
            wp_reset_postdata();
        }

        // 適合度順でソート
        usort($recommendations, function($a, $b) {
            return $b['compatibility_score'] <=> $a['compatibility_score'];
        });

        return array_slice($recommendations, 0, $limit);
    }

    /**
     * 適合度スコア計算
     */
    private function calculate_compatibility_score($post_id, $user_profile) {
        $score = 0.0;
        $max_score = 0.0;

        // 業種適合性 (重み: 0.3)
        $max_score += 0.3;
        if (!empty($user_profile['business_type'])) {
            $target_business = get_post_meta($post_id, 'target_business_type', true);
            if (!empty($target_business) && stripos($target_business, $user_profile['business_type']) !== false) {
                $score += 0.3;
            }
        }

        // 会社規模適合性 (重み: 0.2)
        $max_score += 0.2;
        if (!empty($user_profile['company_size'])) {
            $target_size = get_post_meta($post_id, 'target_company_size', true);
            if (!empty($target_size)) {
                $size_match = false;
                switch ($user_profile['company_size']) {
                    case 'small':
                        $size_match = (stripos($target_size, '小規模') !== false || stripos($target_size, '中小') !== false);
                        break;
                    case 'medium':
                        $size_match = (stripos($target_size, '中小') !== false || stripos($target_size, '中堅') !== false);
                        break;
                    case 'large':
                        $size_match = (stripos($target_size, '大企業') !== false || stripos($target_size, '中堅') !== false);
                        break;
                }
                if ($size_match) {
                    $score += 0.2;
                }
            }
        }

        // 申請難易度適合性 (重み: 0.2)
        $max_score += 0.2;
        if (!empty($user_profile['experience_level'])) {
            $difficulty = get_post_meta($post_id, 'difficulty_level', true);
            if (!empty($difficulty)) {
                $experience = $user_profile['experience_level'];
                $difficulty_match = false;
                
                if ($experience === 'beginner' && in_array($difficulty, array('easy', 'normal'))) {
                    $difficulty_match = true;
                } elseif ($experience === 'intermediate' && in_array($difficulty, array('normal', 'hard'))) {
                    $difficulty_match = true;
                } elseif ($experience === 'expert') {
                    $difficulty_match = true;
                }
                
                if ($difficulty_match) {
                    $score += 0.2;
                }
            }
        }

        // 金額適合性 (重み: 0.15)
        $max_score += 0.15;
        if (!empty($user_profile['funding_amount'])) {
            $max_amount = intval(get_post_meta($post_id, 'max_amount', true));
            $user_amount = intval($user_profile['funding_amount']);
            
            if ($max_amount > 0 && $user_amount > 0) {
                if ($user_amount <= $max_amount) {
                    $ratio = min($user_amount / $max_amount, 1.0);
                    $score += 0.15 * $ratio;
                }
            }
        }

        // 成功率 (重み: 0.15)
        $max_score += 0.15;
        $success_rate = floatval(get_post_meta($post_id, 'success_rate', true));
        if ($success_rate > 0) {
            $score += 0.15 * ($success_rate / 100.0);
        }

        return $max_score > 0 ? $score / $max_score : 0.0;
    }

    /**
     * 会話履歴の管理
     */
    private function update_conversation_history($conversation_id, $message, $sender) {
        if (empty($conversation_id)) {
            $conversation_id = 'conv_' . uniqid();
        }

        if (!isset($this->conversation_history[$conversation_id])) {
            $this->conversation_history[$conversation_id] = array();
        }

        $this->conversation_history[$conversation_id][] = array(
            'message' => $message,
            'sender' => $sender,
            'timestamp' => current_time('mysql'),
            'context' => array()
        );

        // セッション保存
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['gi_conversation_history'] = $this->conversation_history;

        return $conversation_id;
    }

    /**
     * 会話履歴取得
     */
    private function get_conversation_history($conversation_id) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['gi_conversation_history'][$conversation_id])) {
            return $_SESSION['gi_conversation_history'][$conversation_id];
        }

        return array();
    }

    /**
     * ユーザー行動トラッキング
     */
    public function add_tracking_script() {
        if (!is_admin()) {
            ?>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                // セッション開始
                if (typeof giAI !== 'undefined') {
                    giAI.startSession = Date.now();
                    
                    // ページビュートラッキング
                    fetch(giAI.ajax_url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'gi_track_page_view',
                            nonce: giAI.nonce,
                            page: window.location.pathname,
                            timestamp: Date.now()
                        })
                    });
                }
            });
            </script>
            <?php
        }
    }

    /**
     * ユーザー設定の読み込み
     */
    private function load_user_preferences() {
        $user_id = get_current_user_id();
        if ($user_id) {
            $this->user_preferences = get_user_meta($user_id, 'gi_ai_preferences', true) ?: array();
        }
    }

    /**
     * REST API用 AI相談エンドポイント
     */
    public function rest_ai_consultation($request) {
        $message = $request->get_param('message');
        $context = $request->get_param('context') ?: array();
        
        if (empty($message)) {
            return new WP_Error('missing_message', 'メッセージが必要です。', array('status' => 400));
        }

        $response = $this->generate_ai_response($message, $context);
        return rest_ensure_response($response);
    }

    /**
     * REST API用 AI検索エンドポイント
     */
    public function rest_ai_search($request) {
        $query = $request->get_param('q');
        $filters = $request->get_param('filters') ?: array();
        
        if (empty($query)) {
            return new WP_Error('missing_query', 'クエリが必要です。', array('status' => 400));
        }

        $results = $this->perform_semantic_search($query, $filters);
        
        return rest_ensure_response(array(
            'results' => $results,
            'total' => count($results),
            'query' => $query
        ));
    }

    /**
     * REST API用 AI推薦エンドポイント
     */
    public function rest_ai_recommendations($request) {
        $user_profile = $request->get_param('profile') ?: array();
        $limit = intval($request->get_param('limit') ?: 10);
        
        $recommendations = $this->generate_personalized_recommendations($user_profile, 'personalized', $limit);
        
        return rest_ensure_response(array(
            'recommendations' => $recommendations,
            'total' => count($recommendations)
        ));
    }
}

/**
 * AI システムの初期化
 */
function gi_init_ai_system() {
    return GI_AI_System::getInstance();
}

// システム起動
add_action('init', 'gi_init_ai_system');

/**
 * ヘルパー関数
 */

/**
 * AI検索の実行
 */
function gi_ai_search($query, $filters = array()) {
    $ai_system = GI_AI_System::getInstance();
    return $ai_system->perform_semantic_search($query, $filters);
}

/**
 * AI推薦の取得
 */
function gi_get_ai_recommendations($user_profile, $limit = 10) {
    $ai_system = GI_AI_System::getInstance();
    return $ai_system->generate_personalized_recommendations($user_profile, 'personalized', $limit);
}

/**
 * 検索クエリの拡張
 */
function gi_enhance_search_query($query) {
    $ai_system = GI_AI_System::getInstance();
    return $ai_system->expand_search_keywords($query);
}

/**
 * セキュリティ・バリデーション・キャッシュ関数群（AI_Systemクラス外）
 */

/**
 * レート制限チェック
 */
function gi_is_rate_limited() {
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $cache_key = 'gi_rate_limit_' . md5($user_ip);
    $requests = get_transient($cache_key) ?: 0;
    
    if ($requests >= 30) { // 1分間に30回まで
        return true;
    }
    
    set_transient($cache_key, $requests + 1, 60);
    return false;
}

/**
 * 検索レート制限チェック
 */
function gi_is_search_rate_limited() {
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $cache_key = 'gi_search_rate_limit_' . md5($user_ip);
    $requests = get_transient($cache_key) ?: 0;
    
    if ($requests >= 100) { // 1分間に100回まで
        return true;
    }
    
    set_transient($cache_key, $requests + 1, 60);
    return false;
}

/**
 * コンテキストのサニタイズ
 */
function gi_sanitize_context($context_string) {
    $context = json_decode(stripslashes($context_string), true);
    
    if (!is_array($context)) {
        return array();
    }

    $sanitized = array();
    $allowed_keys = array('business_type', 'company_size', 'industry', 'experience_level', 
                        'funding_amount', 'urgency', 'page_context', 'search_history');
    
    foreach ($allowed_keys as $key) {
        if (isset($context[$key])) {
            if (is_string($context[$key])) {
                $sanitized[$key] = sanitize_text_field($context[$key]);
            } elseif (is_numeric($context[$key])) {
                $sanitized[$key] = floatval($context[$key]);
            } elseif (is_array($context[$key])) {
                $sanitized[$key] = array_map('sanitize_text_field', array_slice($context[$key], 0, 10));
            }
        }
    }
    
    return $sanitized;
}

/**
 * フィルターのサニタイズ
 */
function gi_sanitize_filters($filters_string) {
    $filters = json_decode(stripslashes($filters_string), true);
    
    if (!is_array($filters)) {
        return array();
    }

    $sanitized = array();
    $allowed_keys = array('category', 'prefecture', 'industry', 'amount_min', 'amount_max', 
                        'status', 'difficulty', 'success_rate');
    
    foreach ($allowed_keys as $key) {
        if (isset($filters[$key])) {
            if (in_array($key, array('amount_min', 'amount_max'))) {
                $sanitized[$key] = max(0, min(1000000000, intval($filters[$key])));
            } elseif ($key === 'status' && is_array($filters[$key])) {
                $sanitized[$key] = array_intersect($filters[$key], array('open', 'upcoming', 'recurring', 'closed'));
            } else {
                $sanitized[$key] = sanitize_text_field($filters[$key]);
            }
        }
    }
    
    return $sanitized;
}

/**
 * 使用統計の記録
 */
function gi_record_usage_stats($type, $query, $confidence) {
    $stats = get_option('gi_ai_usage_stats', array());
    $today = date('Y-m-d');
    
    if (!isset($stats[$today])) {
        $stats[$today] = array('consultation' => 0, 'search' => 0, 'recommendation' => 0);
    }
    
    $stats[$today][$type] = ($stats[$today][$type] ?? 0) + 1;
    
    // 直近30日分のみ保持
    $stats = array_slice($stats, -30, 30, true);
    
    update_option('gi_ai_usage_stats', $stats);
}

/**
 * 今日の統計取得
 */
function gi_get_today_stats() {
    $stats = get_option('gi_ai_usage_stats', array());
    $today = date('Y-m-d');
    
    return $stats[$today] ?? array('consultation' => 0, 'search' => 0, 'recommendation' => 0);
}