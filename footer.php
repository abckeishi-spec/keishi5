<?php
/**
 * Grant Insight Perfect - Ultra Simple Footer Template
 * 超シンプル版 - functions.phpと完全連携
 * 
 * @package Grant_Insight_Perfect
 * @version 8.0.0-ultra-simple
 */

// 既存ヘルパー関数との完全連携
if (!function_exists('gi_get_sns_urls')) {
    function gi_get_sns_urls() {
        return [
            'twitter' => gi_get_theme_option('sns_twitter_url', ''),
            'facebook' => gi_get_theme_option('sns_facebook_url', ''),
            'linkedin' => gi_get_theme_option('sns_linkedin_url', ''),
            'instagram' => gi_get_theme_option('sns_instagram_url', ''),
            'youtube' => gi_get_theme_option('sns_youtube_url', '')
        ];
    }
}
?>

    </main>

    <!-- Tailwind CSS + Font Awesome + Google Fonts -->
    <?php if (!wp_script_is('tailwind-cdn', 'enqueued')): ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                        'space': ['Space Grotesk', 'sans-serif'],
                        'noto': ['Noto Sans JP', 'sans-serif']
                    },
                    boxShadow: {
                        'elegant': '0 20px 40px -12px rgba(0, 0, 0, 0.15)',
                        'elegant-dark': '0 25px 50px -12px rgba(0, 0, 0, 0.3)'
                    },
                    borderRadius: {
                        '4xl': '2rem',
                        '5xl': '2.5rem'
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@300;400;500;600;700&family=Noto+Sans+JP:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <?php endif; ?>

    <!-- 超シンプル・エレガントフッター -->
    <footer class="site-footer relative overflow-hidden" style="background: linear-gradient(135deg, var(--neutral-50) 0%, var(--neutral-100) 50%, var(--neutral-50) 100%); transition: all 0.7s ease; font-family: var(--font-primary);">
        
        <!-- 控えめな背景装飾 -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-40">
            <!-- グリッド -->
            <div class="absolute inset-0 bg-[linear-gradient(to_right,theme(colors.gray.200)_1px,transparent_1px),linear-gradient(to_bottom,theme(colors.gray.200)_1px,transparent_1px)] bg-[size:4rem_4rem] opacity-30"></div>
            
            <!-- 控えめなグラデーション -->
            <div class="absolute -top-20 -right-20 w-48 h-48" style="background: linear-gradient(135deg, rgba(0, 0, 0, 0.03) 0%, rgba(0, 0, 0, 0.05) 100%); border-radius: 50%; filter: blur(3rem);"></div>
            <div class="absolute -bottom-16 -left-16 w-40 h-40" style="background: linear-gradient(135deg, rgba(0, 0, 0, 0.02) 0%, rgba(0, 0, 0, 0.04) 100%); border-radius: 50%; filter: blur(2rem);"></div>
        </div>

        <div class="relative z-10 py-16 lg:py-20">
            <div class="container mx-auto px-6 lg:px-8">
                
                <!-- メインブランドセクション -->
                <div class="text-center mb-16">
                    <div class="inline-flex items-center space-x-6 mb-8 group">
                        <div class="relative">
                            <img src="<?php echo esc_url(gi_get_media_url('名称未設定のデザイン.png', false)) ?: gi_get_asset_url('assets/images/logo.png'); ?>" 
                                 alt="<?php bloginfo('name'); ?>" 
                                 class="h-16 w-auto drop-shadow-lg group-hover:drop-shadow-xl transition-all duration-300 group-hover:scale-105">
                        </div>
                        
                        <div class="text-left">
                            <h2 class="text-3xl lg:text-4xl font-black leading-tight" style="color: var(--neutral-800); font-family: var(--font-primary);">
                                <?php 
                                $site_name = get_bloginfo('name');
                                $name_parts = explode('・', $site_name);
                                if (count($name_parts) > 1) {
                                    echo esc_html($name_parts[0]) . '・';
                                    echo '<span style="background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-primary-600) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">' . esc_html($name_parts[1]) . '</span>';
                                } else {
                                    echo '<span style="background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-primary-600) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">' . esc_html($site_name) . '</span>';
                                }
                                ?>
                            </h2>
                            <div class="flex items-center space-x-2 mt-1">
                                <span class="text-sm" style="color: var(--neutral-600);">Powered by AI Technology</span>
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-lg max-w-2xl mx-auto leading-relaxed font-light" style="color: var(--neutral-600);">
                        最先端AIテクノロジーで、あなたに最適な助成金・補助金を発見。<br class="hidden md:block">
                        <span class="font-semibold">ビジネス成長を加速</span>させましょう。
                    </p>
                </div>

                <!-- シンプルナビゲーション（2列レイアウト） -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-16">
                    
                    <!-- 補助金検索カード -->
                    <div class="rounded-4xl p-8 transition-all duration-500 border group" style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(20px); border-color: var(--neutral-200); box-shadow: var(--shadow-lg);" onmouseover="this.style.borderColor='var(--brand-primary)'; this.style.boxShadow='var(--shadow-xl)';" onmouseout="this.style.borderColor='var(--neutral-200)'; this.style.boxShadow='var(--shadow-lg)';">
                        <div class="text-center mb-6">
                            <div class="w-14 h-14 rounded-3xl flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300" style="background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-primary-600) 100%);">
                                <i class="fas fa-search text-white text-xl"></i>
                            </div>
                            <h3 class="text-xl font-bold mb-2" style="color: var(--neutral-800); font-family: var(--font-primary);">補助金を探す</h3>
                            <p class="text-sm" style="color: var(--neutral-600);">最適な補助金を瞬時に発見</p>
                        </div>
                        
                        <div class="space-y-2">
                            <a href="<?php echo esc_url(home_url('/grants/')); ?>" 
                               class="flex items-center justify-between p-3 rounded-2xl transition-all duration-300 group/item border" style="background: rgba(0, 0, 0, 0.03); border-color: var(--neutral-200);" onmouseover="this.style.background='rgba(0, 0, 0, 0.06)'; this.style.borderColor='var(--brand-primary)';" onmouseout="this.style.background='rgba(0, 0, 0, 0.03)'; this.style.borderColor='var(--neutral-200)';">
                                <div class="flex items-center space-x-3">
                                    <div class="w-6 h-6 rounded-lg flex items-center justify-center" style="background: var(--neutral-100);">
                                        <i class="fas fa-list text-xs" style="color: var(--brand-primary);"></i>
                                    </div>
                                    <span class="font-medium text-sm" style="color: var(--neutral-700);">助成金一覧</span>
                                </div>
                                <i class="fas fa-chevron-right text-xs transition-all duration-200" style="color: var(--neutral-400);" onmouseover="this.style.color='var(--brand-primary)'; this.style.transform='translateX(0.25rem)';" onmouseout="this.style.color='var(--neutral-400)'; this.style.transform='translateX(0)';"></i>
                            </a>
                            
                            <?php
                            // 主要カテゴリーのみ表示 - モノクロテーマ
                            $main_categories = [
                                ['slug' => 'it', 'name' => 'IT・デジタル化', 'icon' => 'fas fa-laptop-code', 'color' => 'primary'],
                                ['slug' => 'manufacturing', 'name' => 'ものづくり', 'icon' => 'fas fa-industry', 'color' => 'primary'],
                                ['slug' => 'startup', 'name' => '創業・起業', 'icon' => 'fas fa-rocket', 'color' => 'primary'],
                                ['slug' => 'employment', 'name' => '雇用促進', 'icon' => 'fas fa-users', 'color' => 'primary']
                            ];
                            
                            foreach ($main_categories as $category):
                            ?>
                            <a href="<?php echo esc_url(home_url('/grants/?category=' . $category['slug'])); ?>" 
                               class="flex items-center justify-between p-3 rounded-2xl transition-all duration-300 group/item border" style="background: rgba(0, 0, 0, 0.03); border-color: var(--neutral-200);" onmouseover="this.style.background='rgba(0, 0, 0, 0.06)'; this.style.borderColor='var(--brand-primary)';" onmouseout="this.style.background='rgba(0, 0, 0, 0.03)'; this.style.borderColor='var(--neutral-200)';">
                                <div class="flex items-center space-x-3">
                                    <div class="w-6 h-6 rounded-lg flex items-center justify-center" style="background: var(--neutral-100);">
                                        <i class="<?php echo $category['icon']; ?> text-xs" style="color: var(--brand-primary);"></i>
                                    </div>
                                    <span class="font-medium text-sm" style="color: var(--neutral-700);"><?php echo esc_html($category['name']); ?></span>
                                </div>
                                <i class="fas fa-chevron-right text-xs transition-all duration-200" style="color: var(--neutral-400);" onmouseover="this.style.color='var(--brand-primary)'; this.style.transform='translateX(0.25rem)';" onmouseout="this.style.color='var(--neutral-400)'; this.style.transform='translateX(0)';"></i>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- サポート・情報カード -->
                    <div class="rounded-4xl p-8 transition-all duration-500 border group" style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(20px); border-color: var(--neutral-200); box-shadow: var(--shadow-lg);" onmouseover="this.style.borderColor='var(--brand-primary)'; this.style.boxShadow='var(--shadow-xl)';" onmouseout="this.style.borderColor='var(--neutral-200)'; this.style.boxShadow='var(--shadow-lg)';">
                        <div class="text-center mb-6">
                            <div class="w-14 h-14 rounded-3xl flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300" style="background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-primary-600) 100%);">
                                <i class="fas fa-info-circle text-white text-xl"></i>
                            </div>
                            <h3 class="text-xl font-bold mb-2" style="color: var(--neutral-800); font-family: var(--font-primary);">サポート・情報</h3>
                            <p class="text-sm" style="color: var(--neutral-600);">お困りの際はこちらから</p>
                        </div>
                        
                        <div class="space-y-2">
                            <?php
                            // 重要なサポートリンクのみ - モノクロテーマ
                            $support_links = [
                                ['url' => '/about/', 'name' => 'サービスについて', 'icon' => 'fas fa-info-circle', 'color' => 'primary'],
                                ['url' => '/contact/', 'name' => 'お問い合わせ', 'icon' => 'fas fa-envelope', 'color' => 'primary'],
                                ['url' => '/faq/', 'name' => 'よくある質問', 'icon' => 'fas fa-question-circle', 'color' => 'primary'],
                                ['url' => '/privacy/', 'name' => 'プライバシーポリシー', 'icon' => 'fas fa-shield-alt', 'color' => 'primary'],
                                ['url' => '/terms/', 'name' => '利用規約', 'icon' => 'fas fa-file-contract', 'color' => 'primary']
                            ];
                            
                            foreach ($support_links as $link):
                            ?>
                            <a href="<?php echo esc_url(home_url($link['url'])); ?>" 
                               class="flex items-center justify-between p-3 rounded-2xl transition-all duration-300 group/item border" style="background: rgba(0, 0, 0, 0.03); border-color: var(--neutral-200);" onmouseover="this.style.background='rgba(0, 0, 0, 0.06)'; this.style.borderColor='var(--brand-primary)';" onmouseout="this.style.background='rgba(0, 0, 0, 0.03)'; this.style.borderColor='var(--neutral-200)';">
                                <div class="flex items-center space-x-3">
                                    <div class="w-6 h-6 rounded-lg flex items-center justify-center" style="background: var(--neutral-100);">
                                        <i class="<?php echo $link['icon']; ?> text-xs" style="color: var(--brand-primary);"></i>
                                    </div>
                                    <span class="font-medium text-sm" style="color: var(--neutral-700);"><?php echo esc_html($link['name']); ?></span>
                                </div>
                                <i class="fas fa-chevron-right text-xs transition-all duration-200" style="color: var(--neutral-400);" onmouseover="this.style.color='var(--brand-primary)'; this.style.transform='translateX(0.25rem)';" onmouseout="this.style.color='var(--neutral-400)'; this.style.transform='translateX(0)';"></i>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- モバイル用シンプルメニュー -->
                <div class="lg:hidden mb-12">
                    <button id="gi-mobile-footer-toggle" class="w-full rounded-4xl p-5 border flex items-center justify-between transition-all duration-300 group" style="background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(20px); border-color: var(--neutral-200); box-shadow: var(--shadow-lg); color: var(--neutral-800);" onmouseover="this.style.background='rgba(255, 255, 255, 0.9)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.8)';">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-gradient-to-br from-neutral-700 to-neutral-800 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-bars text-white"></i>
                            </div>
                            <div class="text-left">
                                <h3 class="font-bold">メニュー</h3>
                                <p class="text-xs text-gray-600">サービス一覧</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-down transition-transform duration-300" id="gi-mobile-toggle-icon"></i>
                    </button>
                </div>

                <!-- モバイル用コンテンツ -->
                <div id="gi-mobile-footer-content" class="lg:hidden space-y-4 hidden overflow-hidden mb-12" style="max-height: 0; transition: max-height 0.3s ease-out;">
                    
                    <!-- 補助金を探す（モバイル） -->
                    <div class="rounded-3xl p-5 border" style="background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(20px); border-color: var(--neutral-200); box-shadow: var(--shadow-lg);">
                        <h3 class="font-bold mb-3 flex items-center" style="color: var(--neutral-800);">
                            <i class="fas fa-search mr-2" style="color: var(--brand-primary);"></i>補助金を探す
                        </h3>
                        <div class="space-y-2">
                            <a href="<?php echo esc_url(home_url('/grants/')); ?>" class="flex items-center p-3 rounded-xl transition-colors" style="background: rgba(0, 0, 0, 0.03);" onmouseover="this.style.background='rgba(0, 0, 0, 0.06)';" onmouseout="this.style.background='rgba(0, 0, 0, 0.03)';">
                                <i class="fas fa-list mr-3 text-sm" style="color: var(--brand-primary);"></i>
                                <span class="font-medium text-sm" style="color: var(--neutral-700);">助成金一覧</span>
                            </a>
                            <?php foreach ($main_categories as $category): ?>
                            <a href="<?php echo esc_url(home_url('/grants/?category=' . $category['slug'])); ?>" class="flex items-center p-3 rounded-xl transition-colors" style="background: rgba(0, 0, 0, 0.03);" onmouseover="this.style.background='rgba(0, 0, 0, 0.06)';" onmouseout="this.style.background='rgba(0, 0, 0, 0.03)';">
                                <i class="<?php echo $category['icon']; ?> mr-3 text-sm" style="color: var(--brand-primary);"></i>
                                <span class="font-medium text-sm" style="color: var(--neutral-700);"><?php echo esc_html($category['name']); ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- サポート（モバイル） -->
                    <div class="rounded-3xl p-5 border" style="background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(20px); border-color: var(--neutral-200); box-shadow: var(--shadow-lg);">
                        <h3 class="font-bold mb-3 flex items-center" style="color: var(--neutral-800);">
                            <i class="fas fa-info-circle mr-2" style="color: var(--brand-primary);"></i>サポート・情報
                        </h3>
                        <div class="space-y-2">
                            <?php foreach ($support_links as $link): ?>
                            <a href="<?php echo esc_url(home_url($link['url'])); ?>" class="flex items-center p-3 rounded-xl transition-colors" style="background: rgba(0, 0, 0, 0.03);" onmouseover="this.style.background='rgba(0, 0, 0, 0.06)';" onmouseout="this.style.background='rgba(0, 0, 0, 0.03)';">
                                <i class="<?php echo $link['icon']; ?> mr-3 text-sm" style="color: var(--brand-primary);"></i>
                                <span class="font-medium text-sm" style="color: var(--neutral-700);"><?php echo esc_html($link['name']); ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- フッター下部セクション -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                    
                    <!-- SNS & 特徴 -->
                    <div class="text-center lg:text-left">
                        <h4 class="text-xl font-bold mb-6" style="color: var(--neutral-800);">フォローして最新情報をチェック</h4>
                        
                        <div class="flex justify-center lg:justify-start space-x-3 mb-6">
                            <?php
                            $sns_urls = gi_get_sns_urls();
                            $sns_data = [
                                'twitter' => ['icon' => 'fab fa-twitter', 'color' => 'primary'],
                                'facebook' => ['icon' => 'fab fa-facebook-f', 'color' => 'primary'], 
                                'linkedin' => ['icon' => 'fab fa-linkedin-in', 'color' => 'primary'],
                                'instagram' => ['icon' => 'fab fa-instagram', 'color' => 'primary'],
                                'youtube' => ['icon' => 'fab fa-youtube', 'color' => 'primary']
                            ];

                            foreach ($sns_urls as $platform => $url): 
                                if (!empty($url)):
                            ?>
                            <a href="<?php echo esc_url($url); ?>" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="w-10 h-10 rounded-2xl flex items-center justify-center text-white transition-all duration-300 transform hover:-translate-y-1 hover:scale-110 group" style="background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-primary-600) 100%); box-shadow: var(--shadow-lg);" onmouseover="this.style.boxShadow='var(--shadow-xl)';" onmouseout="this.style.boxShadow='var(--shadow-lg)';">
                                <i class="<?php echo $sns_data[$platform]['icon']; ?> text-sm"></i>
                            </a>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>

                        <!-- 特徴バッジ -->
                        <div class="flex flex-wrap justify-center lg:justify-start gap-3">
                            <span class="px-3 py-2 rounded-2xl text-xs font-semibold border hover:scale-105 transition-transform duration-300 cursor-default" style="background: rgba(34, 197, 94, 0.1); color: var(--semantic-success); border-color: rgba(34, 197, 94, 0.2);">
                                <i class="fas fa-check-circle mr-1"></i>無料診断
                            </span>
                            <span class="px-3 py-2 rounded-2xl text-xs font-semibold border hover:scale-105 transition-transform duration-300 cursor-default" style="background: rgba(0, 0, 0, 0.05); color: var(--brand-primary); border-color: var(--neutral-200);">
                                <i class="fas fa-robot mr-1"></i>AI支援
                            </span>
                            <span class="px-3 py-2 rounded-2xl text-xs font-semibold border hover:scale-105 transition-transform duration-300 cursor-default" style="background: rgba(0, 0, 0, 0.05); color: var(--brand-primary); border-color: var(--neutral-200);">
                                <i class="fas fa-shield-alt mr-1"></i>安心・安全
                            </span>
                        </div>
                    </div>

                    <!-- コピーライト・信頼性 -->
                    <div class="text-center lg:text-right">
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="flex flex-col items-center group hover:scale-105 transition-transform duration-300" style="color: var(--semantic-success);">
                                <div class="w-10 h-10 rounded-2xl flex items-center justify-center mb-2 transition-colors" style="background: rgba(34, 197, 94, 0.1);" onmouseover="this.style.background='rgba(34, 197, 94, 0.15)';" onmouseout="this.style.background='rgba(34, 197, 94, 0.1)';">">
                                    <i class="fas fa-shield-alt text-sm" style="color: var(--semantic-success);"></i>
                                </div>
                                <span class="font-medium text-xs">SSL暗号化</span>
                            </div>
                            <div class="flex flex-col items-center group hover:scale-105 transition-transform duration-300" style="color: var(--brand-primary);">
                                <div class="w-10 h-10 rounded-2xl flex items-center justify-center mb-2 transition-colors" style="background: rgba(0, 0, 0, 0.05);" onmouseover="this.style.background='rgba(0, 0, 0, 0.08)';" onmouseout="this.style.background='rgba(0, 0, 0, 0.05)';">">
                                    <i class="fas fa-lock text-sm" style="color: var(--brand-primary);"></i>
                                </div>
                                <span class="font-medium text-xs">情報保護</span>
                            </div>
                            <div class="flex flex-col items-center group hover:scale-105 transition-transform duration-300" style="color: var(--brand-primary);">
                                <div class="w-10 h-10 rounded-2xl flex items-center justify-center mb-2 transition-colors" style="background: rgba(0, 0, 0, 0.05);" onmouseover="this.style.background='rgba(0, 0, 0, 0.08)';" onmouseout="this.style.background='rgba(0, 0, 0, 0.05)';">">
                                    <i class="fas fa-award text-sm" style="color: var(--brand-primary);"></i>
                                </div>
                                <span class="font-medium text-xs">専門家監修</span>
                            </div>
                            <div class="flex flex-col items-center group hover:scale-105 transition-transform duration-300" style="color: var(--brand-primary);">
                                <div class="w-10 h-10 rounded-2xl flex items-center justify-center mb-2 transition-colors" style="background: rgba(0, 0, 0, 0.05);" onmouseover="this.style.background='rgba(0, 0, 0, 0.08)';" onmouseout="this.style.background='rgba(0, 0, 0, 0.05)';">">
                                    <i class="fas fa-robot text-sm" style="color: var(--brand-primary);"></i>
                                </div>
                                <span class="font-medium text-xs">AI技術</span>
                            </div>
                        </div>

                        <div class="pt-4" style="border-top: 1px solid var(--neutral-200);">
                            <p class="mb-1 font-medium" style="color: var(--neutral-600);">
                                &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved.
                            </p>
                            <p class="text-xs" style="color: var(--neutral-500);">
                                Powered by Next-Generation AI Technology
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- トップに戻るボタン -->
    <div id="gi-back-to-top" class="fixed bottom-6 right-6 z-50 opacity-0 pointer-events-none transition-all duration-300">
        <button class="w-12 h-12 text-white rounded-3xl transition-all duration-300 transform hover:-translate-y-2 hover:scale-110 group" style="background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-primary-600) 100%); box-shadow: var(--shadow-lg);" onclick="giScrollToTop()" onmouseover="this.style.boxShadow='var(--shadow-xl)';" onmouseout="this.style.boxShadow='var(--shadow-lg)';">">
            <i class="fas fa-arrow-up"></i>
        </button>
    </div>

    <!-- JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // モバイルメニュー制御
        const mobileToggle = document.getElementById('gi-mobile-footer-toggle');
        const mobileContent = document.getElementById('gi-mobile-footer-content');
        const mobileIcon = document.getElementById('gi-mobile-toggle-icon');
        let isOpen = false;

        if (mobileToggle && mobileContent) {
            mobileToggle.addEventListener('click', function() {
                isOpen = !isOpen;
                
                if (isOpen) {
                    mobileContent.classList.remove('hidden');
                    mobileContent.style.maxHeight = mobileContent.scrollHeight + 'px';
                    mobileIcon.style.transform = 'rotate(180deg)';
                } else {
                    mobileContent.style.maxHeight = '0px';
                    mobileIcon.style.transform = 'rotate(0deg)';
                    setTimeout(() => {
                        mobileContent.classList.add('hidden');
                    }, 300);
                }
            });
        }

        // トップに戻るボタン制御
        let ticking = false;
        
        function updateBackToTop() {
            const backToTopButton = document.getElementById('gi-back-to-top');
            if (!backToTopButton) return;
            
            const scrolled = window.pageYOffset;
            
            if (scrolled > 300) {
                backToTopButton.classList.remove('opacity-0', 'pointer-events-none');
                backToTopButton.classList.add('opacity-100', 'pointer-events-auto');
            } else {
                backToTopButton.classList.add('opacity-0', 'pointer-events-none');
                backToTopButton.classList.remove('opacity-100', 'pointer-events-auto');
            }
            
            ticking = false;
        }

        window.addEventListener('scroll', function() {
            if (!ticking) {
                requestAnimationFrame(updateBackToTop);
                ticking = true;
            }
        });

        // レスポンシブ対応
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024 && mobileContent && !mobileContent.classList.contains('hidden')) {
                mobileContent.classList.add('hidden');
                mobileContent.style.maxHeight = '0px';
                if (mobileIcon) {
                    mobileIcon.style.transform = 'rotate(0deg)';
                }
                isOpen = false;
            }
        });
    });

    // スムーズスクロール
    function giScrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // グローバル関数として公開
    window.giScrollToTop = giScrollToTop;
    </script>

    <?php wp_footer(); ?>

</body>
</html>