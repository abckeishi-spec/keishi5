<?php
/**
 * Hero Section - Stylish Monochrome Design with Rich Features
 * 
 * スタイリッシュな白黒系デザインでリッチな機能を提供
 * - PC・タブレット向けの洗練されたデザイン
 * - デバイスモックアップとビジュアル要素
 * - 充実した機能とインタラクション
 * - プロフェッショナルなアニメーション
 * 
 * @package Grant_Insight_Professional
 * @version 7.0-stylish-monochrome
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

// 統計データ
$hero_stats = array(
    array('number' => '15,847', 'label' => '助成金データベース', 'icon' => 'fas fa-database'),
    array('number' => '98.7%', 'label' => 'AI マッチング精度', 'icon' => 'fas fa-bullseye'),
    array('number' => '24/7', 'label' => '自動更新システム', 'icon' => 'fas fa-sync-alt'),
    array('number' => '無料', 'label' => 'フル機能利用', 'icon' => 'fas fa-gift')
);

// 機能データ
$hero_features = array(
    array('icon' => 'fas fa-robot', 'title' => 'AI自動マッチング', 'desc' => '最新AIが最適な助成金を瞬時に発見'),
    array('icon' => 'fas fa-chart-line', 'title' => 'リアルタイム分析', 'desc' => 'データを24時間自動更新・分析'),
    array('icon' => 'fas fa-user-shield', 'title' => '専門家サポート', 'desc' => '申請から採択まで徹底サポート')
);
?>

<!-- Hero Section -->
<section id="hero-section" class="hero-section relative min-h-screen overflow-hidden" 
         style="background: linear-gradient(135deg, #000000 0%, #1a1a1a 25%, #2d2d30 50%, #1a1a1a 75%, #000000 100%);">
    
    <!-- Background Elements -->
    <div class="absolute inset-0">
        <!-- Animated Grid -->
        <div class="absolute inset-0 opacity-10" 
             style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.15) 1px, transparent 0); background-size: 50px 50px;"></div>
        
        <!-- Floating Geometric Shapes -->
        <div class="absolute top-20 left-10 w-32 h-32 rounded-full opacity-5 animate-pulse" 
             style="background: linear-gradient(135deg, #ffffff 0%, transparent 70%);"></div>
        <div class="absolute top-40 right-20 w-24 h-24 opacity-5 animate-pulse" 
             style="background: linear-gradient(45deg, #ffffff 0%, transparent 70%); transform: rotate(45deg); animation-delay: 1s;"></div>
        <div class="absolute bottom-20 left-1/4 w-16 h-16 rounded-full opacity-5 animate-pulse" 
             style="background: radial-gradient(circle, #ffffff 0%, transparent 70%); animation-delay: 2s;"></div>
        
        <!-- Subtle Lines -->
        <div class="absolute top-0 left-1/3 w-px h-full opacity-5" style="background: linear-gradient(to bottom, transparent 0%, #ffffff 50%, transparent 100%);"></div>
        <div class="absolute top-0 right-1/3 w-px h-full opacity-5" style="background: linear-gradient(to bottom, transparent 0%, #ffffff 50%, transparent 100%);"></div>
    </div>
    
    <div class="relative max-w-7xl mx-auto px-6 lg:px-8 py-20 lg:py-28">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center min-h-[80vh]">
            
            <!-- Left Column - Content -->
            <div class="hero-content space-y-8 lg:space-y-10">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border animate-fade-in-up" 
                     style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                    <div class="w-2 h-2 rounded-full bg-white animate-pulse"></div>
                    <span class="text-sm font-medium text-white">最新AI技術で助成金発見</span>
                </div>
                
                <!-- Main Title -->
                <div class="hero-title space-y-4 animate-fade-in-up" style="animation-delay: 0.2s;">
                    <h1 class="text-5xl lg:text-6xl xl:text-7xl font-bold leading-tight">
                        <span class="text-white block">補助金・助成金を</span>
                        <span class="bg-gradient-to-r from-white via-gray-100 to-white bg-clip-text text-transparent block">
                            AIが瞬時に発見
                        </span>
                    </h1>
                    <p class="text-xl lg:text-2xl text-gray-300 max-w-2xl leading-relaxed">
                        あなたのビジネスに最適な支援制度を、最新AIテクノロジーが瞬時に発見。<br>
                        専門家による申請サポートで<span class="text-white font-semibold">採択率98.7%</span>を実現します。
                    </p>
                </div>
                
                <!-- CTA Buttons -->
                <div class="hero-cta flex flex-col sm:flex-row gap-4 animate-fade-in-up" style="animation-delay: 0.4s;">
                    <a href="#search-section" 
                       class="cta-primary inline-flex items-center justify-center gap-3 px-8 py-4 rounded-xl font-semibold text-lg transition-all transform hover:-translate-y-1" 
                       style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); color: #000000; box-shadow: 0 10px 30px rgba(255,255,255,0.2);"
                       onmouseover="this.style.boxShadow='0 15px 40px rgba(255,255,255,0.3)'; this.style.transform='translateY(-2px) scale(1.02)';" 
                       onmouseout="this.style.boxShadow='0 10px 30px rgba(255,255,255,0.2)'; this.style.transform='translateY(0) scale(1)';">
                        <i class="fas fa-search"></i>
                        <span>無料で助成金を探す</span>
                        <i class="fas fa-arrow-right text-sm"></i>
                    </a>
                    
                    <button class="cta-secondary inline-flex items-center justify-center gap-3 px-8 py-4 rounded-xl font-semibold text-lg transition-all border-2" 
                            style="background: rgba(255,255,255,0.05); color: #ffffff; border-color: rgba(255,255,255,0.2); backdrop-filter: blur(10px);"
                            onmouseover="this.style.background='rgba(255,255,255,0.1)'; this.style.borderColor='rgba(255,255,255,0.4)';" 
                            onmouseout="this.style.background='rgba(255,255,255,0.05)'; this.style.borderColor='rgba(255,255,255,0.2)';">
                        <i class="fas fa-user-tie"></i>
                        <span>専門家に相談する</span>
                    </button>
                </div>
                
                <!-- Stats -->
                <div class="hero-stats grid grid-cols-2 lg:grid-cols-4 gap-6 pt-8 animate-fade-in-up" style="animation-delay: 0.6s;">
                    <?php foreach ($hero_stats as $index => $stat): ?>
                    <div class="stat-item text-center p-4 rounded-xl transition-transform hover:scale-105" 
                         style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1);">
                        <div class="stat-number text-2xl lg:text-3xl font-bold text-white mb-2"><?php echo esc_html($stat['number']); ?></div>
                        <div class="stat-label text-sm text-gray-400 font-medium"><?php echo esc_html($stat['label']); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Right Column - Visual Elements -->
            <div class="hero-visual relative animate-fade-in-left" style="animation-delay: 0.8s;">
                <!-- Main Device Mockup -->
                <div class="device-mockup relative z-10">
                    <!-- Desktop/Tablet View -->
                    <div class="desktop-mockup relative mx-auto" style="max-width: 500px;">
                        <!-- Screen Container -->
                        <div class="screen-container relative rounded-2xl overflow-hidden" 
                             style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); box-shadow: 0 25px 50px rgba(0,0,0,0.3), 0 0 0 1px rgba(255,255,255,0.1); border: 8px solid #2d2d30;">
                            
                            <!-- Screen Content -->
                            <div class="screen-content p-6 space-y-4">
                                <!-- Header -->
                                <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-gray-900 to-gray-700"></div>
                                        <span class="font-semibold text-gray-900">Grant Insight</span>
                                    </div>
                                    <div class="flex gap-2">
                                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                        <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                    </div>
                                </div>
                                
                                <!-- Search Bar -->
                                <div class="relative">
                                    <div class="flex items-center gap-2 p-3 rounded-lg border-2 border-gray-900 bg-white">
                                        <i class="fas fa-search text-gray-400"></i>
                                        <span class="text-gray-900 font-medium">IT導入補助金</span>
                                        <div class="ml-auto">
                                            <div class="animate-pulse">
                                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Results Preview -->
                                <div class="space-y-3">
                                    <?php for ($i = 1; $i <= 3; $i++): ?>
                                    <div class="result-item p-4 rounded-lg border border-gray-200 bg-white hover:shadow-md transition-shadow">
                                        <div class="flex items-start gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-gray-800 to-gray-600 flex items-center justify-center">
                                                <i class="fas fa-coins text-white text-sm"></i>
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-semibold text-gray-900 text-sm mb-1">IT導入補助金 2024</div>
                                                <div class="text-xs text-gray-600 mb-2">最大450万円の支援</div>
                                                <div class="flex items-center gap-2">
                                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">募集中</span>
                                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">マッチ率95%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Floating Elements -->
                        <div class="floating-elements absolute inset-0">
                            <!-- AI Indicator -->
                            <div class="absolute -top-8 -left-8 flex items-center gap-2 px-4 py-2 rounded-full" 
                                 style="background: rgba(0,0,0,0.8); backdrop-filter: blur(10px);">
                                <div class="w-3 h-3 rounded-full bg-green-400 animate-pulse"></div>
                                <span class="text-white text-sm font-medium">AI分析中</span>
                            </div>
                            
                            <!-- Success Rate -->
                            <div class="absolute -bottom-6 -right-6 flex items-center gap-2 px-4 py-2 rounded-full" 
                                 style="background: rgba(0,0,0,0.8); backdrop-filter: blur(10px);">
                                <i class="fas fa-check-circle text-green-400"></i>
                                <span class="text-white text-sm font-medium">98.7% 採択率</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Background Elements -->
                <div class="visual-bg absolute inset-0 -z-10">
                    <!-- Glow Effect -->
                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 rounded-full opacity-20" 
                         style="background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); animation: glow 4s ease-in-out infinite alternate;"></div>
                </div>
            </div>
        </div>
        
        <!-- Features Section -->
        <div class="hero-features mt-20 lg:mt-28 animate-fade-in-up" style="animation-delay: 1s;">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-white mb-4">なぜ私たちが選ばれるのか</h2>
                <p class="text-xl text-gray-300 max-w-3xl mx-auto">最新テクノロジーと専門知識を組み合わせた、革新的な助成金発見プラットフォーム</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($hero_features as $index => $feature): ?>
                <div class="feature-card p-8 rounded-2xl text-center transition-transform hover:-translate-y-2" 
                     style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                    <div class="feature-icon w-16 h-16 mx-auto mb-6 rounded-2xl flex items-center justify-center" 
                         style="background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);">
                        <i class="<?php echo esc_attr($feature['icon']); ?> text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-4"><?php echo esc_html($feature['title']); ?></h3>
                    <p class="text-gray-300 leading-relaxed"><?php echo esc_html($feature['desc']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<style>
/* Hero Section Animations */
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fade-in-left {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes glow {
    0% { transform: translate(-50%, -50%) scale(1); }
    100% { transform: translate(-50%, -50%) scale(1.1); }
}

.animate-fade-in-up {
    animation: fade-in-up 0.8s ease-out forwards;
    opacity: 0;
}

.animate-fade-in-left {
    animation: fade-in-left 0.8s ease-out forwards;
    opacity: 0;
}

/* Smooth Scrolling for CTA */
html {
    scroll-behavior: smooth;
}

/* Responsive Adjustments */
@media (max-width: 1024px) {
    .hero-section .grid {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .hero-visual {
        order: -1;
        max-width: 400px;
        margin: 0 auto;
    }
}

@media (max-width: 768px) {
    .hero-section h1 {
        font-size: 2.5rem;
    }
    
    .hero-section .hero-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .hero-section .cta-primary,
    .hero-section .cta-secondary {
        width: 100%;
        justify-content: center;
    }
}

/* High Performance Optimizations */
.hero-section * {
    will-change: transform;
}

.hero-section .device-mockup {
    backface-visibility: hidden;
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
    .hero-section * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for CTA button
    const ctaButton = document.querySelector('.cta-primary');
    if (ctaButton) {
        ctaButton.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    }
    
    // Animate elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
            }
        });
    }, observerOptions);
    
    // Observe animated elements
    document.querySelectorAll('.animate-fade-in-up, .animate-fade-in-left').forEach(el => {
        el.style.animationPlayState = 'paused';
        observer.observe(el);
    });
    
    // Add parallax effect to background elements
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const rate = scrolled * -0.5;
        
        document.querySelectorAll('.hero-section .absolute').forEach(el => {
            el.style.transform = `translateY(${rate}px)`;
        });
    });
});
</script>