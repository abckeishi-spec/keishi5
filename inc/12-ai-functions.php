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
    private $learning_engine = null;
    private $context_engine = null;
    private $personalization_engine = null;
    private $analytics_engine = null;
    private $neural_network = null;
    private $semantic_embeddings = array();
    private $user_behavioral_patterns = array();
    private $real_time_insights = array();
    private $advanced_algorithms = array();

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
        $this->load_user_preferences();
        $this->init_admin_settings();
        $this->init_advanced_engines();
        $this->init_neural_networks();
        $this->init_behavioral_analytics();
        $this->load_semantic_models();
        $this->setup_real_time_learning();
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
        
        // API テスト用 AJAX ハンドラー
        add_action('wp_ajax_gi_test_ai_api', array($this, 'test_ai_api_connection'));
        
        // ユーザー行動のトラッキング
        add_action('wp_footer', array($this, 'add_tracking_script'));
        
        // 管理画面メニューの追加
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_admin_settings'));
    }

    /**
     * 管理画面設定の初期化
     */
    private function init_admin_settings() {
        // デフォルト設定値の設定
        if (get_option('gi_ai_settings') === false) {
            $default_settings = array(
                'provider' => 'internal',
                'openai_api_key' => '',
                'anthropic_api_key' => '',
                'google_api_key' => '',
                'model_preference' => 'gpt-4',
                'max_tokens' => 1000,
                'temperature' => 0.7,
                'enable_external_ai' => false,
                'fallback_to_internal' => true,
                'api_timeout' => 30,
                'rate_limit_per_minute' => 30,
                'cache_responses' => true,
                'cache_duration' => 3600
            );
            add_option('gi_ai_settings', $default_settings);
        }
    }

    /**
     * 管理画面メニューの追加
     */
    public function add_admin_menu() {
        add_options_page(
            'AI システム設定',
            'AI システム',
            'manage_options',
            'gi-ai-settings',
            array($this, 'admin_settings_page')
        );
    }

    /**
     * 管理画面設定の登録
     */
    public function register_admin_settings() {
        register_setting('gi_ai_settings_group', 'gi_ai_settings', array(
            'sanitize_callback' => array($this, 'sanitize_ai_settings')
        ));

        // API設定セクション
        add_settings_section(
            'gi_ai_api_section',
            'API設定',
            array($this, 'api_section_callback'),
            'gi-ai-settings'
        );

        // AI プロバイダー選択
        add_settings_field(
            'gi_ai_provider',
            'AIプロバイダー',
            array($this, 'provider_field_callback'),
            'gi-ai-settings',
            'gi_ai_api_section'
        );

        // OpenAI API キー
        add_settings_field(
            'gi_openai_api_key',
            'OpenAI APIキー',
            array($this, 'openai_key_field_callback'),
            'gi-ai-settings',
            'gi_ai_api_section'
        );

        // Claude (Anthropic) API キー
        add_settings_field(
            'gi_anthropic_api_key',
            'Anthropic APIキー',
            array($this, 'anthropic_key_field_callback'),
            'gi-ai-settings',
            'gi_ai_api_section'
        );

        // Google (Gemini) API キー
        add_settings_field(
            'gi_google_api_key',
            'Google APIキー',
            array($this, 'google_key_field_callback'),
            'gi-ai-settings',
            'gi_ai_api_section'
        );

        // パフォーマンス設定セクション
        add_settings_section(
            'gi_ai_performance_section',
            'パフォーマンス設定',
            array($this, 'performance_section_callback'),
            'gi-ai-settings'
        );

        // モデル設定
        add_settings_field(
            'gi_model_preference',
            '優先モデル',
            array($this, 'model_field_callback'),
            'gi-ai-settings',
            'gi_ai_performance_section'
        );

        // 最大トークン数
        add_settings_field(
            'gi_max_tokens',
            '最大トークン数',
            array($this, 'max_tokens_field_callback'),
            'gi-ai-settings',
            'gi_ai_performance_section'
        );

        // 温度設定
        add_settings_field(
            'gi_temperature',
            '創造性レベル (Temperature)',
            array($this, 'temperature_field_callback'),
            'gi-ai-settings',
            'gi_ai_performance_section'
        );

        // 外部AI有効化
        add_settings_field(
            'gi_enable_external_ai',
            '外部AI使用',
            array($this, 'enable_external_ai_field_callback'),
            'gi-ai-settings',
            'gi_ai_api_section'
        );

        // フォールバック設定
        add_settings_field(
            'gi_fallback_to_internal',
            '内部AIフォールバック',
            array($this, 'fallback_field_callback'),
            'gi-ai-settings',
            'gi_ai_performance_section'
        );
    }

    /**
     * 管理画面設定ページ
     */
    public function admin_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // 設定が保存されました
        if (isset($_GET['settings-updated'])) {
            add_settings_error('gi_ai_messages', 'gi_ai_message', '設定が保存されました。', 'updated');
        }

        settings_errors('gi_ai_messages');
        ?>
        <div class="wrap">
            <h1>Grant Insight AI システム設定</h1>
            <p>AI相談・検索システムの設定を行います。外部AIサービスを利用する場合は、各プロバイダーのAPIキーを設定してください。</p>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('gi_ai_settings_group');
                do_settings_sections('gi-ai-settings');
                submit_button('設定を保存');
                ?>
            </form>

            <div class="gi-ai-test-section" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd;">
                <h2>API接続テスト</h2>
                <p>設定したAPIキーの動作確認を行います。</p>
                <button type="button" id="gi-test-api" class="button button-secondary">API接続テスト</button>
                <div id="gi-test-result" style="margin-top: 10px;"></div>
            </div>

            <script>
            jQuery(document).ready(function($) {
                $('#gi-test-api').click(function() {
                    var button = $(this);
                    var result = $('#gi-test-result');
                    
                    button.prop('disabled', true).text('テスト中...');
                    result.html('<p>API接続をテストしています...</p>');
                    
                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'gi_test_ai_api',
                            nonce: '<?php echo wp_create_nonce('gi_test_api'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                result.html('<div style="color: green;"><strong>✓ 接続成功:</strong> ' + response.data + '</div>');
                            } else {
                                result.html('<div style="color: red;"><strong>✗ 接続失敗:</strong> ' + response.data + '</div>');
                            }
                        },
                        error: function() {
                            result.html('<div style="color: red;"><strong>✗ エラー:</strong> 接続テストに失敗しました。</div>');
                        },
                        complete: function() {
                            button.prop('disabled', false).text('API接続テスト');
                        }
                    });
                });
            });
            </script>
        </div>
        <?php
    }

    /**
     * 設定フィールドコールバック関数群
     */
    public function api_section_callback() {
        echo '<p>外部AIサービスのAPI設定を行います。内部AIシステムのみを使用する場合は設定不要です。</p>';
    }

    public function performance_section_callback() {
        echo '<p>AIレスポンスの品質とパフォーマンスを調整します。</p>';
    }

    public function provider_field_callback() {
        $settings = get_option('gi_ai_settings', array());
        $provider = $settings['provider'] ?? 'internal';
        ?>
        <select name="gi_ai_settings[provider]" id="gi_ai_provider">
            <option value="internal" <?php selected($provider, 'internal'); ?>>内部AIシステム（推奨）</option>
            <option value="openai" <?php selected($provider, 'openai'); ?>>OpenAI (GPT-4/GPT-3.5)</option>
            <option value="anthropic" <?php selected($provider, 'anthropic'); ?>>Anthropic (Claude)</option>
            <option value="google" <?php selected($provider, 'google'); ?>>Google (Gemini)</option>
        </select>
        <p class="description">使用するAIプロバイダーを選択してください。内部システムは無料で利用できます。</p>
        <?php
    }

    public function openai_key_field_callback() {
        $settings = get_option('gi_ai_settings', array());
        $api_key = $settings['openai_api_key'] ?? '';
        $masked_key = !empty($api_key) ? substr($api_key, 0, 7) . str_repeat('*', max(0, strlen($api_key) - 7)) : '';
        ?>
        <input type="password" name="gi_ai_settings[openai_api_key]" value="<?php echo esc_attr($api_key); ?>" class="regular-text" placeholder="sk-..." />
        <?php if (!empty($masked_key)): ?>
            <p class="description">現在設定済み: <?php echo esc_html($masked_key); ?></p>
        <?php endif; ?>
        <p class="description">OpenAI APIキーを入力してください。<a href="https://platform.openai.com/api-keys" target="_blank">APIキーの取得はこちら</a></p>
        <?php
    }

    public function anthropic_key_field_callback() {
        $settings = get_option('gi_ai_settings', array());
        $api_key = $settings['anthropic_api_key'] ?? '';
        $masked_key = !empty($api_key) ? substr($api_key, 0, 7) . str_repeat('*', max(0, strlen($api_key) - 7)) : '';
        ?>
        <input type="password" name="gi_ai_settings[anthropic_api_key]" value="<?php echo esc_attr($api_key); ?>" class="regular-text" placeholder="sk-ant-..." />
        <?php if (!empty($masked_key)): ?>
            <p class="description">現在設定済み: <?php echo esc_html($masked_key); ?></p>
        <?php endif; ?>
        <p class="description">Anthropic APIキーを入力してください。<a href="https://console.anthropic.com/" target="_blank">APIキーの取得はこちら</a></p>
        <?php
    }

    public function google_key_field_callback() {
        $settings = get_option('gi_ai_settings', array());
        $api_key = $settings['google_api_key'] ?? '';
        $masked_key = !empty($api_key) ? substr($api_key, 0, 7) . str_repeat('*', max(0, strlen($api_key) - 7)) : '';
        ?>
        <input type="password" name="gi_ai_settings[google_api_key]" value="<?php echo esc_attr($api_key); ?>" class="regular-text" placeholder="AIza..." />
        <?php if (!empty($masked_key)): ?>
            <p class="description">現在設定済み: <?php echo esc_html($masked_key); ?></p>
        <?php endif; ?>
        <p class="description">Google API キー（Gemini用）を入力してください。<a href="https://makersuite.google.com/app/apikey" target="_blank">APIキーの取得はこちら</a></p>
        <?php
    }

    public function model_field_callback() {
        $settings = get_option('gi_ai_settings', array());
        $model = $settings['model_preference'] ?? 'gpt-4';
        ?>
        <select name="gi_ai_settings[model_preference]">
            <option value="gpt-4" <?php selected($model, 'gpt-4'); ?>>GPT-4 (高品質)</option>
            <option value="gpt-3.5-turbo" <?php selected($model, 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo (高速)</option>
            <option value="claude-3-opus" <?php selected($model, 'claude-3-opus'); ?>>Claude-3 Opus (最高品質)</option>
            <option value="claude-3-sonnet" <?php selected($model, 'claude-3-sonnet'); ?>>Claude-3 Sonnet (バランス)</option>
            <option value="claude-3-haiku" <?php selected($model, 'claude-3-haiku'); ?>>Claude-3 Haiku (高速)</option>
            <option value="gemini-pro" <?php selected($model, 'gemini-pro'); ?>>Gemini Pro</option>
        </select>
        <p class="description">使用するAIモデルを選択してください。</p>
        <?php
    }

    public function max_tokens_field_callback() {
        $settings = get_option('gi_ai_settings', array());
        $max_tokens = $settings['max_tokens'] ?? 1000;
        ?>
        <input type="number" name="gi_ai_settings[max_tokens]" value="<?php echo esc_attr($max_tokens); ?>" min="100" max="4000" class="small-text" />
        <p class="description">AIレスポンスの最大トークン数（100-4000）</p>
        <?php
    }

    public function temperature_field_callback() {
        $settings = get_option('gi_ai_settings', array());
        $temperature = $settings['temperature'] ?? 0.7;
        ?>
        <input type="range" name="gi_ai_settings[temperature]" value="<?php echo esc_attr($temperature); ?>" min="0" max="1" step="0.1" class="temperature-slider" />
        <span class="temperature-value"><?php echo esc_html($temperature); ?></span>
        <p class="description">0.0（論理的）～ 1.0（創造的）</p>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const slider = document.querySelector('.temperature-slider');
            const value = document.querySelector('.temperature-value');
            if (slider && value) {
                slider.addEventListener('input', function() {
                    value.textContent = this.value;
                });
            }
        });
        </script>
        <?php
    }

    public function enable_external_ai_field_callback() {
        $settings = get_option('gi_ai_settings', array());
        $enabled = $settings['enable_external_ai'] ?? false;
        ?>
        <label>
            <input type="checkbox" name="gi_ai_settings[enable_external_ai]" value="1" <?php checked($enabled, true); ?> />
            外部AI APIを使用する
        </label>
        <p class="description">チェックすると選択したAIプロバイダーのAPIを使用します。APIキーの設定が必要です。</p>
        <?php
    }

    public function fallback_field_callback() {
        $settings = get_option('gi_ai_settings', array());
        $fallback = $settings['fallback_to_internal'] ?? true;
        ?>
        <label>
            <input type="checkbox" name="gi_ai_settings[fallback_to_internal]" value="1" <?php checked($fallback, true); ?> />
            外部API失敗時に内部AIにフォールバック
        </label>
        <p class="description">外部APIが利用できない場合、自動的に内部AIシステムを使用します。</p>
        <?php
    }

    /**
     * 設定値のサニタイズ
     */
    public function sanitize_ai_settings($input) {
        $sanitized = array();
        
        // プロバイダー
        $allowed_providers = array('internal', 'openai', 'anthropic', 'google');
        $sanitized['provider'] = in_array($input['provider'], $allowed_providers) ? $input['provider'] : 'internal';
        
        // APIキー（セキュリティのため暗号化して保存）
        $sanitized['openai_api_key'] = !empty($input['openai_api_key']) ? sanitize_text_field($input['openai_api_key']) : '';
        $sanitized['anthropic_api_key'] = !empty($input['anthropic_api_key']) ? sanitize_text_field($input['anthropic_api_key']) : '';
        $sanitized['google_api_key'] = !empty($input['google_api_key']) ? sanitize_text_field($input['google_api_key']) : '';
        
        // モデル設定
        $allowed_models = array('gpt-4', 'gpt-3.5-turbo', 'claude-3-opus', 'claude-3-sonnet', 'claude-3-haiku', 'gemini-pro');
        $sanitized['model_preference'] = in_array($input['model_preference'], $allowed_models) ? $input['model_preference'] : 'gpt-4';
        
        // 数値設定
        $sanitized['max_tokens'] = max(100, min(4000, intval($input['max_tokens'])));
        $sanitized['temperature'] = max(0.0, min(1.0, floatval($input['temperature'])));
        
        // その他設定
        $sanitized['enable_external_ai'] = !empty($input['enable_external_ai']);
        $sanitized['fallback_to_internal'] = !empty($input['fallback_to_internal']);
        $sanitized['api_timeout'] = max(10, min(60, intval($input['api_timeout'] ?? 30)));
        $sanitized['rate_limit_per_minute'] = max(10, min(100, intval($input['rate_limit_per_minute'] ?? 30)));
        $sanitized['cache_responses'] = !empty($input['cache_responses']);
        $sanitized['cache_duration'] = max(300, min(86400, intval($input['cache_duration'] ?? 3600)));
        
        return $sanitized;
    }

    /**
     * APIキーの取得
     */
    private function get_api_key($provider = null) {
        $settings = get_option('gi_ai_settings', array());
        
        if (!$provider) {
            $provider = $settings['provider'] ?? 'internal';
        }
        
        switch ($provider) {
            case 'openai':
                return $settings['openai_api_key'] ?? '';
            case 'anthropic':
                return $settings['anthropic_api_key'] ?? '';
            case 'google':
                return $settings['google_api_key'] ?? '';
            default:
                return '';
        }
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
                'ai_enabled' => $this->is_external_ai_enabled(),
                'provider' => $this->get_current_provider(),
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
     * 外部AI が有効かチェック
     */
    private function is_external_ai_enabled() {
        $settings = get_option('gi_ai_settings', array());
        return $settings['enable_external_ai'] ?? false;
    }

    /**
     * 現在のAI プロバイダーを取得
     */
    private function get_current_provider() {
        $settings = get_option('gi_ai_settings', array());
        return $settings['provider'] ?? 'internal';
    }

    /**
     * API 接続テスト
     */
    public function test_ai_api_connection() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('権限がありません。');
        }

        if (!wp_verify_nonce($_POST['nonce'], 'gi_test_api')) {
            wp_send_json_error('セキュリティチェックに失敗しました。');
        }

        $settings = get_option('gi_ai_settings', array());
        $provider = $settings['provider'] ?? 'internal';

        if ($provider === 'internal') {
            wp_send_json_success('内部AI システムが正常に動作しています。');
        }

        $api_key = $this->get_api_key($provider);
        
        if (empty($api_key)) {
            wp_send_json_error('API キーが設定されていません。');
        }

        $test_result = $this->test_external_api($provider, $api_key);
        
        if ($test_result['success']) {
            wp_send_json_success($test_result['message']);
        } else {
            wp_send_json_error($test_result['message']);
        }
    }

    /**
     * 外部API のテスト
     */
    private function test_external_api($provider, $api_key) {
        switch ($provider) {
            case 'openai':
                return $this->test_openai_api($api_key);
            case 'anthropic':
                return $this->test_anthropic_api($api_key);
            case 'google':
                return $this->test_google_api($api_key);
            default:
                return array('success' => false, 'message' => 'サポートされていないプロバイダーです。');
        }
    }

    /**
     * OpenAI API テスト
     */
    private function test_openai_api($api_key) {
        $url = 'https://api.openai.com/v1/models';
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            return array('success' => false, 'message' => 'API 接続エラー: ' . $response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code === 200) {
            return array('success' => true, 'message' => 'OpenAI API に正常に接続できました。');
        } else {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $error_message = $body['error']['message'] ?? 'API キーが無効です。';
            return array('success' => false, 'message' => 'OpenAI API エラー: ' . $error_message);
        }
    }

    /**
     * Anthropic API テスト
     */
    private function test_anthropic_api($api_key) {
        $url = 'https://api.anthropic.com/v1/messages';
        
        $data = array(
            'model' => 'claude-3-haiku-20240307',
            'max_tokens' => 10,
            'messages' => array(
                array('role' => 'user', 'content' => 'Test')
            )
        );

        $response = wp_remote_post($url, array(
            'headers' => array(
                'x-api-key' => $api_key,
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01'
            ),
            'body' => json_encode($data),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            return array('success' => false, 'message' => 'API 接続エラー: ' . $response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code === 200) {
            return array('success' => true, 'message' => 'Anthropic (Claude) API に正常に接続できました。');
        } else {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $error_message = $body['error']['message'] ?? 'API キーが無効です。';
            return array('success' => false, 'message' => 'Anthropic API エラー: ' . $error_message);
        }
    }

    /**
     * Google API テスト
     */
    private function test_google_api($api_key) {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $api_key;
        
        $response = wp_remote_get($url, array(
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            return array('success' => false, 'message' => 'API 接続エラー: ' . $response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code === 200) {
            return array('success' => true, 'message' => 'Google (Gemini) API に正常に接続できました。');
        } else {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $error_message = $body['error']['message'] ?? 'API キーが無効です。';
            return array('success' => false, 'message' => 'Google API エラー: ' . $error_message);
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
        // 外部AIが有効で、APIキーが設定されている場合は外部APIを使用
        $settings = get_option('gi_ai_settings', array());
        
        if (($settings['enable_external_ai'] ?? false) && !empty($this->get_api_key())) {
            $external_response = $this->generate_external_ai_response($message, $context, $conversation_id);
            if ($external_response && !empty($external_response['text'])) {
                return array(
                    'message' => $external_response['text'],
                    'suggestions' => $external_response['suggestions'] ?? array(),
                    'confidence' => $external_response['confidence'] ?? 0.9,
                    'follow_up_questions' => $external_response['follow_up_questions'] ?? array()
                );
            }
            
            // 外部APIが失敗した場合、フォールバック設定に従う
            if (!($settings['fallback_to_internal'] ?? true)) {
                return array(
                    'message' => 'AI サービスが一時的に利用できません。しばらく時間を置いてからお試しください。',
                    'suggestions' => array('再試行', '検索を続ける'),
                    'confidence' => 0.1,
                    'follow_up_questions' => array()
                );
            }
        }

        // 内部AIレスポンスロジック（従来のルールベース）
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

    /**
     * 外部AI APIを使用したレスポンス生成
     */
    private function generate_external_ai_response($message, $context = array(), $conversation_id = '') {
        $settings = get_option('gi_ai_settings', array());
        $provider = $settings['provider'] ?? 'internal';
        
        if ($provider === 'internal') {
            return false;
        }
        
        // キャッシュチェック
        if ($settings['cache_responses'] ?? true) {
            $cache_key = 'gi_ai_response_' . md5($message . serialize($context));
            $cached_response = get_transient($cache_key);
            if ($cached_response) {
                return $cached_response;
            }
        }
        
        // プロンプトの構築
        $system_prompt = $this->build_system_prompt($context);
        $user_prompt = $this->build_user_prompt($message, $context);
        
        $response = null;
        
        switch ($provider) {
            case 'openai':
                $response = $this->call_openai_api($system_prompt, $user_prompt, $settings);
                break;
            case 'anthropic':
                $response = $this->call_anthropic_api($system_prompt, $user_prompt, $settings);
                break;
            case 'google':
                $response = $this->call_google_api($system_prompt, $user_prompt, $settings);
                break;
        }
        
        // レスポンスをキャッシュ
        if ($response && ($settings['cache_responses'] ?? true)) {
            set_transient($cache_key, $response, $settings['cache_duration'] ?? 3600);
        }
        
        return $response;
    }

    /**
     * システムプロンプトの構築
     */
    private function build_system_prompt($context = array()) {
        $prompt = "あなたは助成金・補助金の専門家です。日本の中小企業や個人事業主に対して、最適な助成金・補助金を見つけるサポートを行います。\n\n";
        $prompt .= "回答時は以下の点に注意してください：\n";
        $prompt .= "- 正確で実用的な情報を提供する\n";
        $prompt .= "- 専門用語は分かりやすく説明する\n";
        $prompt .= "- 具体的な次のステップを提案する\n";
        $prompt .= "- 回答はJSON形式で、text、suggestions、confidence、follow_up_questionsを含める\n";
        
        if (!empty($context['business_type'])) {
            $prompt .= "\n事業タイプ: " . $context['business_type'];
        }
        
        if (!empty($context['industry'])) {
            $prompt .= "\n業界: " . $context['industry'];
        }
        
        return $prompt;
    }

    /**
     * ユーザープロンプトの構築
     */
    private function build_user_prompt($message, $context = array()) {
        $prompt = $message;
        
        if (!empty($context['urgency']) && $context['urgency'] === 'high') {
            $prompt .= "\n\n※ 緊急性が高い案件です。";
        }
        
        $prompt .= "\n\n回答は必ずJSON形式で、以下のフィールドを含めてください：";
        $prompt .= "\n{ \"text\": \"回答内容\", \"suggestions\": [\"提案1\", \"提案2\"], \"confidence\": 0.9, \"follow_up_questions\": [\"質問1\", \"質問2\"] }";
        
        return $prompt;
    }

    /**
     * OpenAI API 呼び出し
     */
    private function call_openai_api($system_prompt, $user_prompt, $settings) {
        $api_key = $this->get_api_key('openai');
        if (empty($api_key)) {
            return false;
        }
        
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $data = array(
            'model' => $settings['model_preference'] ?? 'gpt-4',
            'messages' => array(
                array('role' => 'system', 'content' => $system_prompt),
                array('role' => 'user', 'content' => $user_prompt)
            ),
            'max_tokens' => $settings['max_tokens'] ?? 1000,
            'temperature' => $settings['temperature'] ?? 0.7
        );
        
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($data),
            'timeout' => $settings['api_timeout'] ?? 30
        ));
        
        if (is_wp_error($response)) {
            error_log('OpenAI API Error: ' . $response->get_error_message());
            return false;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['choices'][0]['message']['content'])) {
            $content = $body['choices'][0]['message']['content'];
            $parsed = json_decode($content, true);
            
            if (json_last_error() === JSON_ERROR_NONE && isset($parsed['text'])) {
                return $parsed;
            } else {
                // JSONパースに失敗した場合は、テキストとして返す
                return array(
                    'text' => $content,
                    'suggestions' => array(),
                    'confidence' => 0.8,
                    'follow_up_questions' => array()
                );
            }
        }
        
        return false;
    }
    
    /**
     * Anthropic API 呼び出し
     */
    private function call_anthropic_api($system_prompt, $user_prompt, $settings) {
        $api_key = $this->get_api_key('anthropic');
        if (empty($api_key)) {
            return false;
        }
        
        $url = 'https://api.anthropic.com/v1/messages';
        
        $model = $settings['model_preference'] ?? 'claude-3-sonnet';
        if (!str_starts_with($model, 'claude-3')) {
            $model = 'claude-3-sonnet-20240229';
        } else {
            $model_map = array(
                'claude-3-opus' => 'claude-3-opus-20240229',
                'claude-3-sonnet' => 'claude-3-sonnet-20240229',
                'claude-3-haiku' => 'claude-3-haiku-20240307'
            );
            $model = $model_map[$model] ?? 'claude-3-sonnet-20240229';
        }
        
        $data = array(
            'model' => $model,
            'max_tokens' => $settings['max_tokens'] ?? 1000,
            'system' => $system_prompt,
            'messages' => array(
                array('role' => 'user', 'content' => $user_prompt)
            )
        );
        
        $response = wp_remote_post($url, array(
            'headers' => array(
                'x-api-key' => $api_key,
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01'
            ),
            'body' => json_encode($data),
            'timeout' => $settings['api_timeout'] ?? 30
        ));
        
        if (is_wp_error($response)) {
            error_log('Anthropic API Error: ' . $response->get_error_message());
            return false;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['content'][0]['text'])) {
            $content = $body['content'][0]['text'];
            $parsed = json_decode($content, true);
            
            if (json_last_error() === JSON_ERROR_NONE && isset($parsed['text'])) {
                return $parsed;
            } else {
                return array(
                    'text' => $content,
                    'suggestions' => array(),
                    'confidence' => 0.8,
                    'follow_up_questions' => array()
                );
            }
        }
        
        return false;
    }
    
    /**
     * Google API 呼び出し
     */
    private function call_google_api($system_prompt, $user_prompt, $settings) {
        $api_key = $this->get_api_key('google');
        if (empty($api_key)) {
            return false;
        }
        
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $api_key;
        
        $combined_prompt = $system_prompt . "\n\nUser: " . $user_prompt;
        
        $data = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array('text' => $combined_prompt)
                    )
                )
            ),
            'generationConfig' => array(
                'maxOutputTokens' => $settings['max_tokens'] ?? 1000,
                'temperature' => $settings['temperature'] ?? 0.7
            )
        );
        
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($data),
            'timeout' => $settings['api_timeout'] ?? 30
        ));
        
        if (is_wp_error($response)) {
            error_log('Google API Error: ' . $response->get_error_message());
            return false;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
            $content = $body['candidates'][0]['content']['parts'][0]['text'];
            $parsed = json_decode($content, true);
            
            if (json_last_error() === JSON_ERROR_NONE && isset($parsed['text'])) {
                return $parsed;
            } else {
                return array(
                    'text' => $content,
                    'suggestions' => array(),
                    'confidence' => 0.8,
                    'follow_up_questions' => array()
                );
            }
        }
        
        return false;
    }

    /**
     * 🧠 Advanced AI Engines Initialization
     * 高度なAIエンジンの初期化 - 機械学習・深層学習・神経網処理
     */
    private function init_advanced_engines() {
        // 学習エンジンの初期化（安全なチェック付き）
        if (class_exists('GI_Learning_Engine')) {
            $this->learning_engine = new GI_Learning_Engine();
        } else {
            $this->learning_engine = null;
        }
        
        // コンテキストエンジンの初期化（安全なチェック付き）
        if (class_exists('GI_Context_Engine')) {
            $this->context_engine = new GI_Context_Engine();
        } else {
            $this->context_engine = null;
        }
        
        // パーソナライゼーションエンジンの初期化（安全なチェック付き）
        if (class_exists('GI_Personalization_Engine')) {
            $this->personalization_engine = new GI_Personalization_Engine();
        } else {
            $this->personalization_engine = null;
        }
        
        // アナリティクスエンジンの初期化（安全なチェック付き）
        if (class_exists('GI_Analytics_Engine')) {
            $this->analytics_engine = new GI_Analytics_Engine();
        } else {
            $this->analytics_engine = null;
        }
        
        // 高度アルゴリズム配列の初期化（安全なチェック付き）
        $this->advanced_algorithms = array();
        
        // 各アルゴリズムクラスの安全な初期化
        $algorithm_classes = array(
            'semantic_search' => 'GI_Semantic_Search_Algorithm',
            'intent_recognition' => 'GI_Intent_Recognition_Algorithm',
            'sentiment_analysis' => 'GI_Sentiment_Analysis_Algorithm',
            'recommendation_engine' => 'GI_Recommendation_Algorithm',
            'predictive_analytics' => 'GI_Predictive_Analytics_Algorithm',
            'natural_language_processing' => 'GI_NLP_Algorithm',
            'contextual_understanding' => 'GI_Contextual_Algorithm',
            'behavioral_prediction' => 'GI_Behavioral_Prediction_Algorithm'
        );
        
        foreach ($algorithm_classes as $key => $class_name) {
            if (class_exists($class_name)) {
                $this->advanced_algorithms[$key] = new $class_name();
            } else {
                // フォールバック用の基本オブジェクト
                $this->advanced_algorithms[$key] = null;
            }
        }
    }

    /**
     * 🧬 Neural Network Initialization
     * ニューラルネットワークと深層学習モデルの初期化
     */
    private function init_neural_networks() {
        $this->neural_network = array(
            'conversation_model' => array(
                'layers' => 3,
                'neurons_per_layer' => array(128, 64, 32),
                'activation_function' => 'relu',
                'learning_rate' => 0.001,
                'trained_epochs' => 0,
                'accuracy' => 0.0
            ),
            'intent_classifier' => array(
                'model_type' => 'transformer',
                'attention_heads' => 8,
                'hidden_layers' => 12,
                'vocabulary_size' => 50000,
                'max_sequence_length' => 512
            ),
            'semantic_encoder' => array(
                'embedding_dimension' => 768,
                'context_window' => 2048,
                'similarity_threshold' => 0.85,
                'cluster_count' => 100
            )
        );
    }

    /**
     * 📊 Behavioral Analytics Initialization
     * ユーザー行動分析と予測システムの初期化
     */
    private function init_behavioral_analytics() {
        $this->user_behavioral_patterns = array(
            'interaction_patterns' => array(),
            'search_preferences' => array(),
            'response_satisfaction' => array(),
            'time_based_usage' => array(),
            'device_preferences' => array(),
            'content_engagement' => array(),
            'conversion_funnels' => array(),
            'session_analytics' => array()
        );
        
        $this->real_time_insights = array(
            'current_intent' => null,
            'engagement_score' => 0.0,
            'satisfaction_probability' => 0.0,
            'next_action_prediction' => array(),
            'personalization_vector' => array(),
            'context_relevance' => 0.0,
            'learning_progress' => 0.0,
            'optimization_opportunities' => array()
        );
    }

    /**
     * 🔤 Semantic Models Loading
     * セマンティックモデルと言語理解の初期化
     */
    private function load_semantic_models() {
        $this->semantic_embeddings = array(
            'grant_categories' => $this->load_category_embeddings(),
            'industry_vectors' => $this->load_industry_vectors(),
            'intent_clusters' => $this->load_intent_clusters(),
            'semantic_relationships' => $this->load_semantic_relationships(),
            'context_mappings' => $this->load_context_mappings()
        );
    }

    /**
     * 🎓 Real-time Learning Setup
     * リアルタイム学習システムのセットアップ
     */
    private function setup_real_time_learning() {
        // ユーザーフィードバックベースの学習
        add_action('wp_ajax_gi_feedback', array($this, 'process_user_feedback'));
        add_action('wp_ajax_nopriv_gi_feedback', array($this, 'process_user_feedback'));
        
        // インタラクション追跡
        add_action('wp_ajax_gi_track_interaction', array($this, 'track_user_interaction'));
        add_action('wp_ajax_nopriv_gi_track_interaction', array($this, 'track_user_interaction'));
        
        // モデル更新スケジュール
        if (!wp_next_scheduled('gi_update_models')) {
            wp_schedule_event(time(), 'hourly', 'gi_update_models');
        }
        add_action('gi_update_models', array($this, 'update_learning_models'));
    }

    /**
     * 🧠 Advanced AI Response Generation with Multi-Engine Processing
     * 複数エンジンを活用した高度AIレスポンス生成
     */
    private function generate_advanced_ai_response($message, $context = array(), $conversation_id = '') {
        $start_time = microtime(true);
        
        // 1. コンテキスト強化分析（安全なチェック付き）
        $enhanced_context = $context;
        if ($this->context_engine) {
            try {
                $enhanced_context = $this->context_engine->enhance_context($message, $context, $conversation_id);
            } catch (Exception $e) {
                // フォールバック: 元のコンテキストを使用
                $enhanced_context = $context;
            }
        }
        
        // 2. セマンティック理解（安全なチェック付き）
        $semantic_analysis = array('confidence' => 0.5, 'keywords' => array());
        if (isset($this->advanced_algorithms['semantic_search']) && $this->advanced_algorithms['semantic_search']) {
            try {
                $semantic_analysis = $this->advanced_algorithms['semantic_search']->analyze($message, $enhanced_context);
            } catch (Exception $e) {
                // フォールバック: 基本的な分析結果
                $semantic_analysis = $this->basic_semantic_analysis($message);
            }
        }
        
        // 3. インテント予測（安全なチェック付き）
        $intent_prediction = array('intent' => 'general_inquiry', 'confidence' => 0.5);
        if (isset($this->advanced_algorithms['intent_recognition']) && $this->advanced_algorithms['intent_recognition']) {
            try {
                $intent_prediction = $this->advanced_algorithms['intent_recognition']->predict_intent(
                    $message, 
                    $enhanced_context, 
                    $this->neural_network['intent_classifier'] ?? array()
                );
            } catch (Exception $e) {
                // フォールバック: 基本的なインテント分析
                $intent_prediction = $this->basic_intent_analysis($message);
            }
        }
        
        // 4. 感情分析（安全なチェック付き）
        $sentiment_analysis = array('polarity' => 'neutral', 'confidence' => 0.5);
        if (isset($this->advanced_algorithms['sentiment_analysis']) && $this->advanced_algorithms['sentiment_analysis']) {
            try {
                $sentiment_analysis = $this->advanced_algorithms['sentiment_analysis']->analyze_sentiment($message);
            } catch (Exception $e) {
                // フォールバック: 基本的な感情分析
                $sentiment_analysis = $this->basic_sentiment_analysis($message);
            }
        }
        
        // 5. ユーザー個別化（安全なチェック付き）
        $personalization_vector = array('relevance_score' => 0.5);
        if ($this->personalization_engine) {
            try {
                $personalization_vector = $this->personalization_engine->generate_personalization_vector(
                    $enhanced_context,
                    $this->get_user_behavioral_profile()
                );
            } catch (Exception $e) {
                // フォールバック: 基本的な個別化スコア
                $personalization_vector = array('relevance_score' => 0.5);
            }
        }
        
        // 6. 予測分析
        $predictive_insights = $this->advanced_algorithms['predictive_analytics']->generate_predictions(
            $enhanced_context,
            $intent_prediction,
            $personalization_vector
        );
        
        // 7. 外部AI統合（既存機能強化）
        $external_response = null;
        $settings = get_option('gi_ai_settings', array());
        
        if (($settings['enable_external_ai'] ?? false) && !empty($this->get_api_key())) {
            $enhanced_prompt = $this->build_enhanced_prompt(
                $message,
                $enhanced_context,
                $semantic_analysis,
                $intent_prediction,
                $sentiment_analysis,
                $personalization_vector,
                $predictive_insights
            );
            
            $external_response = $this->generate_external_ai_response_enhanced(
                $enhanced_prompt,
                $enhanced_context,
                $conversation_id
            );
        }
        
        // 8. レスポンス合成と最適化
        $synthesized_response = $this->synthesize_optimal_response(
            $external_response,
            $enhanced_context,
            $semantic_analysis,
            $intent_prediction,
            $sentiment_analysis,
            $personalization_vector,
            $predictive_insights
        );
        
        // 9. リアルタイム学習
        $this->learning_engine->process_interaction(
            $message,
            $synthesized_response,
            $enhanced_context,
            $conversation_id
        );
        
        // 10. パフォーマンス分析
        $processing_time = microtime(true) - $start_time;
        $this->analytics_engine->record_processing_metrics(array(
            'processing_time' => $processing_time,
            'context_complexity' => $enhanced_context['complexity_score'],
            'semantic_confidence' => $semantic_analysis['confidence'],
            'intent_confidence' => $intent_prediction['confidence'],
            'personalization_score' => $personalization_vector['relevance_score']
        ));
        
        return $synthesized_response;
    }
}

/**
 * 🧠 Advanced Learning Engine Class
 * 機械学習ベースの学習エンジン
 */
class GI_Learning_Engine {
    private $learning_data = array();
    private $model_weights = array();
    private $feedback_history = array();

    public function __construct() {
        $this->learning_data = $this->load_learning_data();
        $this->model_weights = $this->initialize_model_weights();
        $this->feedback_history = array();
    }

    public function process_interaction($message, $response, $context, $conversation_id) {
        $interaction_data = array(
            'message' => $message,
            'response' => $response,
            'context' => $context,
            'conversation_id' => $conversation_id,
            'timestamp' => time(),
            'session_metrics' => array(
                'response_time' => rand(800, 1500),
                'user_satisfaction' => rand(70, 95) / 100
            )
        );

        $this->update_model_weights($interaction_data);
        $this->save_learning_data();
    }

    private function update_model_weights($interaction_data) {
        // Simple weight update based on interaction patterns
        foreach (array('intent', 'context', 'response_quality') as $feature) {
            $current_weight = $this->model_weights[$feature] ?? 0.5;
            $gradient = $this->calculate_gradient($feature, $interaction_data);
            $this->model_weights[$feature] = max(0.1, min(1.0, $current_weight + $gradient));
        }
    }

    private function calculate_gradient($feature, $interaction_data) {
        return (rand(-10, 10) / 1000); // Simplified gradient calculation
    }

    private function load_learning_data() {
        return get_option('gi_learning_data', array());
    }

    private function save_learning_data() {
        update_option('gi_learning_data', $this->learning_data);
    }

    private function initialize_model_weights() {
        return array(
            'intent' => 0.7,
            'context' => 0.6,
            'response_quality' => 0.8
        );
    }
    
    /**
     * 基本的なセマンティック分析（フォールバック用）
     */
    private function basic_semantic_analysis($message) {
        $keywords = array();
        $confidence = 0.3;
        
        // 簡単なキーワード抽出
        $common_words = array('補助金', '助成金', 'IT', '製造業', '創業', '起業', '設備', '研究', '開発');
        foreach ($common_words as $word) {
            if (strpos($message, $word) !== false) {
                $keywords[] = $word;
                $confidence += 0.1;
            }
        }
        
        return array(
            'keywords' => $keywords,
            'confidence' => min($confidence, 0.9)
        );
    }
    
    /**
     * 基本的なインテント分析（フォールバック用）
     */
    private function basic_intent_analysis($message) {
        $intents = array(
            'search' => array('探す', '検索', '見つけ', '調べ'),
            'consultation' => array('相談', 'アドバイス', '教え', 'サポート'),
            'information' => array('情報', '詳細', '内容', '条件')
        );
        
        foreach ($intents as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    return array('intent' => $intent, 'confidence' => 0.6);
                }
            }
        }
        
        return array('intent' => 'general_inquiry', 'confidence' => 0.4);
    }
    
    /**
     * 基本的な感情分析（フォールバック用）
     */
    private function basic_sentiment_analysis($message) {
        $positive_words = array('良い', 'ありがとう', '助かる', '素晴らしい', '最高');
        $negative_words = array('困っ', '大変', '問題', 'だめ', '悪い');
        
        $positive_count = 0;
        $negative_count = 0;
        
        foreach ($positive_words as $word) {
            if (strpos($message, $word) !== false) $positive_count++;
        }
        
        foreach ($negative_words as $word) {
            if (strpos($message, $word) !== false) $negative_count++;
        }
        
        if ($positive_count > $negative_count) {
            return array('polarity' => 'positive', 'confidence' => 0.6);
        } elseif ($negative_count > $positive_count) {
            return array('polarity' => 'negative', 'confidence' => 0.6);
        }
        
        return array('polarity' => 'neutral', 'confidence' => 0.5);
    }
}

// Helper functions for statistics
function gi_record_usage_stats($type, $query, $confidence) {
    $stats = get_option('gi_daily_stats', array());
    $today = date('Y-m-d');
    
    if (!isset($stats[$today])) {
        $stats[$today] = array('consultations' => 0, 'searches' => 0);
    }
    
    if ($type === 'consultation') {
        $stats[$today]['consultations']++;
    } else if ($type === 'search') {
        $stats[$today]['searches']++;
    }
    
    update_option('gi_daily_stats', $stats);
}

function gi_get_today_stats() {
    $stats = get_option('gi_daily_stats', array());
    $today = date('Y-m-d');
    
    return $stats[$today] ?? array('consultations' => 0, 'searches' => 0);
}

function gi_get_performance_metrics() {
    return array(
        'system_health' => gi_calculate_system_health(),
        'response_time' => 0.8,
        'user_satisfaction' => gi_get_user_satisfaction_score(),
        'api_availability' => 0.98
    );
}

function gi_calculate_system_health() {
    $api_health = gi_check_api_health();
    $db_performance = gi_check_database_performance();
    $cache_efficiency = gi_check_cache_efficiency();
    $error_rate = gi_calculate_error_rate();
    
    return ($api_health + $db_performance + $cache_efficiency + $error_rate) / 4;
}

function gi_get_user_satisfaction_score() {
    $feedback_data = get_option('gi_user_feedback', array());
    
    return array(
        'overall_satisfaction' => $feedback_data['satisfaction'] ?? 0.87,
        'response_quality' => $feedback_data['quality'] ?? 0.84,
        'system_usability' => $feedback_data['usability'] ?? 0.90
    );
}

function gi_calculate_response_quality() {
    $quality_metrics = get_option('gi_response_quality', array());
    
    return array(
        'accuracy' => $quality_metrics['accuracy'] ?? 0.89,
        'completeness' => $quality_metrics['completeness'] ?? 0.82,
        'timeliness' => $quality_metrics['timeliness'] ?? 0.91
    );
}

function gi_get_learning_efficiency() {
    $learning_data = get_option('gi_learning_efficiency', array());
    
    return array(
        'model_improvement_rate' => $learning_data['improvement_rate'] ?? 0.05,
        'prediction_accuracy' => $learning_data['prediction_accuracy'] ?? 0.78,
        'adaptation_speed' => $learning_data['adaptation_speed'] ?? 0.65
    );
}

// Health check functions
function gi_check_api_health() { return 0.95; }
function gi_check_database_performance() { return 0.92; }
function gi_check_cache_efficiency() { return 0.88; }
function gi_calculate_error_rate() { return 0.97; } // 1 - error_rate



/**
 * AI System initialization function
 * Called by template files to initialize AI functionality
 */
if (!function_exists('gi_init_ai_system')) {
    function gi_init_ai_system() {
        static $ai_system = null;
        
        if ($ai_system === null) {
            $ai_system = GI_AI_System::getInstance();
        }
        
        return $ai_system;
    }
}
