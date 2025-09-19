<?php
/**
 * Grant Insight Perfect - Functions File Loader (AI機能無効版)
 * @package Grant_Insight_Perfect
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

// テーマバージョン定数（重複チェック追加）
if (!defined('GI_THEME_VERSION')) {
    define('GI_THEME_VERSION', '6.2.2');
}
if (!defined('GI_THEME_PREFIX')) {
    define('GI_THEME_PREFIX', 'gi_');
}

// 機能ファイルの読み込み（AI機能を除外）
$inc_dir = get_template_directory() . '/inc/';

// AI関連ファイルを除いた基本ファイル
$required_files = array(
    '1-theme-setup-optimized.php',    // テーマ基本設定、スクリプト（最適化版）
    '2-post-types.php',               // 投稿タイプ、タクソノミー
    '3-ajax-functions.php',           // AJAX関連
    '4-helper-functions.php',         // ヘルパー関数
    '5-template-tags.php',            // テンプレート用関数
    '6-admin-functions.php',          // 管理画面関連
    '7-acf-setup.php',                // ACF関連
    '8-acf-fields-setup.php',         // ACFフィールド定義
    '9-mobile-optimization.php',      // モバイル最適化機能
    '10-performance-helpers.php',     // パフォーマンス最適化ヘルパー
    // '12-ai-functions.php',         // AI システム - 一時的に無効化
    // '13-security-manager.php',     // セキュリティ管理 - 一時的に無効化
    // '14-error-handler.php'         // エラーハンドリング - 一時的に無効化
);

// 各ファイルを安全に読み込み
foreach ($required_files as $file) {
    $file_path = $inc_dir . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
        error_log("読み込み成功: " . $file);
    } else {
        error_log("ファイル未発見: " . $file_path);
    }
}

// 統一カードレンダラーの読み込み（エラーハンドリング付き）
$card_renderer_path = get_template_directory() . '/inc/11-grant-card-renderer.php';
$card_unified_path = get_template_directory() . '/template-parts/grant-card-unified.php';

if (file_exists($card_renderer_path)) {
    require_once $card_renderer_path;
} else {
    error_log('GrantCardRenderer class not found at ' . $card_renderer_path);
}

if (file_exists($card_unified_path)) {
    require_once $card_unified_path;
} else {
    error_log('grant-card-unified.php not found at ' . $card_unified_path);
}

// グローバルで使えるヘルパー関数
if (!function_exists('gi_render_card')) {
    function gi_render_card($post_id, $view = 'grid') {
        if (class_exists('GrantCardRenderer')) {
            $renderer = GrantCardRenderer::getInstance();
            return $renderer->render($post_id, $view);
        }
        
        // フォールバック
        return '<div class="grant-card-error">カードレンダラーが利用できません</div>';
    }
}

/**
 * AI機能のフォールバック関数群
 */

// AI機能が無効化されている場合のフォールバック
if (!class_exists('GI_AI_System')) {
    class GI_AI_System {
        public static function getInstance() {
            return new self();
        }
        
        public function handle_ai_consultation() {
            wp_send_json_error('AI機能は現在無効化されています');
        }
        
        public function handle_ai_search() {
            wp_send_json_error('AI検索機能は現在無効化されています');
        }
        
        public function handle_ai_recommendation() {
            wp_send_json_error('AI推薦機能は現在無効化されています');
        }
    }
}

if (!class_exists('GI_Security_Manager')) {
    class GI_Security_Manager {
        public static function getInstance() {
            return new self();
        }
        
        public function sanitize_ai_input($input) {
            return sanitize_text_field($input);
        }
    }
}

if (!class_exists('GI_Error_Handler')) {
    class GI_Error_Handler {
        public static function getInstance() {
            return new self();
        }
        
        public function handleError($error) {
            error_log('Error handled: ' . $error);
        }
    }
}

/**
 * テーマの最終初期化
 */
function gi_final_init() {
    error_log('Grant Insight Theme v' . GI_THEME_VERSION . ': AI機能無効版で初期化完了');
}
add_action('wp_loaded', 'gi_final_init', 999);

/**
 * クリーンアップ処理
 */
function gi_theme_cleanup() {
    // オプションの削除
    delete_option('gi_login_attempts');
    
    // モバイル最適化キャッシュのクリア
    delete_option('gi_mobile_cache');
    
    // トランジェントのクリア
    delete_transient('gi_site_stats_v2');
    
    // オブジェクトキャッシュのフラッシュ（存在する場合のみ）
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
}
add_action('switch_theme', 'gi_theme_cleanup');

/**
 * スクリプトにdefer属性を追加（改善版）
 */
if (!function_exists('gi_add_defer_attribute')) {
    function gi_add_defer_attribute($tag, $handle, $src) {
        // 管理画面では処理しない
        if (is_admin()) {
            return $tag;
        }
        
        // WordPressコアスクリプトは除外
        if (strpos($src, 'wp-includes/js/') !== false) {
            return $tag;
        }
        
        // 既にdefer/asyncがある場合はスキップ
        if (strpos($tag, 'defer') !== false || strpos($tag, 'async') !== false) {
            return $tag;
        }
        
        // 特定のハンドルにのみdeferを追加
        $defer_handles = array(
            'gi-main-js',
            'gi-frontend-js',
            'gi-mobile-enhanced'
        );
        
        if (in_array($handle, $defer_handles)) {
            return str_replace('<script ', '<script defer ', $tag);
        }
        
        return $tag;
    }
}

// フィルターの重複登録を防ぐ
remove_filter('script_loader_tag', 'gi_add_defer_attribute', 10);
add_filter('script_loader_tag', 'gi_add_defer_attribute', 10, 3);

/**
 * モバイル用AJAX エンドポイント - さらに読み込み
 */
function gi_ajax_load_more_grants() {
    check_ajax_referer('gi_ajax_nonce', 'nonce');
    
    $page = intval($_POST['page'] ?? 1);
    $posts_per_page = 10;
    
    $args = [
        'post_type' => 'grant',
        'posts_per_page' => $posts_per_page,
        'post_status' => 'publish',
        'paged' => $page,
        'orderby' => 'date',
        'order' => 'DESC'
    ];
    
    $query = new WP_Query($args);
    
    if (!$query->have_posts()) {
        wp_send_json_error('No more posts found');
    }
    
    ob_start();
    
    while ($query->have_posts()): $query->the_post();
        echo gi_render_mobile_card(get_the_ID());
    endwhile;
    
    wp_reset_postdata();
    
    $html = ob_get_clean();
    
    wp_send_json_success([
        'html' => $html,
        'page' => $page,
        'max_pages' => $query->max_num_pages,
        'found_posts' => $query->found_posts
    ]);
}
add_action('wp_ajax_gi_load_more_grants', 'gi_ajax_load_more_grants');
add_action('wp_ajax_nopriv_gi_load_more_grants', 'gi_ajax_load_more_grants');

/**
 * テーマのアクティベーションチェック
 */
function gi_theme_activation_check() {
    // PHP バージョンチェック
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo 'Grant Insight テーマはPHP 7.4以上が必要です。現在のバージョン: ' . PHP_VERSION;
            echo '</p></div>';
        });
    }
    
    // WordPress バージョンチェック
    global $wp_version;
    if (version_compare($wp_version, '5.8', '<')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-warning"><p>';
            echo 'Grant Insight テーマはWordPress 5.8以上を推奨します。';
            echo '</p></div>';
        });
    }
    
    // 必須プラグインチェック（ACFなど）
    if (!class_exists('ACF') && is_admin()) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-info"><p>';
            echo 'Grant Insight テーマの全機能を利用するには、Advanced Custom Fields (ACF) プラグインのインストールを推奨します。';
            echo '</p></div>';
        });
    }
}
add_action('after_setup_theme', 'gi_theme_activation_check');

/**
 * エラーハンドリング用のグローバル関数
 */
if (!function_exists('gi_log_error')) {
    function gi_log_error($message, $context = array()) {
        $log_message = '[Grant Insight Error] ' . $message;
        if (!empty($context)) {
            $log_message .= ' | Context: ' . print_r($context, true);
        }
        error_log($log_message);
    }
}

/**
 * テーマ設定のデフォルト値を取得
 */
if (!function_exists('gi_get_theme_option')) {
    function gi_get_theme_option($option_name, $default = null) {
        $theme_options = get_option('gi_theme_options', array());
        
        if (isset($theme_options[$option_name])) {
            return $theme_options[$option_name];
        }
        
        return $default;
    }
}

/**
 * テーマ設定を保存
 */
if (!function_exists('gi_update_theme_option')) {
    function gi_update_theme_option($option_name, $value) {
        $theme_options = get_option('gi_theme_options', array());
        $theme_options[$option_name] = $value;
        
        return update_option('gi_theme_options', $theme_options);
    }
}

/**
 * テーマのバージョンアップグレード処理
 */
function gi_theme_version_upgrade() {
    $current_version = get_option('gi_installed_version', '0.0.0');
    
    if (version_compare($current_version, GI_THEME_VERSION, '<')) {
        // バージョンアップグレード処理
        
        // 6.2.0 -> 6.2.1 のアップグレード
        if (version_compare($current_version, '6.2.1', '<')) {
            // キャッシュのクリア
            gi_theme_cleanup();
        }
        
        // 6.2.1 -> 6.2.2 のアップグレード
        if (version_compare($current_version, '6.2.2', '<')) {
            // 新しいメタフィールドの追加など
            flush_rewrite_rules();
        }
        
        // バージョン更新
        update_option('gi_installed_version', GI_THEME_VERSION);
        
        // アップグレード完了通知
        if (is_admin()) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>';
                echo 'Grant Insight テーマが v' . GI_THEME_VERSION . ' にアップグレードされました（AI機能無効版）。';
                echo '</p></div>';
            });
        }
    }
}
add_action('init', 'gi_theme_version_upgrade');

// デバッグ情報
error_log("📋 Grant Insight Theme: AI機能無効版のfunctions.phpが読み込まれました");
?>