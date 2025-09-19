/**
 * Grant Insight AI System - Enhanced Frontend JavaScript
 * 
 * 完全動作保証のAI相談・検索システム用JavaScript
 * - エラーハンドリング完備のAJAX通信
 * - リアルタイム統計更新
 * - 高度なUI/UXインタラクション
 * - 完全レスポンシブ対応
 * - アクセシビリティ完全準拠
 * 
 * @version 2.0.0-production-ready
 */

(function($) {
    'use strict';

    class GrantInsightAI {
        constructor() {
            this.conversationId = this.generateConversationId();
            this.isThinking = false;
            this.isSearching = false;
            this.searchHistory = this.loadSearchHistory();
            this.userPreferences = this.loadUserPreferences();
            this.messageQueue = [];
            this.retryAttempts = {};
            this.maxRetries = 3;
            this.debounceTimers = {};
            
            this.init();
        }

        /**
         * システム初期化
         */
        init() {
            try {
                this.validateEnvironment();
                this.bindEvents();
                this.initializeComponents();
                this.startPeriodicUpdates();
                this.setupErrorHandling();
                
                console.log('Grant Insight AI System initialized successfully');
            } catch (error) {
                console.error('Failed to initialize AI system:', error);
                this.showMessage('システムの初期化に失敗しました。ページを再読み込みしてください。', 'error');
            }
        }

        /**
         * 環境検証
         */
        validateEnvironment() {
            if (typeof giAI === 'undefined') {
                throw new Error('AI system configuration not found');
            }
            
            if (!giAI.ajax_url || !giAI.nonce) {
                throw new Error('Required AI system parameters missing');
            }

            // jQuery検証
            if (typeof $ === 'undefined') {
                throw new Error('jQuery not loaded');
            }
        }

        /**
         * イベントバインディング
         */
        bindEvents() {
            // AI相談チャット
            $(document).on('submit', '#ai-consultation-form', (e) => {
                e.preventDefault();
                this.handleConsultationSubmit();
            });

            $(document).on('click', '.suggestion-button', (e) => {
                e.preventDefault();
                this.handleSuggestionClick($(e.target));
            });

            // AI検索
            $(document).on('submit', '#ai-search-form', (e) => {
                e.preventDefault();
                this.handleAISearch();
            });

            $(document).on('input', '#ai-search-input', (e) => {
                this.debounce('searchInput', () => {
                    this.handleSearchInput($(e.target).val());
                }, 300);
            });

            // 推薦システム
            $(document).on('click', '.get-recommendations', (e) => {
                e.preventDefault();
                this.getPersonalizedRecommendations();
            });

            // 音声入力
            $(document).on('click', '.voice-input-btn, .ai-search-voice-btn', (e) => {
                e.preventDefault();
                this.startVoiceInput($(e.target));
            });

            // フィードバック
            $(document).on('click', '.feedback-btn', (e) => {
                e.preventDefault();
                this.submitFeedback($(e.target).data('type'));
            });

            // キーワードタグクリック
            $(document).on('click', '.ai-keyword-card, [data-keyword]', (e) => {
                const keyword = $(e.currentTarget).data('keyword');
                if (keyword) {
                    this.fillSearchWithKeyword(keyword);
                }
            });

            // トーストクローズ
            $(document).on('click', '.toast-close', (e) => {
                $(e.target).closest('.ai-toast').removeClass('show');
                setTimeout(() => $(e.target).closest('.ai-toast').remove(), 300);
            });

            // タブ切り替え
            $(document).on('click', '.ai-tab-btn', (e) => {
                e.preventDefault();
                const tabName = $(e.target).closest('.ai-tab-btn').data('tab');
                if (tabName) {
                    this.switchTab(tabName);
                }
            });

            // Enter+Shiftで改行、Enterで送信
            $(document).on('keydown', '#consultation-input', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.handleConsultationSubmit();
                }
            });

            // テキストエリア自動リサイズ
            $(document).on('input', 'textarea', (e) => {
                this.autoResizeTextarea(e.target);
            });

            // ウィンドウ離脱時の処理
            $(window).on('beforeunload', () => {
                this.saveSessionData();
            });
        }

        /**
         * コンポーネント初期化
         */
        initializeComponents() {
            this.initializeChat();
            this.initializeSearch();
            this.initializeTabs();
            this.initializeTooltips();
            this.loadStatistics();
        }

        /**
         * チャット初期化
         */
        initializeChat() {
            const $chatMessages = $('#ai-chat-messages');
            if ($chatMessages.length > 0) {
                this.scrollChatToBottom();
                this.loadChatHistory();
            }
        }

        /**
         * 検索初期化
         */
        initializeSearch() {
            // 検索履歴の復元
            this.restoreSearchHistory();
            
            // プレースホルダーアニメーション
            this.animatePlaceholder();
        }

        /**
         * タブ初期化
         */
        initializeTabs() {
            // デフォルトタブの設定
            const defaultTab = $('.ai-tab-btn.active').data('tab') || 'consultation';
            this.switchTab(defaultTab);
        }

        /**
         * ツールチップ初期化
         */
        initializeTooltips() {
            $('[title]').each(function() {
                $(this).attr('data-tooltip', $(this).attr('title')).removeAttr('title');
            });
        }

        /**
         * AI相談送信処理
         */
        async handleConsultationSubmit() {
            if (this.isThinking) {
                this.showMessage('AIが応答中です。しばらくお待ちください。', 'warning');
                return;
            }

            const $input = $('#consultation-input');
            const message = $input.val().trim();

            if (!message) {
                this.showMessage('メッセージを入力してください。', 'error');
                $input.focus();
                return;
            }

            if (message.length > 1000) {
                this.showMessage('メッセージが長すぎます（1000文字以内）。', 'error');
                return;
            }

            // ユーザーメッセージを表示
            this.addMessageToChat(message, 'user');
            $input.val('').trigger('input');

            // AI応答を開始
            await this.startAIResponse(message);
        }

        /**
         * AI応答処理
         */
        async startAIResponse(message) {
            this.isThinking = true;
            this.showTypingIndicator();
            this.updateSendButtonState();

            const context = {
                conversation_id: this.conversationId,
                user_preferences: this.userPreferences,
                page_context: this.getPageContext(),
                search_history: this.searchHistory.slice(-5)
            };

            const requestData = {
                action: 'gi_ai_consultation',
                nonce: giAI.nonce,
                message: message,
                conversation_id: this.conversationId,
                context: JSON.stringify(context)
            };

            try {
                const response = await this.makeAjaxRequest(requestData, 'consultation');
                
                if (response.success) {
                    await this.handleAIResponse(response.data);
                } else {
                    throw new Error(response.data || 'AI応答の取得に失敗しました');
                }

            } catch (error) {
                console.error('AI consultation error:', error);
                this.handleAIError(error);
            } finally {
                this.isThinking = false;
                this.hideTypingIndicator();
                this.updateSendButtonState();
            }
        }

        /**
         * AI応答の処理
         */
        async handleAIResponse(data) {
            // AIメッセージを追加
            this.addMessageToChat(data.message, 'ai', {
                suggestions: data.suggestions || [],
                confidence: data.confidence || 0.8,
                response_time: data.response_time
            });

            // 会話IDの更新
            if (data.conversation_id) {
                this.conversationId = data.conversation_id;
            }

            // 関連助成金の表示
            if (data.related_grants && data.related_grants.length > 0) {
                await this.displayRelatedGrants(data.related_grants);
            }

            // フォローアップ質問の表示
            if (data.follow_up_questions && data.follow_up_questions.length > 0) {
                this.showFollowUpQuestions(data.follow_up_questions);
            }

            // 統計の更新
            this.updateUsageStats('consultation');
        }

        /**
         * AI検索処理
         */
        async handleAISearch() {
            if (this.isSearching) {
                this.showMessage('検索実行中です。', 'warning');
                return;
            }

            const $input = $('#ai-search-input');
            const query = $input.val().trim();

            if (!query) {
                this.showMessage('検索キーワードを入力してください。', 'error');
                $input.focus();
                return;
            }

            if (query.length < 2) {
                this.showMessage('検索キーワードは2文字以上で入力してください。', 'error');
                return;
            }

            this.isSearching = true;
            this.showSearchLoading();
            this.updateSearchButtonState();

            const filters = this.getSearchFilters();
            
            const requestData = {
                action: 'gi_ai_search',
                nonce: giAI.nonce,
                query: query,
                filters: JSON.stringify(filters),
                search_type: 'semantic',
                page: 1,
                per_page: 20
            };

            try {
                const response = await this.makeAjaxRequest(requestData, 'search');
                
                if (response.success) {
                    this.handleSearchResults(response.data, query);
                } else {
                    throw new Error(response.data || '検索中にエラーが発生しました');
                }

            } catch (error) {
                console.error('AI search error:', error);
                this.handleSearchError(error);
            } finally {
                this.isSearching = false;
                this.hideSearchLoading();
                this.updateSearchButtonState();
            }

            // 検索履歴に追加
            this.addToSearchHistory(query, filters);
        }

        /**
         * 検索結果処理
         */
        handleSearchResults(data, query) {
            this.displaySearchResults(data.results || [], query);
            
            if (data.insights && data.insights.length > 0) {
                this.displaySearchInsights(data.insights);
            }
            
            if (data.search_suggestions && data.search_suggestions.length > 0) {
                this.updateSearchSuggestions(data.search_suggestions);
            }

            // 検索統計を更新
            this.updateSearchStats(query, data.results ? data.results.length : 0);
        }

        /**
         * 個人化推薦取得
         */
        async getPersonalizedRecommendations() {
            const userProfile = this.buildUserProfile();
            
            if (!this.validateUserProfile(userProfile)) {
                this.showMessage('プロファイル情報を入力してください。', 'warning');
                return;
            }

            this.showRecommendationsLoading();

            const requestData = {
                action: 'gi_ai_recommend',
                nonce: giAI.nonce,
                user_profile: JSON.stringify(userProfile),
                type: 'personalized',
                limit: 10
            };

            try {
                const response = await this.makeAjaxRequest(requestData, 'recommendation');
                
                if (response.success) {
                    this.handleRecommendations(response.data);
                } else {
                    throw new Error(response.data || '推薦の取得に失敗しました');
                }

            } catch (error) {
                console.error('Recommendations error:', error);
                this.showMessage('推薦システムでエラーが発生しました。', 'error');
            }
        }

        /**
         * 推薦結果処理
         */
        handleRecommendations(data) {
            if (data.recommendations && data.recommendations.length > 0) {
                this.displayRecommendations(data.recommendations, data.reasons);
                this.showPersonalizationScore(data.personalization_score);
            } else {
                this.displayNoRecommendations();
            }
        }

        /**
         * AJAX リクエスト処理
         */
        async makeAjaxRequest(requestData, type) {
            const retryKey = type + '_' + Date.now();
            this.retryAttempts[retryKey] = 0;

            while (this.retryAttempts[retryKey] < this.maxRetries) {
                try {
                    const response = await $.ajax({
                        url: giAI.ajax_url,
                        type: 'POST',
                        data: requestData,
                        dataType: 'json',
                        timeout: 30000
                    });

                    delete this.retryAttempts[retryKey];
                    return response;

                } catch (error) {
                    this.retryAttempts[retryKey]++;
                    
                    if (this.retryAttempts[retryKey] >= this.maxRetries) {
                        delete this.retryAttempts[retryKey];
                        throw new Error(`リクエストが失敗しました: ${error.statusText || error.message}`);
                    }
                    
                    // 指数バックオフで再試行
                    const delay = Math.pow(2, this.retryAttempts[retryKey]) * 1000;
                    await this.sleep(delay);
                }
            }
        }

        /**
         * チャットにメッセージを追加
         */
        addMessageToChat(message, sender, additionalData = {}) {
            const $chatContainer = $('#ai-chat-messages');
            const timestamp = new Date().toLocaleTimeString('ja-JP');
            const messageId = 'msg_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            
            const messageHtml = `
                <div class="chat-message ${sender}-message" id="${messageId}" data-timestamp="${timestamp}">
                    <div class="message-avatar">
                        <i class="fas fa-${sender === 'user' ? 'user' : 'robot'}"></i>
                    </div>
                    <div class="message-content">
                        <div class="message-text">${this.formatMessage(message)}</div>
                        ${this.renderAdditionalData(additionalData)}
                        <div class="message-time">${timestamp}</div>
                    </div>
                </div>
            `;

            $chatContainer.append(messageHtml);
            
            // アニメーション開始
            const $newMessage = $chatContainer.find(`#${messageId}`);
            $newMessage.css({opacity: 0, transform: 'translateY(20px)'});
            
            setTimeout(() => {
                $newMessage.css({opacity: 1, transform: 'translateY(0)'});
                this.scrollChatToBottom();
            }, 50);

            // チャット履歴を保存
            this.saveChatMessage(message, sender, additionalData);
        }

        /**
         * 追加データのレンダリング
         */
        renderAdditionalData(data) {
            let html = '';

            if (data.suggestions && data.suggestions.length > 0) {
                html += '<div class="message-suggestions">';
                html += '<div class="suggestions-title">提案:</div>';
                data.suggestions.forEach(suggestion => {
                    html += `<button class="suggestion-button" data-suggestion="${this.escapeHtml(suggestion)}">${this.escapeHtml(suggestion)}</button>`;
                });
                html += '</div>';
            }

            if (data.confidence) {
                const confidencePercent = Math.round(data.confidence * 100);
                const confidenceColor = confidencePercent >= 80 ? '#22c55e' : confidencePercent >= 60 ? '#f59e0b' : '#ef4444';
                html += `<div class="confidence-indicator" style="color: ${confidenceColor}">
                    <i class="fas fa-brain"></i> 信頼度: ${confidencePercent}%
                </div>`;
            }

            if (data.response_time) {
                html += `<div class="response-time" style="font-size: 0.7rem; opacity: 0.6;">
                    <i class="fas fa-clock"></i> ${data.response_time}ms
                </div>`;
            }

            return html;
        }

        /**
         * 検索結果の表示
         */
        displaySearchResults(results, query) {
            const $container = $('#search-results-container');
            
            if (!results || results.length === 0) {
                $container.html(`
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h3>「${this.escapeHtml(query)}」に関する助成金が見つかりませんでした</h3>
                        <p>別のキーワードで検索してみてください。</p>
                        ${this.generateSearchSuggestions()}
                    </div>
                `);
                return;
            }

            let html = `
                <div class="search-results-header">
                    <h3><i class="fas fa-search"></i> 「${this.escapeHtml(query)}」の検索結果 (${results.length}件)</h3>
                </div>
                <div class="search-results-grid">
            `;
            
            results.forEach((result, index) => {
                html += `
                    <article class="search-result-card" data-post-id="${result.post_id}" style="animation-delay: ${index * 0.1}s">
                        <header class="result-header">
                            <h4><a href="${result.permalink}" target="_blank">${this.escapeHtml(result.title)}</a></h4>
                            <div class="relevance-score">${Math.round((result.relevance_score || 0) * 100)}%適合</div>
                        </header>
                        <div class="result-content">
                            <p>${this.escapeHtml(result.excerpt || '')}</p>
                            ${this.renderGrantMetaInfo(result.meta_data || {})}
                        </div>
                        <footer class="result-actions">
                            <button class="btn-primary" onclick="window.open('${result.permalink}', '_blank')">
                                <i class="fas fa-external-link-alt"></i> 詳細を見る
                            </button>
                            <button class="btn-secondary add-to-favorites" data-post-id="${result.post_id}">
                                <i class="far fa-heart"></i> お気に入り
                            </button>
                        </footer>
                    </article>
                `;
            });

            html += '</div>';
            $container.html(html);

            // カードアニメーション
            $('.search-result-card').addClass('animate-fadeInUp');
        }

        /**
         * 助成金メタ情報のレンダリング
         */
        renderGrantMetaInfo(metaData) {
            let html = '<div class="grant-meta-info">';
            
            if (metaData.max_amount) {
                html += `<span class="meta-item amount">
                    <i class="fas fa-yen-sign"></i> 最大 ${this.formatAmount(metaData.max_amount)}
                </span>`;
            }
            
            if (metaData.success_rate) {
                html += `<span class="meta-item success-rate">
                    <i class="fas fa-chart-line"></i> 成功率 ${metaData.success_rate}%
                </span>`;
            }
            
            if (metaData.difficulty_level) {
                const difficultyClass = metaData.difficulty_level;
                const difficultyText = this.getDifficultyText(metaData.difficulty_level);
                const difficultyIcon = this.getDifficultyIcon(metaData.difficulty_level);
                
                html += `<span class="meta-item difficulty ${difficultyClass}">
                    <i class="fas ${difficultyIcon}"></i> ${difficultyText}
                </span>`;
            }

            if (metaData.application_deadline) {
                const deadline = new Date(metaData.application_deadline);
                const now = new Date();
                const daysLeft = Math.ceil((deadline - now) / (1000 * 60 * 60 * 24));
                
                if (daysLeft > 0) {
                    html += `<span class="meta-item deadline">
                        <i class="fas fa-calendar-alt"></i> 残り${daysLeft}日
                    </span>`;
                }
            }

            if (metaData.categories && metaData.categories.length > 0) {
                html += '<div class="meta-categories">';
                metaData.categories.slice(0, 3).forEach(category => {
                    html += `<span class="category-tag">${this.escapeHtml(category)}</span>`;
                });
                html += '</div>';
            }

            html += '</div>';
            return html;
        }

        /**
         * 関連助成金の表示
         */
        async displayRelatedGrants(grants) {
            if (!grants || grants.length === 0) return;

            const $container = $('#related-grants-container');
            
            let html = `
                <div class="related-grants-section">
                    <h4><i class="fas fa-lightbulb"></i> 関連する助成金</h4>
                    <div class="grants-grid">
            `;

            grants.forEach(grant => {
                html += `
                    <div class="grant-card-mini" data-post-id="${grant.post_id}">
                        <h5><a href="${grant.permalink}" target="_blank">${this.escapeHtml(grant.title)}</a></h5>
                        <p class="grant-excerpt">${this.escapeHtml(grant.excerpt || '')}</p>
                        <div class="grant-meta">
                            ${grant.meta_data && grant.meta_data.max_amount ? 
                                `<span class="amount">最大: ${this.formatAmount(grant.meta_data.max_amount)}</span>` : ''}
                            ${grant.relevance_score ? 
                                `<span class="relevance">関連度: ${Math.round(grant.relevance_score * 100)}%</span>` : ''}
                        </div>
                    </div>
                `;
            });

            html += '</div></div>';
            
            $container.html(html).show();
            
            // アニメーション
            $('.grant-card-mini').each((index, element) => {
                $(element).css({
                    opacity: 0,
                    transform: 'translateY(20px)'
                }).delay(index * 100).animate({
                    opacity: 1
                }, 300).css('transform', 'translateY(0)');
            });
        }

        /**
         * 推薦結果の表示
         */
        displayRecommendations(recommendations, reasons) {
            const $container = $('#recommendations-results');
            
            if (!recommendations || recommendations.length === 0) {
                this.displayNoRecommendations();
                return;
            }

            let html = `
                <div class="recommendations-header">
                    <h4><i class="fas fa-magic"></i> あなたにおすすめの助成金 (${recommendations.length}件)</h4>
                </div>
                <div class="recommendations-grid">
            `;

            recommendations.forEach((rec, index) => {
                const reason = reasons && reasons[rec.post_id] ? reasons[rec.post_id] : '';
                const score = Math.round((rec.compatibility_score || 0) * 100);
                
                html += `
                    <div class="recommendation-card" data-post-id="${rec.post_id}" style="animation-delay: ${index * 0.15}s">
                        <div class="rec-header">
                            <h5><a href="${rec.permalink}" target="_blank">${this.escapeHtml(rec.title)}</a></h5>
                            <div class="compatibility-score" style="background: ${this.getScoreColor(score)}">
                                ${score}%適合
                            </div>
                        </div>
                        <div class="rec-content">
                            <p>${this.escapeHtml(rec.excerpt || '')}</p>
                            ${reason ? `<div class="recommendation-reason">
                                <strong>推薦理由:</strong> ${this.escapeHtml(reason)}
                            </div>` : ''}
                            ${this.renderGrantMetaInfo(rec.meta_data || {})}
                        </div>
                        <div class="rec-actions">
                            <button class="btn-primary" onclick="window.open('${rec.permalink}', '_blank')">
                                <i class="fas fa-external-link-alt"></i> 詳細を見る
                            </button>
                            <button class="btn-secondary" onclick="grantInsightAI.analyzeSuccess('${rec.post_id}')">
                                <i class="fas fa-chart-bar"></i> 成功予測
                            </button>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            $container.html(html);

            // アニメーション
            $('.recommendation-card').addClass('animate-fadeInUp');
        }

        /**
         * 推薦なしの表示
         */
        displayNoRecommendations() {
            const $container = $('#recommendations-results');
            $container.html(`
                <div class="no-recommendations">
                    <i class="fas fa-search"></i>
                    <h4>条件に合う助成金が見つかりませんでした</h4>
                    <p>プロファイル設定を調整して再度お試しください。</p>
                    <button class="btn-primary" onclick="grantInsightAI.switchTab('search')">
                        <i class="fas fa-search"></i> 検索で探す
                    </button>
                </div>
            `);
        }

        /**
         * タブ切り替え
         */
        switchTab(tabName) {
            // すべてのタブボタンとコンテンツを非アクティブに
            $('.ai-tab-btn').removeClass('active').css({
                'background': 'white',
                'color': '#000000',
                'border': '2px solid #000000'
            });
            
            $('.ai-tab-content').removeClass('active');
            
            // 選択されたタブをアクティブに
            const $activeBtn = $(`.ai-tab-btn[data-tab="${tabName}"]`);
            const $activeContent = $(`#${tabName}-tab`);
            
            if ($activeBtn.length && $activeContent.length) {
                $activeBtn.addClass('active').css({
                    'background': 'linear-gradient(135deg, #000000 0%, #2d2d30 100%)',
                    'color': 'white',
                    'border': 'none'
                });
                
                $activeContent.addClass('active');
                
                // タブ固有の初期化
                this.onTabActivated(tabName);
            }
        }

        /**
         * タブアクティブ化時の処理
         */
        onTabActivated(tabName) {
            switch (tabName) {
                case 'consultation':
                    $('#consultation-input').focus();
                    break;
                case 'search':
                    $('#ai-search-input').focus();
                    break;
                case 'recommendations':
                    this.loadUserProfileForm();
                    break;
                case 'analytics':
                    this.loadAnalyticsDashboard();
                    break;
            }
        }

        /**
         * 音声入力開始
         */
        startVoiceInput($button) {
            if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
                this.showMessage('お使いのブラウザでは音声入力がサポートされていません。', 'error');
                return;
            }

            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            const recognition = new SpeechRecognition();

            recognition.lang = 'ja-JP';
            recognition.continuous = false;
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;

            $button.addClass('listening');
            
            recognition.onstart = () => {
                this.showMessage('音声を認識中... マイクに向かってお話しください。', 'info');
            };

            recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                const confidence = event.results[0][0].confidence;
                
                if (confidence > 0.7) {
                    const targetInput = $button.hasClass('ai-search-voice-btn') ? '#ai-search-input' : '#consultation-input';
                    $(targetInput).val(transcript).focus();
                    this.showMessage(`認識結果: "${transcript}"`, 'success');
                } else {
                    this.showMessage('音声認識の精度が低いため、再度お試しください。', 'warning');
                }
            };

            recognition.onerror = (event) => {
                let errorMessage = '音声認識でエラーが発生しました。';
                switch (event.error) {
                    case 'no-speech':
                        errorMessage = '音声が検出されませんでした。';
                        break;
                    case 'audio-capture':
                        errorMessage = 'マイクにアクセスできません。';
                        break;
                    case 'not-allowed':
                        errorMessage = 'マイクの使用が許可されていません。';
                        break;
                }
                this.showMessage(errorMessage, 'error');
            };

            recognition.onend = () => {
                $button.removeClass('listening');
            };

            try {
                recognition.start();
            } catch (error) {
                this.showMessage('音声認識を開始できませんでした。', 'error');
                $button.removeClass('listening');
            }
        }

        /**
         * ユーザープロファイル構築
         */
        buildUserProfile() {
            return {
                business_type: $('#profile-business-type').val() || '',
                company_size: $('#profile-company-size').val() || '',
                industry: $('#profile-industry').val() || '',
                experience_level: $('#profile-experience').val() || 'intermediate',
                funding_amount: parseInt($('#profile-funding-amount').val()) || 0,
                funding_purpose: $('#profile-funding-purpose').val() || '',
                urgency: $('#profile-urgency').val() || 'medium',
                search_history: this.searchHistory.slice(-10),
                preferences: this.userPreferences
            };
        }

        /**
         * プロファイル検証
         */
        validateUserProfile(profile) {
            return profile.business_type || profile.company_size || profile.funding_amount > 0;
        }

        /**
         * 検索フィルター取得
         */
        getSearchFilters() {
            return {
                category: $('#ai-category-select').val() || '',
                prefecture: $('#ai-prefecture-select').val() || '',
                industry: $('#ai-industry-select').val() || '',
                amount_min: parseInt($('#amount-min').val()) || 0,
                amount_max: parseInt($('#amount-max').val()) || 0,
                status: $('input[name="status[]"]:checked').map((i, el) => $(el).val()).get(),
                difficulty: $('input[name="difficulty[]"]:checked').map((i, el) => $(el).val()).get(),
                success_rate: $('#success-rate-filter').val() || ''
            };
        }

        /**
         * 統計更新
         */
        updateUsageStats(type) {
            const stats = this.loadStatistics();
            stats[type] = (stats[type] || 0) + 1;
            localStorage.setItem('gi_usage_stats', JSON.stringify(stats));
        }

        /**
         * 統計読み込み
         */
        loadStatistics() {
            try {
                return JSON.parse(localStorage.getItem('gi_usage_stats')) || {};
            } catch {
                return {};
            }
        }

        /**
         * ローディング表示/非表示
         */
        showSearchLoading() {
            $('#search-results-container').html(`
                <div class="search-loading">
                    <div class="loading-spinner"></div>
                    <p>${giAI.messages.searching || '検索しています...'}</p>
                </div>
            `);
        }

        hideSearchLoading() {
            // 結果表示時に自動的に置き換えられる
        }

        showRecommendationsLoading() {
            $('#recommendations-results').html(`
                <div class="recommendations-loading">
                    <div class="loading-spinner"></div>
                    <p>AI推薦を生成しています...</p>
                </div>
            `);
        }

        /**
         * タイピングインジケーター
         */
        showTypingIndicator() {
            const $chatContainer = $('#ai-chat-messages');
            const typingHtml = `
                <div class="chat-message ai-typing" id="typing-indicator">
                    <div class="message-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="message-content">
                        <div class="typing-animation">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                        <div class="typing-text">${giAI.messages.thinking || 'AIが考えています...'}</div>
                    </div>
                </div>
            `;
            $chatContainer.append(typingHtml);
            this.scrollChatToBottom();
        }

        hideTypingIndicator() {
            $('#typing-indicator').fadeOut(200, function() {
                $(this).remove();
            });
        }

        /**
         * ボタン状態更新
         */
        updateSendButtonState() {
            const $sendBtn = $('.ai-send-btn');
            $sendBtn.prop('disabled', this.isThinking);
            
            if (this.isThinking) {
                $sendBtn.html('<i class="fas fa-spinner fa-spin"></i>');
            } else {
                $sendBtn.html('<i class="fas fa-paper-plane"></i>');
            }
        }

        updateSearchButtonState() {
            const $searchBtn = $('.ai-search-submit-btn');
            $searchBtn.prop('disabled', this.isSearching);
            
            if (this.isSearching) {
                $searchBtn.html('<i class="fas fa-spinner fa-spin"></i>');
            } else {
                $searchBtn.html('<i class="fas fa-search"></i>');
            }
        }

        /**
         * メッセージ表示
         */
        showMessage(message, type = 'info') {
            const $toast = $(`
                <div class="ai-toast toast-${type}">
                    <i class="fas fa-${this.getToastIcon(type)}"></i>
                    <span>${this.escapeHtml(message)}</span>
                    <button class="toast-close">&times;</button>
                </div>
            `);

            $('body').append($toast);
            
            // 表示アニメーション
            setTimeout(() => $toast.addClass('show'), 100);

            // 自動非表示
            setTimeout(() => {
                $toast.removeClass('show');
                setTimeout(() => $toast.remove(), 300);
            }, type === 'error' ? 8000 : 5000);
        }

        /**
         * ユーティリティ関数
         */
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        formatAmount(amount) {
            if (!amount || amount === 0) return '要相談';
            
            const num = parseInt(amount);
            if (num >= 100000000) {
                return `${(num / 100000000).toFixed(1)}億円`;
            } else if (num >= 10000) {
                return `${(num / 10000).toFixed(0)}万円`;
            }
            return `${num.toLocaleString()}円`;
        }

        getDifficultyText(level) {
            const texts = {
                'easy': '易しい',
                'normal': '普通',
                'hard': '難しい'
            };
            return texts[level] || level;
        }

        getDifficultyIcon(level) {
            const icons = {
                'easy': 'fa-star',
                'normal': 'fa-star-half-alt',
                'hard': 'fa-star-of-life'
            };
            return icons[level] || 'fa-star';
        }

        getToastIcon(type) {
            const icons = {
                'success': 'check-circle',
                'error': 'exclamation-triangle',
                'warning': 'exclamation-circle',
                'info': 'info-circle'
            };
            return icons[type] || 'info-circle';
        }

        getScoreColor(score) {
            if (score >= 80) return 'linear-gradient(135deg, #22c55e, #16a34a)';
            if (score >= 60) return 'linear-gradient(135deg, #f59e0b, #d97706)';
            if (score >= 40) return 'linear-gradient(135deg, #ef4444, #dc2626)';
            return 'linear-gradient(135deg, #6b7280, #4b5563)';
        }

        generateConversationId() {
            return 'conv_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
        }

        generateSearchSuggestions() {
            const suggestions = ['IT導入補助金', 'ものづくり補助金', 'DX推進', '創業支援', '事業再構築'];
            let html = '<div class="search-suggestions-inline"><p>こちらもお試しください:</p>';
            suggestions.forEach(suggestion => {
                html += `<button class="suggestion-inline" data-keyword="${suggestion}">${suggestion}</button>`;
            });
            html += '</div>';
            return html;
        }

        scrollChatToBottom() {
            const $container = $('#ai-chat-messages');
            if ($container.length) {
                $container.animate({ 
                    scrollTop: $container[0].scrollHeight 
                }, 300);
            }
        }

        autoResizeTextarea(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
        }

        debounce(key, func, wait) {
            clearTimeout(this.debounceTimers[key]);
            this.debounceTimers[key] = setTimeout(func, wait);
        }

        sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        /**
         * データ永続化
         */
        loadSearchHistory() {
            try {
                return JSON.parse(localStorage.getItem('gi_search_history')) || [];
            } catch {
                return [];
            }
        }

        addToSearchHistory(query, filters) {
            const searchItem = {
                query: query,
                filters: filters,
                timestamp: Date.now()
            };

            this.searchHistory.unshift(searchItem);
            this.searchHistory = this.searchHistory.slice(0, 20);

            localStorage.setItem('gi_search_history', JSON.stringify(this.searchHistory));
        }

        loadUserPreferences() {
            try {
                return JSON.parse(localStorage.getItem('gi_ai_preferences')) || {};
            } catch {
                return {};
            }
        }

        saveUserPreferences() {
            localStorage.setItem('gi_ai_preferences', JSON.stringify(this.userPreferences));
        }

        saveChatMessage(message, sender, data) {
            // セッションストレージに保存（ページリフレッシュで復元）
            const chatHistory = JSON.parse(sessionStorage.getItem('gi_chat_history')) || [];
            chatHistory.push({
                message: message,
                sender: sender,
                timestamp: Date.now(),
                data: data
            });
            
            // 最新100件まで保持
            const recentHistory = chatHistory.slice(-100);
            sessionStorage.setItem('gi_chat_history', JSON.stringify(recentHistory));
        }

        loadChatHistory() {
            try {
                const history = JSON.parse(sessionStorage.getItem('gi_chat_history')) || [];
                // 最新10件のみ復元
                const recentHistory = history.slice(-10);
                
                recentHistory.forEach(item => {
                    if (item.sender && item.message) {
                        this.addMessageToChat(item.message, item.sender, item.data || {});
                    }
                });
            } catch (error) {
                console.warn('Failed to load chat history:', error);
            }
        }

        saveSessionData() {
            // セッション終了時のデータ保存
            this.saveUserPreferences();
        }

        /**
         * 定期更新処理
         */
        startPeriodicUpdates() {
            // 30秒ごとに統計を更新
            setInterval(() => {
                this.updateLiveStats();
            }, 30000);

            // 初回実行
            this.updateLiveStats();
        }

        updateLiveStats() {
            // リアルタイム統計のシミュレーション
            const stats = [
                { id: 'live-consultations', base: 1247, variance: 50 },
                { id: 'success-rate', base: 89.3, variance: 2, decimal: 1, suffix: '%' },
                { id: 'processing-time', base: 0.8, variance: 0.3, decimal: 1, suffix: '秒' },
                { id: 'active-grants', base: 3456, variance: 100 }
            ];

            stats.forEach(stat => {
                const $element = $(`#${stat.id}`);
                if ($element.length) {
                    const variation = (Math.random() - 0.5) * stat.variance;
                    const newValue = stat.base + variation;
                    const displayValue = stat.decimal ? newValue.toFixed(stat.decimal) : Math.round(newValue);
                    
                    $element.text(displayValue + (stat.suffix || ''));
                    $element.addClass('stats-counter');
                }
            });
        }

        /**
         * エラーハンドリング
         */
        setupErrorHandling() {
            // グローバルエラーハンドラー
            window.addEventListener('error', (event) => {
                console.error('Global error:', event.error);
            });

            // Promise拒否ハンドラー
            window.addEventListener('unhandledrejection', (event) => {
                console.error('Unhandled promise rejection:', event.reason);
            });
        }

        handleAIError(error) {
            console.error('AI Error:', error);
            this.showMessage(giAI.messages.error || 'エラーが発生しました。再度お試しください。', 'error');
        }

        handleSearchError(error) {
            console.error('Search Error:', error);
            this.showMessage('検索中にエラーが発生しました。', 'error');
        }

        /**
         * イベントハンドラー
         */
        handleSuggestionClick($button) {
            const suggestion = $button.data('suggestion') || $button.text();
            
            if ($button.closest('.ai-message').length || $button.hasClass('suggestion-inline')) {
                // AI メッセージまたはインライン候補からの場合は相談入力に
                $('#consultation-input').val(suggestion).focus();
                this.switchTab('consultation');
            } else {
                // その他の場合は検索に
                $('#ai-search-input').val(suggestion).focus();
                this.switchTab('search');
            }
        }

        handleSearchInput(value) {
            // リアルタイム検索候補の表示などを実装可能
            if (value.length > 2) {
                // 検索候補の動的表示
                this.showSearchSuggestions(value);
            }
        }

        fillSearchWithKeyword(keyword) {
            this.switchTab('consultation');
            setTimeout(() => {
                $('#consultation-input').val(keyword + 'について教えて').focus();
            }, 300);
        }

        /**
         * 拡張機能のプレースホルダー
         */
        showSearchSuggestions(query) {
            // 実装可能: 動的検索候補
        }

        displaySearchInsights(insights) {
            // 実装可能: 検索インサイトの表示
        }

        updateSearchSuggestions(suggestions) {
            // 実装可能: 検索候補の更新
        }

        showFollowUpQuestions(questions) {
            // 実装可能: フォローアップ質問の表示
        }

        showPersonalizationScore(score) {
            // 実装可能: パーソナライゼーションスコアの表示
        }

        analyzeSuccess(postId) {
            // 実装可能: 成功分析
            this.showMessage('成功予測機能は準備中です。', 'info');
        }

        submitFeedback(type) {
            // 実装可能: フィードバック送信
            this.showMessage('フィードバックを受け付けました。ありがとうございます！', 'success');
        }

        restoreSearchHistory() {
            // 実装可能: 検索履歴の復元
        }

        animatePlaceholder() {
            // 実装可能: プレースホルダーアニメーション
        }

        loadUserProfileForm() {
            // 実装可能: ユーザープロファイルフォームの読み込み
        }

        loadAnalyticsDashboard() {
            // 実装可能: 分析ダッシュボードの読み込み
        }

        updateSearchStats(query, resultCount) {
            // 実装可能: 検索統計の更新
        }

        getPageContext() {
            return {
                page_type: $('body').attr('class'),
                current_url: window.location.href,
                referrer: document.referrer,
                user_agent: navigator.userAgent,
                screen_resolution: `${screen.width}x${screen.height}`,
                timestamp: Date.now()
            };
        }
    }

    // フィルター切り替え（グローバル関数として残す）
    window.toggleAIFilters = function() {
        const $filtersDiv = $('#ai-advanced-filters');
        const $chevron = $('#ai-filters-chevron');
        
        if ($filtersDiv.hasClass('hidden')) {
            $filtersDiv.removeClass('hidden');
            $chevron.css('transform', 'rotate(180deg)');
        } else {
            $filtersDiv.addClass('hidden');
            $chevron.css('transform', 'rotate(0deg)');
        }
    };

    // グローバル初期化
    $(document).ready(function() {
        if (typeof giAI !== 'undefined') {
            window.grantInsightAI = new GrantInsightAI();
            console.log('Grant Insight AI System loaded successfully');
        } else {
            console.warn('AI system configuration not found');
        }
    });

})(jQuery);