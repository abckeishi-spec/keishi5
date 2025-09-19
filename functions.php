<?php
/**
 * Grant Insight Perfect - Functions File Loader (段階的AI機能復旧版)
 * @package Grant_Insight_Perfect
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

// テーマバージョン定数
if (!defined('GI_THEME_VERSION')) {
    define('GI_THEME_VERSION', '8.1.0');
}
if (!defined('GI_THEME_PREFIX')) {
    define('GI_THEME_PREFIX', 'gi_');
}

// 機能ファイルの読み込み（段階的にAI機能を復旧）
$inc_dir = get_template_directory() . '/inc/';

// 基本機能ファイル（安全確認済み）
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
);

// AI機能を段階的に有効化（エラーハンドリング付き）
$ai_files = array(
    '13-security-manager.php',        // セキュリティ管理（比較的安全）
    '14-error-handler.php',           // エラーハンドリング（比較的安全）
    '12-ai-functions.php'             // AI システム（最後に読み込み）
);

// 基本ファイルの安全読み込み
foreach ($required_files as $file) {
    $file_path = $inc_dir . $file;
    if (file_exists($file_path)) {
        try {
            require_once $file_path;
            gi_log_debug("✅ 読み込み成功: " . $file);
        } catch (Exception $e) {
            gi_log_error("❌ 読み込みエラー: " . $file . " - " . $e->getMessage());
        }
    } else {
        gi_log_error("❌ ファイル未発見: " . $file_path);
    }
}

// AI機能の段階的読み込み（エラーハンドリング強化）
foreach ($ai_files as $file) {
    $file_path = $inc_dir . $file;
    if (file_exists($file_path)) {
        try {
            // メモリ使用量チェック
            $memory_before = memory_get_usage();
            
            // AI機能を安全に読み込み
            ob_start(); // 出力バッファリング開始
            include_once $file_path;
            $output = ob_get_clean(); // 出力をキャプチャして削除
            
            $memory_after = memory_get_usage();
            $memory_used = $memory_after - $memory_before;
            
            gi_log_debug("✅ AI機能読み込み成功: " . $file . " (メモリ使用量: " . number_format($memory_used / 1024) . " KB)");
            
            // 予期しない出力がある場合は警告
            if (!empty($output)) {
                gi_log_error("⚠️ 予期しない出力: " . $file . " - " . substr($output, 0, 100));
            }
            
        } catch (ParseError $e) {
            gi_log_error("❌ 構文エラー: " . $file . " - " . $e->getMessage());
            // 構文エラーの場合はフォールバック関数を提供
            gi_provide_ai_fallback();
        } catch (Error $e) {
            gi_log_error("❌ 致命的エラー: " . $file . " - " . $e->getMessage());
            gi_provide_ai_fallback();
        } catch (Exception $e) {
            gi_log_error("❌ 例外エラー: " . $file . " - " . $e->getMessage());
            gi_provide_ai_fallback();
        }
    } else {
        gi_log_error("❌ AI機能ファイル未発見: " . $file_path);
        gi_provide_ai_fallback();
    }
}

// 統一カードレンダラーの読み込み（エラーハンドリング付き）
$card_renderer_path = get_template_directory() . '/inc/11-grant-card-renderer.php';
$card_unified_path = get_template_directory() . '/template-parts/grant-card-unified.php';

if (file_exists($card_renderer_path)) {
    try {
        require_once $card_renderer_path;
        gi_log_debug("✅ カードレンダラー読み込み成功");
    } catch (Exception $e) {
        gi_log_error("❌ カードレンダラーエラー: " . $e->getMessage());
    }
} else {
    gi_log_error("❌ GrantCardRenderer class not found");
}

if (file_exists($card_unified_path)) {
    try {
        require_once $card_unified_path;
        gi_log_debug("✅ 統一カードテンプレート読み込み成功");
    } catch (Exception $e) {
        gi_log_error("❌ 統一カードテンプレートエラー: " . $e->getMessage());
    }
}

/**
 * AI機能のフォールバック関数群
 */
function gi_provide_ai_fallback() {
    // AI機能が失敗した場合のフォールバック
    if (!class_exists('GI_AI_System')) {
        class GI_AI_System {
            public static function getInstance() {
                return new self();
            }
            
            public function handle_ai_consultation() {
                wp_send_json_success(array(
                    'response' => 'AI機能は現在メンテナンス中です。しばらくお待ちください。',
                    'fallback' => true
                ));
            }
            
            public function handle_ai_search() {
                wp_send_json_success(array(
                    'results' => array(),
                    'message' => 'AI検索機能は現在メンテナンス中です。',
                    'fallback' => true
                ));
            }
        }
        
        gi_log_debug("✅ AI機能フォールバック提供");
    }
}

/**
 * グローバルで使えるヘルパー関数
 */
if (!function_exists('gi_render_card')) {
    function gi_render_card($post_id, $view = 'grid') {
        if (class_exists('GrantCardRenderer')) {
            try {
                $renderer = GrantCardRenderer::getInstance();
                return $renderer->render($post_id, $view);
            } catch (Exception $e) {
                gi_log_error("カードレンダリングエラー: " . $e->getMessage());
                return gi_render_fallback_card($post_id);
            }
        }
        
        return gi_render_fallback_card($post_id);
    }
}

/**
 * フォールバックカードレンダリング
 */
if (!function_exists('gi_render_fallback_card')) {
    function gi_render_fallback_card($post_id) {
        $post = get_post($post_id);
        if (!$post) return '';
        
        return sprintf(
            '<div class="grant-card-fallback bg-white p-4 rounded-lg shadow border">
                <h3 class="font-bold text-lg mb-2">%s</h3>
                <p class="text-gray-600 mb-4">%s</p>
                <a href="%s" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
                    詳細を見る
                </a>
            </div>',
            esc_html($post->post_title),
            esc_html(wp_trim_words($post->post_excerpt ?: $post->post_content, 20)),
            esc_url(get_permalink($post_id))
        );
    }
}

/**
 * デバッグ用ログ関数
 */
if (!function_exists('gi_log_debug')) {
    function gi_log_debug($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[Grant Insight Debug] ' . $message);
        }
    }
}

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
 * 安全なAJAX処理のためのベース関数
 */
if (!function_exists('gi_safe_ajax_handler')) {
    function gi_safe_ajax_handler($callback, $nonce_action = 'gi_ajax_nonce') {
        // nonce検証
        if (!wp_verify_nonce($_POST['nonce'] ?? '', $nonce_action)) {
            wp_send_json_error('Invalid nonce');
            return;
        }
        
        try {
            call_user_func($callback);
        } catch (Exception $e) {
            gi_log_error('AJAX処理エラー: ' . $e->getMessage());
            wp_send_json_error('処理中にエラーが発生しました');
        }
    }
}

/**
 * テーマの最終初期化
 */
function gi_final_init() {
    $memory_usage = memory_get_usage(true) / 1024 / 1024;
    gi_log_debug("Grant Insight Theme v" . GI_THEME_VERSION . ": 初期化完了 (メモリ使用量: " . number_format($memory_usage, 2) . " MB)");
    
    // AI機能の状態確認
    if (class_exists('GI_AI_System')) {
        gi_log_debug("✅ AI機能: 有効");
    } else {
        gi_log_debug("⚠️ AI機能: フォールバックモード");
    }
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
    
    gi_log_debug("✅ テーマクリーンアップ完了");
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
    gi_safe_ajax_handler(function() {
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
            echo gi_render_card(get_the_ID());
        endwhile;
        
        wp_reset_postdata();
        
        $html = ob_get_clean();
        
        wp_send_json_success([
            'html' => $html,
            'page' => $page,
            'max_pages' => $query->max_num_pages,
            'found_posts' => $query->found_posts
        ]);
    });
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
    
    gi_log_debug("✅ テーマアクティベーションチェック完了");
}
add_action('after_setup_theme', 'gi_theme_activation_check');

/**
 * テーマのバージョンアップグレード処理
 */
function gi_theme_version_upgrade() {
    $current_version = get_option('gi_installed_version', '0.0.0');
    
    if (version_compare($current_version, GI_THEME_VERSION, '<')) {
        // バージョンアップグレード処理
        
        // 8.0.x -> 8.1.0 のアップグレード
        if (version_compare($current_version, '8.1.0', '<')) {
            // AI機能の段階的復旧用キャッシュクリア
            gi_theme_cleanup();
            
            // 新機能の初期化
            gi_update_theme_option('ai_recovery_mode', true);
        }
        
        // バージョン更新
        update_option('gi_installed_version', GI_THEME_VERSION);
        
        // アップグレード完了通知
        if (is_admin()) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>';
                echo 'Grant Insight テーマが v' . GI_THEME_VERSION . ' にアップグレードされました（AI機能段階的復旧版）。';
                echo '</p></div>';
            });
        }
        
        gi_log_debug("✅ テーマアップグレード完了: v" . GI_THEME_VERSION);
    }
}
add_action('init', 'gi_theme_version_upgrade');

/**
 * システム状態の監視
 */
function gi_monitor_system_health() {
    $memory_limit = wp_convert_hr_to_bytes(ini_get('memory_limit'));
    $memory_usage = memory_get_usage(true);
    $memory_percent = ($memory_usage / $memory_limit) * 100;
    
    if ($memory_percent > 80) {
        gi_log_error("⚠️ メモリ使用量が高い: " . number_format($memory_percent, 1) . "%");
    }
    
    // AI機能の状態監視
    if (!class_exists('GI_AI_System')) {
        gi_log_debug("ℹ️ AI機能はフォールバックモードで動作中");
    }
}
add_action('wp_loaded', 'gi_monitor_system_health', 1000);

// 初期化完了ログ
gi_log_debug("📋 Grant Insight Theme Functions.php 読み込み完了 (v" . GI_THEME_VERSION . ")");
?>