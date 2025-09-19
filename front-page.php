<?php
/**
 * Grant Insight Perfect - Ultra Simple Front Page
 * 白画面問題解決のため最小構成
 * 
 * @package Grant_Insight_Perfect
 * @version 8.0-minimal
 */

get_header(); ?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grant Insight Perfect</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            margin: 0;
            padding: 20px;
            background: #ffffff;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .hero {
            text-align: center;
            padding: 80px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin-bottom: 60px;
            border-radius: 10px;
        }
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            font-weight: 700;
        }
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        .search-section {
            background: #f8f9fa;
            padding: 60px 40px;
            border-radius: 10px;
            margin-bottom: 60px;
            text-align: center;
        }
        .search-box {
            max-width: 600px;
            margin: 0 auto;
        }
        .search-input {
            width: 100%;
            padding: 15px 20px;
            font-size: 16px;
            border: 2px solid #e9ecef;
            border-radius: 50px;
            outline: none;
            transition: border-color 0.3s ease;
        }
        .search-input:focus {
            border-color: #667eea;
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background: #5a6fd8;
        }
        .categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 60px;
        }
        .category-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }
        .category-card:hover {
            transform: translateY(-5px);
        }
        .category-icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: #667eea;
        }
        .category-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .category-desc {
            color: #666;
            line-height: 1.5;
        }
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }
            .container {
                padding: 20px 10px;
            }
            .search-section {
                padding: 40px 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    
    <!-- ヒーローセクション -->
    <section class="hero">
        <h1>Grant Insight Perfect</h1>
        <p>助成金・補助金検索システム</p>
        <p>あなたにぴったりの助成金を簡単に見つけることができます</p>
    </section>

    <!-- 検索セクション -->
    <section class="search-section">
        <h2>助成金を検索する</h2>
        <div class="search-box">
            <input type="text" class="search-input" placeholder="キーワードを入力してください（例：創業支援、研究開発、IT導入）">
            <br>
            <button class="btn">検索する</button>
        </div>
    </section>

    <!-- カテゴリセクション -->
    <section class="categories">
        <div class="category-card">
            <div class="category-icon">🏢</div>
            <h3 class="category-title">創業・起業支援</h3>
            <p class="category-desc">新しく事業を始める方向けの助成金・補助金情報</p>
        </div>
        
        <div class="category-card">
            <div class="category-icon">🔬</div>
            <h3 class="category-title">研究開発</h3>
            <p class="category-desc">技術開発・研究活動に関する支援制度</p>
        </div>
        
        <div class="category-card">
            <div class="category-icon">💻</div>
            <h3 class="category-title">IT・デジタル化</h3>
            <p class="category-desc">デジタル変革・IT導入支援の補助金</p>
        </div>
        
        <div class="category-card">
            <div class="category-icon">🌱</div>
            <h3 class="category-title">環境・省エネ</h3>
            <p class="category-desc">環境保護・省エネルギー関連の支援</p>
        </div>
        
        <div class="category-card">
            <div class="category-icon">👥</div>
            <h3 class="category-title">雇用・人材育成</h3>
            <p class="category-desc">人材採用・教育訓練に関する助成金</p>
        </div>
        
        <div class="category-card">
            <div class="category-icon">🏭</div>
            <h3 class="category-title">設備投資</h3>
            <p class="category-desc">機械設備・施設整備の補助制度</p>
        </div>
    </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Grant Insight Perfect - シンプル版が読み込まれました');
    
    // 検索ボタンのクリック処理
    const searchBtn = document.querySelector('.btn');
    const searchInput = document.querySelector('.search-input');
    
    if (searchBtn && searchInput) {
        searchBtn.addEventListener('click', function() {
            const keyword = searchInput.value.trim();
            if (keyword) {
                alert('検索機能は開発中です。キーワード: ' + keyword);
            } else {
                alert('検索キーワードを入力してください。');
            }
        });
        
        // Enterキーでも検索
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchBtn.click();
            }
        });
    }
    
    // カテゴリカードのクリック処理
    document.querySelectorAll('.category-card').forEach(card => {
        card.addEventListener('click', function() {
            const title = this.querySelector('.category-title').textContent;
            alert(title + 'の詳細ページは開発中です。');
        });
    });
});
</script>

</body>
</html>

<?php get_footer(); ?>