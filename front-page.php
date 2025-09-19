<?php
/**
 * Grant Insight Perfect - Enhanced Front Page (AI機能安全版)
 * 白画面問題解決済み・段階的AI機能復旧版
 * 
 * @package Grant_Insight_Perfect  
 * @version 8.1-enhanced
 */

get_header(); ?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grant Insight Perfect - 助成金・補助金検索システム</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+JP:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        /* カスタムアニメーション */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes pulse-soft {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out;
        }
        
        .animate-pulse-soft {
            animation: pulse-soft 2s ease-in-out infinite;
        }
        
        /* グラデーション背景 */
        .hero-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        /* 検索ボックス強化 */
        .search-container {
            position: relative;
        }
        
        .search-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e5e7eb;
            border-top: none;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-height: 200px;
            overflow-y: auto;
            z-index: 10;
            display: none;
        }
        
        .suggestion-item {
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.2s ease;
        }
        
        .suggestion-item:hover {
            background-color: #f9fafb;
        }
        
        .suggestion-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body class="bg-gray-50">

<!-- ヒーローセクション -->
<section class="hero-gradient min-h-screen flex items-center justify-center relative overflow-hidden">
    <!-- 背景装飾 -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-10 -right-10 w-80 h-80 bg-white bg-opacity-10 rounded-full animate-pulse-soft"></div>
        <div class="absolute -bottom-10 -left-10 w-96 h-96 bg-white bg-opacity-5 rounded-full animate-pulse-soft" style="animation-delay: 1s;"></div>
    </div>
    
    <div class="container mx-auto px-6 relative z-10">
        <div class="text-center text-white animate-fade-in-up">
            <h1 class="text-6xl md:text-7xl font-bold mb-6">
                Grant Insight
                <span class="block text-5xl md:text-6xl font-light mt-2">Perfect</span>
            </h1>
            <p class="text-xl md:text-2xl mb-8 opacity-90 max-w-3xl mx-auto leading-relaxed">
                AI搭載の次世代助成金・補助金検索システム
                <span class="block mt-2">あなたにぴったりの支援制度を瞬時に発見</span>
            </p>
            
            <!-- 統計情報 -->
            <div class="flex justify-center space-x-8 mb-12 text-lg">
                <div class="text-center">
                    <div class="text-3xl font-bold">10,000+</div>
                    <div class="opacity-80">助成金データベース</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold">95%</div>
                    <div class="opacity-80">マッチング精度</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold">24/7</div>
                    <div class="opacity-80">AI相談サポート</div>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="#search-section" class="bg-white text-purple-600 px-8 py-4 rounded-full font-semibold text-lg hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 shadow-lg">
                    <i class="fas fa-search mr-2"></i>今すぐ検索開始
                </a>
                <a href="#ai-consultation" class="border-2 border-white text-white px-8 py-4 rounded-full font-semibold text-lg hover:bg-white hover:text-purple-600 transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-robot mr-2"></i>AI相談を試す
                </a>
            </div>
        </div>
    </div>
    
    <!-- スクロール指示 -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 text-white animate-bounce">
        <i class="fas fa-chevron-down text-2xl"></i>
    </div>
</section>

<!-- 検索セクション -->
<section id="search-section" class="py-20 bg-white">
    <div class="container mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-6">
                智能助成金検索
            </h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                AI技術を活用した高精度マッチングで、あなたの事業に最適な助成金を見つけます
            </p>
        </div>
        
        <div class="max-w-4xl mx-auto">
            <!-- 検索ボックス -->
            <div class="search-container mb-12">
                <div class="relative">
                    <input 
                        type="text" 
                        id="smart-search-input"
                        class="w-full px-6 py-6 text-lg border-2 border-gray-200 rounded-2xl focus:border-purple-500 focus:outline-none transition-all duration-300 pl-14"
                        placeholder="例：創業支援、IT導入、研究開発、雇用助成..."
                    >
                    <i class="fas fa-search absolute left-5 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"></i>
                    <button id="smart-search-btn" class="absolute right-3 top-1/2 transform -translate-y-1/2 bg-purple-600 text-white px-8 py-3 rounded-xl hover:bg-purple-700 transition-colors duration-300 font-semibold">
                        <i class="fas fa-magic mr-2"></i>AI検索
                    </button>
                </div>
                
                <!-- 検索候補 -->
                <div id="search-suggestions" class="search-suggestions">
                    <!-- 動的に生成される検索候補 -->
                </div>
            </div>
            
            <!-- クイック検索タグ -->
            <div class="text-center mb-12">
                <p class="text-gray-600 mb-4">人気の検索キーワード：</p>
                <div class="flex flex-wrap justify-center gap-3">
                    <button class="quick-search-tag bg-gray-100 hover:bg-purple-100 text-gray-700 px-4 py-2 rounded-full transition-colors duration-300" data-keyword="創業支援">
                        創業支援
                    </button>
                    <button class="quick-search-tag bg-gray-100 hover:bg-purple-100 text-gray-700 px-4 py-2 rounded-full transition-colors duration-300" data-keyword="IT導入補助金">
                        IT導入補助金
                    </button>
                    <button class="quick-search-tag bg-gray-100 hover:bg-purple-100 text-gray-700 px-4 py-2 rounded-full transition-colors duration-300" data-keyword="研究開発">
                        研究開発
                    </button>
                    <button class="quick-search-tag bg-gray-100 hover:bg-purple-100 text-gray-700 px-4 py-2 rounded-full transition-colors duration-300" data-keyword="雇用調整助成金">
                        雇用調整助成金
                    </button>
                    <button class="quick-search-tag bg-gray-100 hover:bg-purple-100 text-gray-700 px-4 py-2 rounded-full transition-colors duration-300" data-keyword="省エネ支援">
                        省エネ支援
                    </button>
                </div>
            </div>
            
            <!-- 検索結果表示エリア -->
            <div id="search-results" class="mt-12" style="display: none;">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">検索結果</h3>
                <div id="results-container" class="space-y-6">
                    <!-- 動的に検索結果が表示される -->
                </div>
            </div>
        </div>
    </div>
</section>

<!-- カテゴリセクション -->
<section id="categories-section" class="py-20 bg-gray-50">
    <div class="container mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-6">
                カテゴリ別検索
            </h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                事業分野やステージに応じた助成金を効率的に探索できます
            </p>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- 創業・起業支援 -->
            <div class="category-card bg-white p-8 rounded-2xl shadow-lg card-hover cursor-pointer" data-category="startup">
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-rocket text-3xl text-blue-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">創業・起業支援</h3>
                    <p class="text-gray-600 mb-6">
                        新しく事業を始める方向けの助成金・補助金情報
                    </p>
                    <div class="text-sm text-gray-500">
                        <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full mr-2">創業補助金</span>
                        <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full">スタートアップ支援</span>
                    </div>
                </div>
            </div>
            
            <!-- 研究開発 -->
            <div class="category-card bg-white p-8 rounded-2xl shadow-lg card-hover cursor-pointer" data-category="research">
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-flask text-3xl text-green-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">研究開発</h3>
                    <p class="text-gray-600 mb-6">
                        技術開発・研究活動に関する支援制度
                    </p>
                    <div class="text-sm text-gray-500">
                        <span class="bg-green-50 text-green-700 px-3 py-1 rounded-full mr-2">R&D支援</span>
                        <span class="bg-green-50 text-green-700 px-3 py-1 rounded-full">技術開発</span>
                    </div>
                </div>
            </div>
            
            <!-- IT・デジタル化 -->
            <div class="category-card bg-white p-8 rounded-2xl shadow-lg card-hover cursor-pointer" data-category="digital">
                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-laptop-code text-3xl text-purple-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">IT・デジタル化</h3>
                    <p class="text-gray-600 mb-6">
                        デジタル変革・IT導入支援の補助金
                    </p>
                    <div class="text-sm text-gray-500">
                        <span class="bg-purple-50 text-purple-700 px-3 py-1 rounded-full mr-2">IT導入補助金</span>
                        <span class="bg-purple-50 text-purple-700 px-3 py-1 rounded-full">DX支援</span>
                    </div>
                </div>
            </div>
            
            <!-- 環境・省エネ -->
            <div class="category-card bg-white p-8 rounded-2xl shadow-lg card-hover cursor-pointer" data-category="environment">
                <div class="text-center">
                    <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-leaf text-3xl text-emerald-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">環境・省エネ</h3>
                    <p class="text-gray-600 mb-6">
                        環境保護・省エネルギー関連の支援
                    </p>
                    <div class="text-sm text-gray-500">
                        <span class="bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full mr-2">省エネ補助金</span>
                        <span class="bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full">環境支援</span>
                    </div>
                </div>
            </div>
            
            <!-- 雇用・人材育成 -->
            <div class="category-card bg-white p-8 rounded-2xl shadow-lg card-hover cursor-pointer" data-category="employment">
                <div class="text-center">
                    <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-users text-3xl text-orange-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">雇用・人材育成</h3>
                    <p class="text-gray-600 mb-6">
                        人材採用・教育訓練に関する助成金
                    </p>
                    <div class="text-sm text-gray-500">
                        <span class="bg-orange-50 text-orange-700 px-3 py-1 rounded-full mr-2">雇用助成金</span>
                        <span class="bg-orange-50 text-orange-700 px-3 py-1 rounded-full">人材育成</span>
                    </div>
                </div>
            </div>
            
            <!-- 設備投資 -->
            <div class="category-card bg-white p-8 rounded-2xl shadow-lg card-hover cursor-pointer" data-category="equipment">
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-cogs text-3xl text-red-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">設備投資</h3>
                    <p class="text-gray-600 mb-6">
                        機械設備・施設整備の補助制度
                    </p>
                    <div class="text-sm text-gray-500">
                        <span class="bg-red-50 text-red-700 px-3 py-1 rounded-full mr-2">設備補助金</span>
                        <span class="bg-red-50 text-red-700 px-3 py-1 rounded-full">投資支援</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- AI相談セクション -->
<section id="ai-consultation" class="py-20 bg-white">
    <div class="container mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-6">
                AI相談アシスタント
            </h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                24時間いつでも、AI専門家があなたの質問にお答えします
            </p>
        </div>
        
        <div class="max-w-4xl mx-auto">
            <div class="bg-gradient-to-r from-purple-50 to-blue-50 p-8 rounded-2xl">
                <div class="text-center mb-8">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fas fa-robot text-3xl text-purple-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Grant AI アシスタント</h3>
                    <p class="text-gray-600">お気軽に何でもお聞きください</p>
                </div>
                
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <div class="mb-6">
                        <label class="block text-gray-700 font-semibold mb-2">ご質問をどうぞ：</label>
                        <textarea 
                            id="ai-consultation-input"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-purple-500 focus:outline-none resize-none"
                            rows="4"
                            placeholder="例：IT導入補助金の申請条件を教えてください。うちの会社でも申請できますか？"
                        ></textarea>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-shield-alt mr-1"></i>
                            プライバシー保護・安全な通信
                        </div>
                        <button id="ai-consultation-btn" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition-colors duration-300 font-semibold">
                            <i class="fas fa-paper-plane mr-2"></i>AI に相談する
                        </button>
                    </div>
                </div>
                
                <!-- AI回答表示エリア -->
                <div id="ai-response" class="mt-6" style="display: none;">
                    <div class="bg-white rounded-xl p-6 shadow-lg">
                        <h4 class="font-bold text-gray-800 mb-4">
                            <i class="fas fa-robot text-purple-600 mr-2"></i>AI アシスタントの回答：
                        </h4>
                        <div id="ai-response-content" class="text-gray-700 leading-relaxed">
                            <!-- AI回答が動的に表示される -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    console.log('✅ Grant Insight Perfect - Enhanced Version 読み込み完了');
    
    // 検索機能の初期化
    initializeSearch();
    
    // カテゴリ機能の初期化  
    initializeCategories();
    
    // AI相談機能の初期化
    initializeAIConsultation();
    
    // スムーススクロール
    initializeSmoothScroll();
});

function initializeSearch() {
    const searchInput = document.getElementById('smart-search-input');
    const searchBtn = document.getElementById('smart-search-btn');
    const suggestionsContainer = document.getElementById('search-suggestions');
    const quickSearchTags = document.querySelectorAll('.quick-search-tag');
    
    // 検索候補データ
    const searchSuggestions = [
        '創業支援補助金',
        'IT導入補助金',
        '研究開発助成金',
        '雇用調整助成金',
        'ものづくり補助金',
        '省エネ設備投資支援',
        'スタートアップ支援',
        '事業再構築補助金',
        '小規模事業者持続化補助金',
        'デジタル変革支援'
    ];
    
    // 検索入力時の候補表示
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            
            if (query.length > 0) {
                const matches = searchSuggestions.filter(suggestion => 
                    suggestion.toLowerCase().includes(query)
                );
                
                if (matches.length > 0) {
                    showSuggestions(matches.slice(0, 5));
                } else {
                    hideSuggestions();
                }
            } else {
                hideSuggestions();
            }
        });
        
        // Enter キーで検索実行
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                executeSearch();
            }
        });
    }
    
    // 検索ボタンクリック
    if (searchBtn) {
        searchBtn.addEventListener('click', executeSearch);
    }
    
    // クイック検索タグ
    quickSearchTags.forEach(tag => {
        tag.addEventListener('click', function() {
            const keyword = this.dataset.keyword;
            searchInput.value = keyword;
            executeSearch();
        });
    });
    
    function showSuggestions(suggestions) {
        if (!suggestionsContainer) return;
        
        suggestionsContainer.innerHTML = '';
        suggestions.forEach(suggestion => {
            const item = document.createElement('div');
            item.className = 'suggestion-item';
            item.textContent = suggestion;
            item.addEventListener('click', function() {
                searchInput.value = suggestion;
                executeSearch();
                hideSuggestions();
            });
            suggestionsContainer.appendChild(item);
        });
        
        suggestionsContainer.style.display = 'block';
    }
    
    function hideSuggestions() {
        if (suggestionsContainer) {
            suggestionsContainer.style.display = 'none';
        }
    }
    
    // 検索実行
    function executeSearch() {
        const query = searchInput.value.trim();
        if (!query) return;
        
        hideSuggestions();
        
        // 検索ボタンのローディング状態
        const originalText = searchBtn.innerHTML;
        searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>検索中...';
        searchBtn.disabled = true;
        
        // モック検索結果生成
        setTimeout(() => {
            displaySearchResults(generateMockResults(query));
            
            // ボタンを元に戻す
            searchBtn.innerHTML = originalText;
            searchBtn.disabled = false;
        }, 1500);
    }
    
    function generateMockResults(query) {
        const mockResults = [
            {
                title: '創業支援補助金（最大200万円）',
                description: '新規事業立ち上げを支援する制度。事業計画書の作成から資金調達まで幅広くサポートします。',
                amount: '最大200万円',
                deadline: '2024年3月31日',
                category: '創業支援',
                score: 95
            },
            {
                title: 'IT導入補助金2024（最大450万円）',
                description: 'デジタル化を推進する中小企業向け。ITツールの導入費用を幅広く支援します。',
                amount: '最大450万円',
                deadline: '2024年2月29日',
                category: 'IT・デジタル',
                score: 87
            },
            {
                title: 'ものづくり・商業・サービス生産性向上促進補助金',
                description: '革新的サービス開発・試作品開発・生産プロセスの改善を行う事業者を支援。',
                amount: '最大1000万円',
                deadline: '2024年4月15日',
                category: '製造業支援',
                score: 82
            }
        ];
        
        return mockResults;
    }
    
    function displaySearchResults(results) {
        const resultsSection = document.getElementById('search-results');
        const container = document.getElementById('results-container');
        
        if (!resultsSection || !container) return;
        
        container.innerHTML = '';
        
        results.forEach((result, index) => {
            const resultCard = document.createElement('div');
            resultCard.className = 'bg-white p-6 rounded-xl shadow-lg card-hover';
            resultCard.innerHTML = `
                <div class="flex justify-between items-start mb-4">
                    <h4 class="text-xl font-bold text-gray-800 flex-1 pr-4">${result.title}</h4>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-purple-600">${result.amount}</div>
                        <div class="text-sm text-gray-500">マッチ度: ${result.score}%</div>
                    </div>
                </div>
                <p class="text-gray-600 mb-4">${result.description}</p>
                <div class="flex justify-between items-center">
                    <div class="flex space-x-2">
                        <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-sm">${result.category}</span>
                        <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm">締切: ${result.deadline}</span>
                    </div>
                    <button class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors duration-300">
                        詳細を見る
                    </button>
                </div>
            `;
            
            container.appendChild(resultCard);
        });
        
        resultsSection.style.display = 'block';
        
        // 結果セクションまでスクロール
        resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    
    // 外部クリックで候補を非表示
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            hideSuggestions();
        }
    });
}

function initializeCategories() {
    const categoryCards = document.querySelectorAll('.category-card');
    
    categoryCards.forEach(card => {
        card.addEventListener('click', function() {
            const category = this.dataset.category;
            searchByCategory(category);
        });
    });
    
    function searchByCategory(category) {
        const categoryQueries = {
            'startup': '創業支援',
            'research': '研究開発',
            'digital': 'IT導入',
            'environment': '省エネ支援',
            'employment': '雇用助成',
            'equipment': '設備投資'
        };
        
        const query = categoryQueries[category] || category;
        document.getElementById('smart-search-input').value = query;
        
        // 検索セクションにスクロール
        document.getElementById('search-section').scrollIntoView({ behavior: 'smooth' });
        
        // 少し遅延させて検索実行
        setTimeout(() => {
            executeSearch();
        }, 800);
    }
}

function initializeAIConsultation() {
    const consultationInput = document.getElementById('ai-consultation-input');
    const consultationBtn = document.getElementById('ai-consultation-btn');
    const responseContainer = document.getElementById('ai-response');
    const responseContent = document.getElementById('ai-response-content');
    
    if (consultationBtn) {
        consultationBtn.addEventListener('click', function() {
            const question = consultationInput.value.trim();
            if (!question) {
                alert('ご質問を入力してください。');
                return;
            }
            
            // ローディング状態
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>AI が回答を作成中...';
            this.disabled = true;
            
            // モックAI回答生成
            setTimeout(() => {
                displayAIResponse(generateAIResponse(question));
                
                // ボタンを元に戻す
                this.innerHTML = originalText;
                this.disabled = false;
            }, 2000);
        });
    }
    
    function generateAIResponse(question) {
        // シンプルなキーワードベースの回答生成
        const responses = {
            'IT導入': {
                text: 'IT導入補助金について説明いたします。\n\n**対象者：** 中小企業・小規模事業者\n**補助額：** 最大450万円\n**対象経費：** ソフトウェア購入費、クラウド利用料、導入関連費用\n\n**申請の流れ：**\n1. IT導入支援事業者の選定\n2. 交付申請\n3. IT導入・支払い\n4. 事業実績報告\n\n詳細な申請条件や必要書類については、個別にご相談いただけます。',
                suggestions: ['申請条件の詳細', '必要書類一覧', 'IT導入支援事業者の探し方']
            },
            '創業': {
                text: '創業支援に関する助成金・補助金をご案内いたします。\n\n**主な制度：**\n• 創業支援補助金（最大200万円）\n• 新創業融資制度\n• 地域創業助成金\n\n**支援内容：**\n- 事業立ち上げ資金\n- 事務所賃借料\n- 広告宣伝費\n- 人件費\n\n**申請のポイント：**\n事業計画書の充実度が重要です。市場分析、収益予測、資金計画を詳細に記載することが採択率向上のカギとなります。',
                suggestions: ['事業計画書の作成方法', '創業時の資金調達', '申請スケジュール']
            },
            'default': {
                text: 'ご質問ありがとうございます。\n\n助成金・補助金に関するご相談を承っております。より具体的なアドバイスのために、以下の情報を教えていただけますでしょうか：\n\n• 事業内容・業種\n• 会社規模（従業員数）\n• 具体的な用途・目的\n• 希望する支援額\n\nこれらの情報をもとに、最適な制度をご提案いたします。',
                suggestions: ['具体的な事業内容を相談', '業種別の助成金を調べる', '申請サポートについて']
            }
        };
        
        // キーワードマッチング
        let response = responses.default;
        for (const keyword in responses) {
            if (question.includes(keyword) && keyword !== 'default') {
                response = responses[keyword];
                break;
            }
        }
        
        return response;
    }
    
    function displayAIResponse(response) {
        if (!responseContainer || !responseContent) return;
        
        responseContent.innerHTML = `
            <div class="whitespace-pre-line">${response.text}</div>
            ${response.suggestions ? `
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h5 class="font-semibold text-gray-800 mb-3">関連する質問：</h5>
                    <div class="space-y-2">
                        ${response.suggestions.map(suggestion => 
                            `<button class="block w-full text-left text-purple-600 hover:text-purple-800 hover:bg-purple-50 px-3 py-2 rounded transition-colors duration-300">
                                <i class="fas fa-question-circle mr-2"></i>${suggestion}
                            </button>`
                        ).join('')}
                    </div>
                </div>
            ` : ''}
        `;
        
        responseContainer.style.display = 'block';
        responseContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function initializeSmoothScroll() {
    // アンカーリンクのスムーススクロール
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// パフォーマンス監視
if ('performance' in window) {
    window.addEventListener('load', function() {
        setTimeout(() => {
            const perfData = performance.getEntriesByType('navigation')[0];
            if (perfData) {
                console.log('🚀 ページ読み込み完了:', Math.round(perfData.loadEventEnd - perfData.loadEventStart), 'ms');
            }
        }, 100);
    });
}
</script>

</body>
</html>

<?php get_footer(); ?>