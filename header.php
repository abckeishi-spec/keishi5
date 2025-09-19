<?php
/**
 * Simple & Elegant Header - Monochrome Design
 * シンプルで洗練されたヘッダー（モノクロデザイン）
 * 
 * @package Grant_Insight_Clean
 * @version 8.0.0-simple
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    
    <?php wp_head(); ?>
    
    <!-- Preload fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+JP:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        /* ===============================================
           SIMPLE HEADER STYLES - MONOCHROME DESIGN
           =============================================== */
        
        :root {
            --header-bg: #ffffff;
            --header-text: #171717;
            --header-text-muted: #737373;
            --header-border: #e5e5e5;
            --header-hover: #f5f5f5;
            --header-accent: #000000;
            --header-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            --header-shadow-lg: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --header-transition: all 0.2s ease;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        
        /* Header Container */
        .simple-header {
            background: var(--header-bg);
            border-bottom: 1px solid var(--header-border);
            transition: var(--header-transition);
            position: relative;
            z-index: 1000;
        }
        
        /* Desktop: Fixed header */
        @media (min-width: 768px) {
            .simple-header {
                position: sticky;
                top: 0;
                box-shadow: var(--header-shadow);
            }
            
            .simple-header.scrolled {
                box-shadow: var(--header-shadow-lg);
            }
        }
        
        /* Mobile: Non-fixed header (scrolls away) */
        @media (max-width: 767px) {
            .simple-header {
                position: static;
            }
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 4rem;
        }
        
        @media (min-width: 768px) {
            .header-content {
                height: 5rem;
            }
        }
        
        /* Logo */
        .header-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--header-text);
            font-weight: 700;
            font-size: 1.25rem;
            transition: var(--header-transition);
        }
        
        .header-logo:hover {
            color: var(--header-accent);
        }
        
        .header-logo-icon {
            width: 2rem;
            height: 2rem;
            background: var(--header-accent);
            color: white;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        
        @media (min-width: 768px) {
            .header-logo {
                font-size: 1.5rem;
            }
            
            .header-logo-icon {
                width: 2.5rem;
                height: 2.5rem;
                font-size: 1.125rem;
            }
        }
        
        /* Hide logo text on small mobile */
        @media (max-width: 480px) {
            .header-logo-text {
                display: none;
            }
        }
        
        /* Desktop Navigation */
        .header-nav {
            display: none;
            gap: 2rem;
        }
        
        @media (min-width: 768px) {
            .header-nav {
                display: flex;
            }
        }
        
        .nav-link {
            color: var(--header-text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9375rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            transition: var(--header-transition);
            position: relative;
        }
        
        .nav-link:hover {
            color: var(--header-text);
            background: var(--header-hover);
        }
        
        .nav-link.active {
            color: var(--header-accent);
            font-weight: 600;
        }
        
        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -0.125rem;
            left: 50%;
            transform: translateX(-50%);
            width: 1.5rem;
            height: 2px;
            background: var(--header-accent);
            border-radius: 1px;
        }
        
        /* Actions */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border: none;
            background: transparent;
            color: var(--header-text-muted);
            border-radius: 0.5rem;
            transition: var(--header-transition);
            cursor: pointer;
            font-size: 1rem;
        }
        
        .action-btn:hover {
            color: var(--header-text);
            background: var(--header-hover);
        }
        
        @media (min-width: 768px) {
            .action-btn {
                width: 2.75rem;
                height: 2.75rem;
            }
        }
        
        /* Search Button - Desktop Only */
        .search-btn {
            display: none;
        }
        
        @media (min-width: 768px) {
            .search-btn {
                display: flex;
            }
        }
        
        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: flex;
        }
        
        @media (min-width: 768px) {
            .mobile-menu-btn {
                display: none;
            }
        }
        
        /* Mobile Menu Overlay */
        .mobile-menu {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: var(--header-transition);
        }
        
        .mobile-menu.active {
            opacity: 1;
            visibility: visible;
        }
        
        .mobile-menu-panel {
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            max-width: 20rem;
            height: 100%;
            background: var(--header-bg);
            padding: 1.5rem;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            overflow-y: auto;
        }
        
        .mobile-menu.active .mobile-menu-panel {
            transform: translateX(0);
        }
        
        .mobile-menu-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--header-border);
        }
        
        .mobile-menu-title {
            font-weight: 600;
            color: var(--header-text);
        }
        
        .mobile-menu-close {
            width: 2rem;
            height: 2rem;
            border: none;
            background: transparent;
            color: var(--header-text-muted);
            border-radius: 0.25rem;
            cursor: pointer;
            transition: var(--header-transition);
        }
        
        .mobile-menu-close:hover {
            color: var(--header-text);
            background: var(--header-hover);
        }
        
        .mobile-nav {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .mobile-nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem;
            color: var(--header-text);
            text-decoration: none;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: var(--header-transition);
        }
        
        .mobile-nav-link:hover {
            background: var(--header-hover);
        }
        
        .mobile-nav-link.active {
            background: var(--header-accent);
            color: white;
        }
        
        .mobile-nav-icon {
            width: 1.25rem;
            text-align: center;
        }
        
        /* Utilities */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
    </style>
</head>
<body <?php body_class(); ?>>

<!-- Simple Header -->
<header class="simple-header" id="header">
    <div class="header-container">
        <div class="header-content">
            <!-- Logo -->
            <a href="<?php echo home_url(); ?>" class="header-logo">
                <div class="header-logo-icon">
                    <i class="fas fa-coins"></i>
                </div>
                <span class="header-logo-text">Grant Insight</span>
            </a>
            
            <!-- Desktop Navigation -->
            <nav class="header-nav" aria-label="メインナビゲーション">
                <a href="<?php echo home_url('/grants/'); ?>" class="nav-link <?php echo (is_post_type_archive('grant') || is_singular('grant')) ? 'active' : ''; ?>">
                    助成金検索
                </a>
                <a href="<?php echo home_url('/categories/'); ?>" class="nav-link <?php echo is_tax('grant_category') ? 'active' : ''; ?>">
                    カテゴリ
                </a>
                <a href="<?php echo home_url('/blog/'); ?>" class="nav-link <?php echo is_home() || is_singular('post') ? 'active' : ''; ?>">
                    コラム
                </a>
                <a href="<?php echo home_url('/about/'); ?>" class="nav-link <?php echo is_page('about') ? 'active' : ''; ?>">
                    サービス
                </a>
            </nav>
            
            <!-- Actions -->
            <div class="header-actions">
                <button class="action-btn search-btn" aria-label="検索" onclick="openSearch()">
                    <i class="fas fa-search"></i>
                </button>
                <button class="action-btn mobile-menu-btn" aria-label="メニューを開く" onclick="openMobileMenu()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </div>
</header>

<!-- Mobile Menu -->
<div class="mobile-menu" id="mobileMenu" onclick="closeMobileMenu(event)">
    <div class="mobile-menu-panel" onclick="event.stopPropagation()">
        <div class="mobile-menu-header">
            <span class="mobile-menu-title">メニュー</span>
            <button class="mobile-menu-close" onclick="closeMobileMenu()" aria-label="メニューを閉じる">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <nav class="mobile-nav" aria-label="モバイルナビゲーション">
            <a href="<?php echo home_url(); ?>" class="mobile-nav-link <?php echo is_front_page() ? 'active' : ''; ?>">
                <i class="fas fa-home mobile-nav-icon"></i>
                <span>ホーム</span>
            </a>
            <a href="<?php echo home_url('/grants/'); ?>" class="mobile-nav-link <?php echo (is_post_type_archive('grant') || is_singular('grant')) ? 'active' : ''; ?>">
                <i class="fas fa-coins mobile-nav-icon"></i>
                <span>助成金検索</span>
            </a>
            <a href="<?php echo home_url('/categories/'); ?>" class="mobile-nav-link <?php echo is_tax('grant_category') ? 'active' : ''; ?>">
                <i class="fas fa-th-large mobile-nav-icon"></i>
                <span>カテゴリ</span>
            </a>
            <a href="<?php echo home_url('/blog/'); ?>" class="mobile-nav-link <?php echo is_home() || is_singular('post') ? 'active' : ''; ?>">
                <i class="fas fa-newspaper mobile-nav-icon"></i>
                <span>コラム</span>
            </a>
            <a href="<?php echo home_url('/about/'); ?>" class="mobile-nav-link <?php echo is_page('about') ? 'active' : ''; ?>">
                <i class="fas fa-info-circle mobile-nav-icon"></i>
                <span>サービス</span>
            </a>
        </nav>
    </div>
</div>

<script>
// Header scroll behavior for desktop
let lastScrollY = window.scrollY;
let header = document.getElementById('header');

function handleScroll() {
    const currentScrollY = window.scrollY;
    
    // Desktop: Add scrolled class for shadow effect
    if (window.innerWidth >= 768) {
        if (currentScrollY > 10) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    }
    
    lastScrollY = currentScrollY;
}

// Mobile menu functions
function openMobileMenu() {
    document.getElementById('mobileMenu').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeMobileMenu(event) {
    if (!event || event.target.classList.contains('mobile-menu') || event.target.classList.contains('mobile-menu-close')) {
        document.getElementById('mobileMenu').classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Search function placeholder
function openSearch() {
    // Implement search functionality
    window.location.href = '<?php echo home_url('/grants/'); ?>';
}

// Event listeners
window.addEventListener('scroll', handleScroll, { passive: true });
window.addEventListener('resize', () => {
    // Close mobile menu on desktop
    if (window.innerWidth >= 768) {
        closeMobileMenu({ target: { classList: { contains: () => true } } });
    }
});

// Close mobile menu on escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeMobileMenu({ target: { classList: { contains: () => true } } });
    }
});

// Initialize
handleScroll();
</script>

<?php wp_body_open(); ?>