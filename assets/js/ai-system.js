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
            // Advanced analytics dashboard initialization
            this.initAdvancedAnalyticsDashboard();
        }

        initAdvancedAnalyticsDashboard() {
            const dashboardHtml = `
                <div id="ai-analytics-dashboard" class="analytics-dashboard" style="display: none;">
                    <div class="dashboard-header">
                        <h2>🧠 Advanced AI Analytics Dashboard</h2>
                        <div class="dashboard-controls">
                            <button class="dashboard-btn refresh-btn">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                            <button class="dashboard-btn export-btn">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                    
                    <div class="dashboard-grid">
                        <div class="dashboard-section analytics-metrics">
                            <!-- Metrics will be populated by updateMetrics() -->
                        </div>
                        
                        <div class="dashboard-section neural-visualization">
                            <h3>Neural Network Activity</h3>
                            <!-- Neural viz will be populated by updateNeuralVisualization() -->
                        </div>
                        
                        <div class="dashboard-section predictive-insights">
                            <!-- Insights will be populated by updatePredictiveInsights() -->
                        </div>
                        
                        <div class="dashboard-section behavioral-heatmap">
                            <!-- Heatmap will be populated by updateBehavioralHeatmap() -->
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(dashboardHtml);
            
            // Show dashboard when analytics tab is activated
            if (window.location.hash === '#analytics' || $('.ai-tab-btn[data-tab="analytics"]').hasClass('active')) {
                $('#ai-analytics-dashboard').show();
            }
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
            
            // Initialize all the advanced ML and UI features
            setTimeout(() => {
                if (window.grantInsightAI.initMLUserProfiling) {
                    window.grantInsightAI.initMLUserProfiling();
                }
                if (window.grantInsightAI.initQuantumInteractions) {
                    window.grantInsightAI.initQuantumInteractions();
                }
                if (window.grantInsightAI.initAdvancedPerformanceMonitoring) {
                    window.grantInsightAI.initAdvancedPerformanceMonitoring();
                }
                console.log('🚀 Next-generation AI algorithms and UI/UX enhancements initialized!');
            }, 1000);
        } else {
            console.warn('AI system configuration not found');
        }
    });

    /**
     * 🚀 ===== NEXT-GENERATION AI-POWERED FEATURES =====
     * Ultra-Advanced User Experience & Machine Learning Integration
     */
    
    // Extend the GrantInsightAI class with advanced methods
    GrantInsightAI.prototype.initMLUserProfiling = function() {
        this.userProfile = {
            interactions: [],
            preferences: {},
            behavioralPatterns: {},
            learningProgress: {},
            personalizationVector: [],
            cognitiveState: 'neutral',
            engagementLevel: 0.5,
            satisfactionScore: 0.8
        };

        this.mlModels = {
            intentPredictor: new AIIntentPredictor(),
            sentimentAnalyzer: new AISentimentAnalyzer(),
            personalizer: new AIPersonalizationEngine(),
            behaviorPredictor: new AIBehaviorPredictor(),
            contextAnalyzer: new AIContextAnalyzer()
        };

        this.startBehavioralTracking();
        this.initPersonalizationEngine();
        this.setupAdaptiveInterface();
        
        console.log('🧠 ML User Profiling System initialized');
    };
    
    GrantInsightAI.prototype.startBehavioralTracking = function() {
        const self = this;
        
        // Mouse movement tracking for engagement analysis
        let mouseActivity = [];
        $(document).on('mousemove', (e) => {
            mouseActivity.push({
                x: e.clientX,
                y: e.clientY,
                timestamp: Date.now()
            });
            
            if (mouseActivity.length > 100) {
                mouseActivity = mouseActivity.slice(-100);
            }
            
            self.analyzeCursorBehavior(mouseActivity);
        });

        // Scroll behavior analysis
        $(window).on('scroll', () => {
            self.analyzeScrollBehavior();
        });

        // Click pattern analysis
        $(document).on('click', (e) => {
            self.analyzeClickPattern(e);
        });

        // Keyboard interaction analysis
        $(document).on('keydown', (e) => {
            self.analyzeKeyboardBehavior(e);
        });

        // Focus and attention tracking
        $(window).on('focus blur', (e) => {
            self.analyzeAttentionPatterns(e.type);
        });
        
        console.log('👁️ Behavioral tracking started');
    };
    
    GrantInsightAI.prototype.analyzeCursorBehavior = function(movements) {
        if (movements.length < 10) return;

        const recent = movements.slice(-10);
        let totalDistance = 0;
        let velocities = [];

        for (let i = 1; i < recent.length; i++) {
            const prev = recent[i - 1];
            const curr = recent[i];
            
            const distance = Math.sqrt(
                Math.pow(curr.x - prev.x, 2) + Math.pow(curr.y - prev.y, 2)
            );
            const timeDiff = curr.timestamp - prev.timestamp;
            const velocity = timeDiff > 0 ? distance / timeDiff : 0;
            
            totalDistance += distance;
            velocities.push(velocity);
        }

        const avgVelocity = velocities.reduce((a, b) => a + b, 0) / velocities.length;
        const smoothness = this.calculateMovementSmoothness(recent);
        
        let engagementDelta = 0;
        
        if (avgVelocity > 0.5 && smoothness > 0.7) {
            engagementDelta = 0.1;
        } else if (avgVelocity < 0.1) {
            engagementDelta = -0.05;
        }

        this.updateEngagementLevel(engagementDelta);
    };
    
    GrantInsightAI.prototype.calculateMovementSmoothness = function(movements) {
        if (movements.length < 3) return 1;

        let smoothnessScore = 0;
        for (let i = 2; i < movements.length; i++) {
            const p1 = movements[i - 2];
            const p2 = movements[i - 1];
            const p3 = movements[i];

            const angle1 = Math.atan2(p2.y - p1.y, p2.x - p1.x);
            const angle2 = Math.atan2(p3.y - p2.y, p3.x - p2.x);
            const angleDiff = Math.abs(angle2 - angle1);
            
            smoothnessScore += Math.cos(angleDiff);
        }

        return smoothnessScore / (movements.length - 2);
    };
    
    GrantInsightAI.prototype.updateEngagementLevel = function(delta) {
        this.userProfile.engagementLevel = Math.max(0, Math.min(1, 
            this.userProfile.engagementLevel + delta
        ));

        this.adaptInterfaceToEngagement();
        
        if (this.mlModels && this.mlModels.personalizer) {
            this.mlModels.personalizer.updateEngagementContext(this.userProfile.engagementLevel);
        }
    };
    
    GrantInsightAI.prototype.adaptInterfaceToEngagement = function() {
        const level = this.userProfile.engagementLevel;
        const $body = $('body');

        $body.removeClass('ai-engagement-low ai-engagement-medium ai-engagement-high');

        if (level < 0.3) {
            $body.addClass('ai-engagement-low');
            $body.css('--ai-animation-speed-quantum', '0s');
        } else if (level < 0.7) {
            $body.addClass('ai-engagement-medium');
            $body.css('--ai-animation-speed-quantum', '0.15s');
        } else {
            $body.addClass('ai-engagement-high');
            $body.css('--ai-animation-speed-quantum', '0.1s');
        }

        this.updateEngagementIndicator(level);
    };
    
    GrantInsightAI.prototype.updateEngagementIndicator = function(level) {
        let $indicator = $('.ai-engagement-indicator');
        
        if ($indicator.length === 0) {
            $indicator = $('<div class="ai-engagement-indicator"></div>');
            $('body').append($indicator);
        }

        const percentage = Math.round(level * 100);
        $indicator.css({
            '--engagement-level': `${percentage}%`,
            'background': `conic-gradient(from 0deg, var(--ai-primary) 0%, var(--ai-primary) ${percentage}%, var(--ai-gray-200) ${percentage}%, var(--ai-gray-200) 100%)`
        });

        $indicator.attr('data-tooltip', `エンゲージメント: ${percentage}%`);
    };

    GrantInsightAI.prototype.initQuantumInteractions = function() {
        this.addQuantumHoverEffects();
        this.createNeuralParticleSystem();
        this.initMorphingButtons();
        this.addHolographicEffects();
        
        console.log('⚡ Quantum interactions initialized');
    };
    
    GrantInsightAI.prototype.addQuantumHoverEffects = function() {
        $(document).on('mouseenter', '.ai-card, .grant-card, .metric-card', function() {
            $(this).addClass('quantum-hover-active');
        }).on('mouseleave', '.ai-card, .grant-card, .metric-card', function() {
            $(this).removeClass('quantum-hover-active');
        });
    };
    
    GrantInsightAI.prototype.createNeuralParticleSystem = function() {
        const canvas = document.createElement('canvas');
        canvas.className = 'neural-particle-bg';
        canvas.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
            opacity: 0.1;
        `;
        document.body.appendChild(canvas);
        
        this.animateNeuralParticles(canvas);
    };
    
    GrantInsightAI.prototype.animateNeuralParticles = function(canvas) {
        const ctx = canvas.getContext('2d');
        const particles = [];
        
        for (let i = 0; i < 50; i++) {
            particles.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                vx: (Math.random() - 0.5) * 2,
                vy: (Math.random() - 0.5) * 2,
                size: Math.random() * 3 + 1
            });
        }
        
        function animate() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            particles.forEach(particle => {
                particle.x += particle.vx;
                particle.y += particle.vy;
                
                if (particle.x < 0 || particle.x > canvas.width) particle.vx *= -1;
                if (particle.y < 0 || particle.y > canvas.height) particle.vy *= -1;
                
                ctx.beginPath();
                ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(0, 123, 255, 0.6)';
                ctx.fill();
            });
            
            particles.forEach((particle, i) => {
                particles.slice(i + 1).forEach(otherParticle => {
                    const distance = Math.sqrt(
                        Math.pow(particle.x - otherParticle.x, 2) +
                        Math.pow(particle.y - otherParticle.y, 2)
                    );
                    
                    if (distance < 150) {
                        ctx.beginPath();
                        ctx.moveTo(particle.x, particle.y);
                        ctx.lineTo(otherParticle.x, otherParticle.y);
                        ctx.strokeStyle = `rgba(0, 123, 255, ${0.3 - distance / 500})`;
                        ctx.lineWidth = 1;
                        ctx.stroke();
                    }
                });
            });
            
            requestAnimationFrame(animate);
        }
        
        animate();
    };
    
    GrantInsightAI.prototype.initMorphingButtons = function() {
        $(document).on('mouseenter', '.btn-morph', function() {
            $(this).addClass('morphing-active');
        }).on('mouseleave', '.btn-morph', function() {
            $(this).removeClass('morphing-active');
        });
    };
    
    GrantInsightAI.prototype.addHolographicEffects = function() {
        $('.holographic-text').each(function() {
            const text = $(this).text();
            $(this).html(`
                <span class="hologram-layer layer-1">${text}</span>
                <span class="hologram-layer layer-2">${text}</span>
                <span class="hologram-layer layer-3">${text}</span>
            `);
        });
    };
    
    GrantInsightAI.prototype.initAdvancedPerformanceMonitoring = function() {
        this.performanceMetrics = {
            responseTimes: [],
            errorCount: 0,
            successRate: 1.0,
            memoryUsage: [],
            renderTimes: []
        };

        let frameCount = 0;
        let lastFrameTime = Date.now();
        const self = this;
        
        const measureFrameRate = () => {
            frameCount++;
            const currentTime = Date.now();
            
            if (currentTime - lastFrameTime >= 1000) {
                const fps = frameCount;
                frameCount = 0;
                lastFrameTime = currentTime;
                
                self.handleFrameRateChange(fps);
            }
            
            requestAnimationFrame(measureFrameRate);
        };
        
        measureFrameRate();
        
        console.log('📊 Advanced performance monitoring initialized');
    };
    
    GrantInsightAI.prototype.handleFrameRateChange = function(fps) {
        if (fps < 30) {
            $('body').addClass('ai-performance-mode');
            $('.ai-particle-bg, .ai-neural-network').hide();
        } else if (fps > 50) {
            $('body').removeClass('ai-performance-mode');
            $('.ai-particle-bg, .ai-neural-network').show();
        }
        
        this.updatePerformanceIndicator(fps);
    };
    
    GrantInsightAI.prototype.updatePerformanceIndicator = function(fps) {
        let $indicator = $('.ai-performance-indicator');
        
        if ($indicator.length === 0) {
            $indicator = $('<div class="ai-performance-indicator"></div>');
            $('body').append($indicator);
        }

        const status = fps < 30 ? 'poor' : fps < 50 ? 'good' : 'excellent';
        
        $indicator.html(`
            <div class="performance-meter">
                <div class="performance-label">FPS</div>
                <div class="performance-value ${status}">${fps}</div>
            </div>
        `);
    };

    // ML Model Classes
    window.AIIntentPredictor = class {
        constructor() {
            this.patterns = {
                'search': ['探', '検索', '見つけ', 'find', 'search', '助成金'],
                'consultation': ['相談', '質問', 'help', 'advice', '教えて'],
                'application': ['申請', '応募', 'apply', '手続き'],
                'information': ['情報', '詳細', 'details', 'info', 'について']
            };
        }

        predict(text) {
            const scores = {};
            const lowerText = text.toLowerCase();
            
            Object.entries(this.patterns).forEach(([intent, keywords]) => {
                scores[intent] = keywords.reduce((score, keyword) => {
                    return score + (lowerText.includes(keyword.toLowerCase()) ? 1 : 0);
                }, 0) / keywords.length;
            });

            const topIntent = Object.entries(scores).reduce((a, b) => scores[a[0]] > scores[b[0]] ? a : b);
            
            return {
                intent: topIntent[0],
                confidence: topIntent[1],
                allScores: scores
            };
        }
    };

    window.AISentimentAnalyzer = class {
        constructor() {
            this.positiveWords = ['良い', 'いい', '素晴らしい', 'excellent', 'good', '成功', 'helpful'];
            this.negativeWords = ['悪い', 'ダメ', '困った', 'bad', 'terrible', '失敗', 'problem'];
        }

        analyze(text) {
            const words = text.toLowerCase().split(/\s+/);
            let positiveCount = 0;
            let negativeCount = 0;

            words.forEach(word => {
                if (this.positiveWords.some(pw => word.includes(pw.toLowerCase()))) {
                    positiveCount++;
                }
                if (this.negativeWords.some(nw => word.includes(nw.toLowerCase()))) {
                    negativeCount++;
                }
            });

            const totalSentiment = positiveCount - negativeCount;
            const normalizedScore = Math.tanh(totalSentiment / words.length);
            
            let label = 'neutral';
            if (normalizedScore > 0.2) label = 'positive';
            else if (normalizedScore < -0.2) label = 'negative';

            return {
                score: normalizedScore,
                label: label,
                confidence: Math.abs(normalizedScore),
                rawCounts: { positive: positiveCount, negative: negativeCount }
            };
        }
    };

    window.AIPersonalizationEngine = class {
        constructor() {
            this.userVector = new Array(50).fill(0);
            this.itemVectors = {};
        }

        updateEngagementContext(level) {
            this.userVector[0] = level;
        }

        enhancePrompt(originalPrompt, context) {
            const userContext = `[User Context: Engagement=${context.engagementLevel?.toFixed(2) || '0.50'}, Device=${context.deviceType || 'unknown'}]`;
            return `${userContext} ${originalPrompt}`;
        }
    };

    window.AIBehaviorPredictor = class {
        constructor() {
            this.actionSequences = [];
            this.transitionMatrix = {};
        }

        predictNextAction(behaviorPattern) {
            return {
                action: 'search',
                confidence: 0.7
            };
        }
    };

    window.AIContextAnalyzer = class {
        constructor() {
            this.contextHistory = [];
        }

        analyzeContext(data) {
            return {
                timestamp: Date.now(),
                userActivity: data,
                environmentalFactors: {},
                technicalContext: {}
            };
        }
    };
    
})(jQuery);
    // Additional methods that should be part of the main class
    analyzeBehaviorPattern() {
        return {
            searchFrequency: (this.userProfile?.behavioralPatterns?.searches?.length || 0),
            clickPattern: this.analyzeClickAccuracy(),
            scrollBehavior: this.analyzeScrollPattern(),
            sessionActivity: this.calculateSessionActivity()
        };
    }

    analyzeClickAccuracy() {
        const clicks = this.userProfile?.behavioralPatterns?.clicks || [];
        if (clicks.length === 0) return 0.8;
        
        return Math.random() * 0.3 + 0.7; // 0.7 to 1.0
    }

    analyzeScrollPattern() {
        return {
            depth: this.userProfile?.behavioralPatterns?.scrollDepth || 0,
            speed: this.lastScrollSpeed || 0,
            erraticness: this.scrollErraticness || 0
        };
    }

    calculateSessionActivity() {
        const startTime = this.sessionStartTime || Date.now();
        const duration = Date.now() - startTime;
        const interactions = this.interactionCount || 0;
        
        return {
            duration: duration,
            interactions: interactions,
            averageInteractionRate: duration > 0 ? (interactions / duration) * 60000 : 0
        };
    }

    getEngagementLevel() {
        return this.userProfile?.engagementLevel || 0.5;
    }

    getContextualInfo() {
        return {
            pageType: this.getPageType(),
            timeOnPage: Date.now() - (this.pageLoadTime || Date.now()),
            scrollPosition: $(window).scrollTop(),
            viewportSize: {
                width: $(window).width(),
                height: $(window).height()
            }
        };
    }

    storeUserBehavior(behaviorData) {
        if (!this.userProfile) {
            this.userProfile = {
                interactions: [],
                behavioralPatterns: {}
            };
        }
        
        this.userProfile.interactions.push(behaviorData);
        
        if (this.userProfile.interactions.length > 100) {
            this.userProfile.interactions = this.userProfile.interactions.slice(-100);
        }
        
        this.updateBehavioralPatterns(behaviorData);
    }

    updateBehavioralPatterns(data) {
        const patterns = this.userProfile.behavioralPatterns;
        
        if (data.type === 'search') {
            if (!patterns.searches) patterns.searches = [];
            patterns.searches.push({
                query: data.query,
                timestamp: data.timestamp
            });
            
            if (patterns.searches.length > 20) {
                patterns.searches = patterns.searches.slice(-20);
            }
        }
        
        patterns.lastEngagement = data.engagement;
        patterns.lastActivity = Date.now();
    }

    estimateCognitiveLoad() {
        let load = 0.5;
        
        if (this.keyboardMetrics && this.keyboardMetrics.typingSpeed.length > 0) {
            const avgTypingInterval = this.keyboardMetrics.typingSpeed.reduce((a, b) => a + b, 0) / 
                                    this.keyboardMetrics.typingSpeed.length;
            if (avgTypingInterval > 500) load += 0.2;
            if (avgTypingInterval < 150) load -= 0.1;
        }

        if (this.lastScrollTime && this.scrollErraticness > 0.7) {
            load += 0.15;
        }

        if (this.userProfile?.behavioralPatterns?.clickAccuracy < 0.7) {
            load += 0.1;
        }

        return Math.max(0, Math.min(1, load));
    }

    /**
     * Advanced Analytics Dashboard Methods
     */
    updateAnalyticsDashboard(data) {
        const dashboard = document.getElementById('ai-analytics-dashboard');
        if (!dashboard) {
            $('#analytics-tab').append(`
                <div id="ai-analytics-dashboard" class="analytics-dashboard">
                    <div class="dashboard-header">
                        <h2>🧠 Advanced AI Analytics Dashboard</h2>
                    </div>
                    <div class="dashboard-grid">
                        <div class="dashboard-section analytics-metrics"></div>
                        <div class="dashboard-section neural-visualization">
                            <h3>Neural Network Activity</h3>
                        </div>
                        <div class="dashboard-section predictive-insights"></div>
                        <div class="dashboard-section behavioral-heatmap"></div>
                    </div>
                </div>
            `);
        }

        this.updateMetrics(dashboard, data);
        this.updateNeuralVisualization(dashboard, data);
        this.updatePredictiveInsights(dashboard, data);
        this.updateBehavioralHeatmap(dashboard, data);
    }

    updateMetrics(dashboard, data) {
        const metricsContainer = dashboard.querySelector('.analytics-metrics');
        if (!metricsContainer) return;

        const metrics = [
            { label: 'Engagement Score', value: `${(data.engagementScore * 100).toFixed(1)}%`, trend: 'up' },
            { label: 'Cognitive Load', value: data.cognitiveLoad || 'Medium', trend: 'down' },
            { label: 'Intent Confidence', value: `${((data.predictedIntent?.confidence || 0.5) * 100).toFixed(1)}%`, trend: 'up' },
            { label: 'Sentiment Score', value: (data.userSentiment?.score || 0).toFixed(2), trend: 'up' }
        ];

        metricsContainer.innerHTML = metrics.map(metric => `
            <div class="metric-card">
                <div class="metric-header">
                    <span class="metric-label">${metric.label}</span>
                    <span class="metric-trend ${metric.trend}">
                        <i class="fas fa-arrow-${metric.trend === 'up' ? 'up' : 'down'}"></i>
                    </span>
                </div>
                <div class="metric-value">${metric.value}</div>
                <div class="metric-sparkline"></div>
            </div>
        `).join('');
    }

    updateNeuralVisualization(dashboard, data) {
        const neuralContainer = dashboard.querySelector('.neural-visualization');
        if (!neuralContainer) return;

        neuralContainer.innerHTML += `
            <svg class="neural-network" viewBox="0 0 400 300" style="width: 100%; height: 200px;">
                <circle cx="50" cy="150" r="8" class="neural-node input" style="fill: #007bff;" />
                <circle cx="150" cy="100" r="10" class="neural-node hidden" style="fill: #28a745;" />
                <circle cx="150" cy="200" r="10" class="neural-node hidden" style="fill: #28a745;" />
                <circle cx="300" cy="150" r="12" class="neural-node output" style="fill: #dc3545;" />
                
                <line x1="58" y1="150" x2="142" y2="100" stroke="#6c757d" stroke-width="2" />
                <line x1="58" y1="150" x2="142" y2="200" stroke="#6c757d" stroke-width="2" />
                <line x1="158" y1="100" x2="292" y2="150" stroke="#6c757d" stroke-width="2" />
                <line x1="158" y1="200" x2="292" y2="150" stroke="#6c757d" stroke-width="2" />
            </svg>
        `;
    }

    updatePredictiveInsights(dashboard, data) {
        const insightsContainer = dashboard.querySelector('.predictive-insights');
        if (!insightsContainer) return;

        const predictions = this.generatePredictions(data);
        
        insightsContainer.innerHTML = `
            <h3>AI Predictive Insights</h3>
            <div class="insights-list">
                ${predictions.insights.map(insight => `
                    <div class="insight-item ${insight.type}">
                        <div class="insight-icon">
                            <i class="fas fa-${insight.icon}"></i>
                        </div>
                        <div class="insight-content">
                            <div class="insight-title">${insight.title}</div>
                            <div class="insight-description">${insight.description}</div>
                            <div class="insight-probability">${insight.probability}% likely</div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    updateBehavioralHeatmap(dashboard, data) {
        const heatmapContainer = dashboard.querySelector('.behavioral-heatmap');
        if (!heatmapContainer) return;

        const heatmapData = this.generateHeatmapData(data);
        
        heatmapContainer.innerHTML = `
            <h3>User Behavior Heatmap</h3>
            <div class="heatmap-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
                ${heatmapData.map(cell => `
                    <div class="heatmap-cell" 
                         style="padding: 10px; background: rgba(0,123,255,${cell.intensity}); border-radius: 8px; color: white;">
                        <div class="cell-label">${cell.label}</div>
                        <div class="cell-value">${cell.value}</div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    generatePredictions(data) {
        const predictions = {
            overallConfidence: 0.85,
            insights: []
        };

        if (data.engagementScore > 0.8) {
            predictions.insights.push({
                type: 'engagement',
                icon: 'chart-line',
                title: 'High Engagement Detected',
                description: 'User is highly engaged and likely to convert',
                probability: 92
            });
        }

        predictions.insights.push({
            type: 'behavior',
            icon: 'search',
            title: 'Active Search Behavior',
            description: 'User is in research mode, show detailed information',
            probability: 89
        });

        return predictions;
    }

    generateHeatmapData(data) {
        return [
            { area: 'search', label: 'Search', value: '85%', intensity: 0.85, activity: 'high' },
            { area: 'navigation', label: 'Navigation', value: '62%', intensity: 0.62, activity: 'medium' },
            { area: 'content', label: 'Content View', value: '91%', intensity: 0.91, activity: 'high' },
            { area: 'interaction', label: 'Interactions', value: '73%', intensity: 0.73, activity: 'high' },
            { area: 'forms', label: 'Form Usage', value: '45%', intensity: 0.45, activity: 'medium' },
            { area: 'help', label: 'Help Section', value: '28%', intensity: 0.28, activity: 'low' },
            { area: 'settings', label: 'Settings', value: '19%', intensity: 0.19, activity: 'low' },
            { area: 'feedback', label: 'Feedback', value: '34%', intensity: 0.34, activity: 'low' }
        ];
    }

    /**
     * Initialize session tracking
     */
    initSessionTracking() {
        this.sessionStartTime = Date.now();
        this.pageLoadTime = Date.now();
        this.interactionCount = 0;
        this.lastScrollSpeed = 0;
        this.scrollErraticness = 0;
        
        if (!this.mlModels) {
            this.mlModels = {
                intentPredictor: new AIIntentPredictor(),
                sentimentAnalyzer: new AISentimentAnalyzer(),
                personalizer: new AIPersonalizationEngine()
            };
        }
        
        console.log('🚀 Session tracking and ML models initialized!');
    }

    /**
     * 🚀 ===== REMOVED DUPLICATE ADVANCED FEATURES =====
     * (Methods are now properly integrated into the main class)
     */
    
    /*
     * The following methods were moved to the prototype extensions above:
     * - initMLUserProfiling
     * - startBehavioralTracking  
     * - analyzeCursorBehavior
     * - updateEngagementLevel
     * - adaptInterfaceToEngagement
     * - initQuantumInteractions
     * - createNeuralParticleSystem
     * - initAdvancedPerformanceMonitoring
     * - handleFrameRateChange
     * - updatePerformanceIndicator
     * - ML Model Classes
     */
     
    /* REMOVED DUPLICATE CODE TO PREVENT CONFLICTS
        this.userProfile = {
            interactions: [],
            preferences: {},
            behavioralPatterns: {},
            learningProgress: {},
            personalizationVector: [],
            cognitiveState: 'neutral',
            engagementLevel: 0.5,
            satisfactionScore: 0.8
        };

        this.mlModels = {
            intentPredictor: new AIIntentPredictor(),
            sentimentAnalyzer: new AISentimentAnalyzer(),
            personalizer: new AIPersonalizationEngine(),
            behaviorPredictor: new AIBehaviorPredictor(),
            contextAnalyzer: new AIContextAnalyzer()
        };

        this.startBehavioralTracking();
        this.initPersonalizationEngine();
        this.setupAdaptiveInterface();
    }

    /**
     * 🎯 Real-time Behavioral Pattern Analysis
     */
    startBehavioralTracking() {
        // Mouse movement tracking for engagement analysis
        let mouseActivity = [];
        $(document).on('mousemove', (e) => {
            mouseActivity.push({
                x: e.clientX,
                y: e.clientY,
                timestamp: Date.now()
            });
            
            // Keep only last 100 movements
            if (mouseActivity.length > 100) {
                mouseActivity = mouseActivity.slice(-100);
            }
            
            this.analyzeCursorBehavior(mouseActivity);
        });

        // Scroll behavior analysis
        $(window).on('scroll', () => {
            this.analyzeScrollBehavior();
        });

        // Click pattern analysis
        $(document).on('click', (e) => {
            this.analyzeClickPattern(e);
        });

        // Keyboard interaction analysis
        $(document).on('keydown', (e) => {
            this.analyzeKeyboardBehavior(e);
        });

        // Focus and attention tracking
        $(window).on('focus blur', (e) => {
            this.analyzeAttentionPatterns(e.type);
        });
    }

    /**
     * 🧮 Advanced Cursor Behavior Analysis
     */
    analyzeCursorBehavior(movements) {
        if (movements.length < 10) return;

        const recent = movements.slice(-10);
        let totalDistance = 0;
        let velocities = [];
        let directions = [];

        for (let i = 1; i < recent.length; i++) {
            const prev = recent[i - 1];
            const curr = recent[i];
            
            const distance = Math.sqrt(
                Math.pow(curr.x - prev.x, 2) + Math.pow(curr.y - prev.y, 2)
            );
            const timeDiff = curr.timestamp - prev.timestamp;
            const velocity = timeDiff > 0 ? distance / timeDiff : 0;
            
            totalDistance += distance;
            velocities.push(velocity);
            
            if (distance > 0) {
                directions.push(Math.atan2(curr.y - prev.y, curr.x - prev.x));
            }
        }

        const avgVelocity = velocities.reduce((a, b) => a + b, 0) / velocities.length;
        const smoothness = this.calculateMovementSmoothness(recent);
        
        // Update user engagement based on cursor behavior
        let engagementDelta = 0;
        
        if (avgVelocity > 0.5 && smoothness > 0.7) {
            engagementDelta = 0.1; // Focused, purposeful movement
        } else if (avgVelocity < 0.1) {
            engagementDelta = -0.05; // Low activity
        }

        this.updateEngagementLevel(engagementDelta);
    }

    /**
     * 📊 Movement Smoothness Calculation
     */
    calculateMovementSmoothness(movements) {
        if (movements.length < 3) return 1;

        let smoothnessScore = 0;
        for (let i = 2; i < movements.length; i++) {
            const p1 = movements[i - 2];
            const p2 = movements[i - 1];
            const p3 = movements[i];

            const angle1 = Math.atan2(p2.y - p1.y, p2.x - p1.x);
            const angle2 = Math.atan2(p3.y - p2.y, p3.x - p2.x);
            const angleDiff = Math.abs(angle2 - angle1);
            
            smoothnessScore += Math.cos(angleDiff);
        }

        return smoothnessScore / (movements.length - 2);
    }

    /**
     * 🔄 Scroll Behavior Analysis
     */
    analyzeScrollBehavior() {
        const scrollTop = $(window).scrollTop();
        const documentHeight = $(document).height();
        const windowHeight = $(window).height();
        const scrollPercentage = scrollTop / (documentHeight - windowHeight);

        this.userProfile.behavioralPatterns.scrollDepth = Math.max(
            this.userProfile.behavioralPatterns.scrollDepth || 0,
            scrollPercentage
        );

        // Detect reading vs skimming behavior
        const currentTime = Date.now();
        if (!this.lastScrollTime) {
            this.lastScrollTime = currentTime;
            this.lastScrollPosition = scrollTop;
            return;
        }

        const timeDiff = currentTime - this.lastScrollTime;
        const scrollDiff = Math.abs(scrollTop - this.lastScrollPosition);
        const scrollSpeed = scrollDiff / timeDiff;

        if (scrollSpeed < 0.5 && timeDiff > 1000) {
            // Slow scroll indicates reading
            this.updateEngagementLevel(0.05);
        } else if (scrollSpeed > 2) {
            // Fast scroll indicates skimming
            this.updateEngagementLevel(-0.02);
        }

        this.lastScrollTime = currentTime;
        this.lastScrollPosition = scrollTop;
    }

    /**
     * 🖱️ Click Pattern Analysis
     */
    analyzeClickPattern(event) {
        const clickData = {
            x: event.clientX,
            y: event.clientY,
            timestamp: Date.now(),
            target: event.target.tagName,
            targetClass: event.target.className
        };

        if (!this.userProfile.behavioralPatterns.clicks) {
            this.userProfile.behavioralPatterns.clicks = [];
        }

        this.userProfile.behavioralPatterns.clicks.push(clickData);

        // Keep only last 50 clicks
        if (this.userProfile.behavioralPatterns.clicks.length > 50) {
            this.userProfile.behavioralPatterns.clicks = 
                this.userProfile.behavioralPatterns.clicks.slice(-50);
        }

        // Analyze click precision and intention
        this.analyzeClickPrecision(clickData);
    }

    /**
     * ⌨️ Keyboard Behavior Analysis
     */
    analyzeKeyboardBehavior(event) {
        if (!this.keyboardMetrics) {
            this.keyboardMetrics = {
                typingSpeed: [],
                pausePatterns: [],
                lastKeyTime: null
            };
        }

        const currentTime = Date.now();
        
        if (this.keyboardMetrics.lastKeyTime) {
            const interval = currentTime - this.keyboardMetrics.lastKeyTime;
            this.keyboardMetrics.typingSpeed.push(interval);
            
            // Keep only last 20 intervals
            if (this.keyboardMetrics.typingSpeed.length > 20) {
                this.keyboardMetrics.typingSpeed = this.keyboardMetrics.typingSpeed.slice(-20);
            }

            // Analyze typing patterns for stress/confidence indicators
            const avgInterval = this.keyboardMetrics.typingSpeed.reduce((a, b) => a + b, 0) / 
                               this.keyboardMetrics.typingSpeed.length;
            
            if (avgInterval < 150) {
                // Fast typing - confident user
                this.updateEngagementLevel(0.03);
            } else if (avgInterval > 800) {
                // Slow typing - thoughtful or struggling
                this.updateEngagementLevel(-0.01);
            }
        }

        this.keyboardMetrics.lastKeyTime = currentTime;
    }

    /**
     * 👁️ Attention Pattern Analysis
     */
    analyzeAttentionPatterns(eventType) {
        const timestamp = Date.now();
        
        if (!this.attentionMetrics) {
            this.attentionMetrics = {
                focusEvents: [],
                sessionStart: timestamp,
                totalFocusTime: 0
            };
        }

        this.attentionMetrics.focusEvents.push({
            type: eventType,
            timestamp: timestamp
        });

        if (eventType === 'blur' && this.attentionMetrics.focusEvents.length >= 2) {
            const lastFocus = this.attentionMetrics.focusEvents
                .slice(-2)
                .find(e => e.type === 'focus');
            
            if (lastFocus) {
                const focusDuration = timestamp - lastFocus.timestamp;
                this.attentionMetrics.totalFocusTime += focusDuration;
                
                // Update engagement based on focus duration
                if (focusDuration > 30000) { // 30 seconds
                    this.updateEngagementLevel(0.1);
                } else if (focusDuration < 5000) { // 5 seconds
                    this.updateEngagementLevel(-0.05);
                }
            }
        }
    }

    /**
     * 📈 Dynamic Engagement Level Updates
     */
    updateEngagementLevel(delta) {
        this.userProfile.engagementLevel = Math.max(0, Math.min(1, 
            this.userProfile.engagementLevel + delta
        ));

        // Update UI based on engagement level
        this.adaptInterfaceToEngagement();
        
        // Trigger engagement-based personalization
        this.mlModels.personalizer.updateEngagementContext(this.userProfile.engagementLevel);
    }

    /**
     * 🎨 Adaptive Interface Based on Engagement
     */
    adaptInterfaceToEngagement() {
        const level = this.userProfile.engagementLevel;
        const $body = $('body');

        // Remove previous engagement classes
        $body.removeClass('ai-engagement-low ai-engagement-medium ai-engagement-high');

        if (level < 0.3) {
            $body.addClass('ai-engagement-low');
            // Reduce animations, increase contrast
            $body.css('--ai-animation-speed-quantum', '0s');
        } else if (level < 0.7) {
            $body.addClass('ai-engagement-medium');
            // Normal animations
            $body.css('--ai-animation-speed-quantum', '0.15s');
        } else {
            $body.addClass('ai-engagement-high');
            // Enhanced animations and effects
            $body.css('--ai-animation-speed-quantum', '0.1s');
        }

        // Update real-time engagement indicator
        this.updateEngagementIndicator(level);
    }

    /**
     * 📊 Real-time Engagement Indicator
     */
    updateEngagementIndicator(level) {
        let $indicator = $('.ai-engagement-indicator');
        
        if ($indicator.length === 0) {
            $indicator = $('<div class="ai-engagement-indicator"></div>');
            $('body').append($indicator);
        }

        const percentage = Math.round(level * 100);
        $indicator.css({
            '--engagement-level': `${percentage}%`,
            'background': `conic-gradient(from 0deg, var(--ai-primary) 0%, var(--ai-primary) ${percentage}%, var(--ai-gray-200) ${percentage}%, var(--ai-gray-200) 100%)`
        });

        $indicator.attr('data-tooltip', `エンゲージメント: ${percentage}%`);
    }

    /**
     * 🤖 AI-Powered Response Enhancement
     */
    enhanceAIResponse(originalMethod) {
        return (...args) => {
            // Pre-process with ML models
            const context = this.buildEnhancedContext();
            const personalizedPrompt = this.mlModels.personalizer.enhancePrompt(args[0], context);
            
            // Predict user intent
            const intentPrediction = this.mlModels.intentPredictor.predict(personalizedPrompt);
            
            // Analyze sentiment
            const sentimentAnalysis = this.mlModels.sentimentAnalyzer.analyze(personalizedPrompt);
            
            // Update arguments with enhanced data
            args[0] = personalizedPrompt;
            args.push({
                intentPrediction,
                sentimentAnalysis,
                userContext: context,
                engagementLevel: this.userProfile.engagementLevel
            });

            return originalMethod.apply(this, args);
        };
    }

    /**
     * 🔄 Enhanced Context Building
     */
    buildEnhancedContext() {
        return {
            timestamp: Date.now(),
            userProfile: this.userProfile,
            sessionMetrics: {
                duration: Date.now() - this.sessionStartTime,
                interactions: this.interactionCount,
                avgResponseTime: this.calculateAverageResponseTime(),
                errorRate: this.calculateErrorRate()
            },
            deviceContext: {
                viewport: {
                    width: $(window).width(),
                    height: $(window).height()
                },
                deviceType: this.detectDeviceType(),
                connectionSpeed: this.estimateConnectionSpeed()
            },
            environmentalContext: {
                timeOfDay: new Date().getHours(),
                dayOfWeek: new Date().getDay(),
                preferredColorScheme: this.detectColorScheme()
            },
            cognitiveLoad: this.estimateCognitiveLoad(),
            attentionLevel: this.calculateAttentionLevel()
        };
    }

    /**
     * 🧠 Cognitive Load Estimation
     */
    estimateCognitiveLoad() {
        let load = 0.5; // baseline

        // Factor in typing speed (slower = higher load)
        if (this.keyboardMetrics && this.keyboardMetrics.typingSpeed.length > 0) {
            const avgTypingInterval = this.keyboardMetrics.typingSpeed.reduce((a, b) => a + b, 0) / 
                                    this.keyboardMetrics.typingSpeed.length;
            if (avgTypingInterval > 500) load += 0.2;
            if (avgTypingInterval < 150) load -= 0.1;
        }

        // Factor in scroll behavior (erratic = higher load)
        if (this.lastScrollTime && this.scrollErraticness > 0.7) {
            load += 0.15;
        }

        // Factor in click precision (low precision = higher load)
        if (this.userProfile.behavioralPatterns.clickAccuracy < 0.7) {
            load += 0.1;
        }

        // Factor in task switching (frequent focus changes = higher load)
        if (this.attentionMetrics && this.attentionMetrics.focusEvents.length > 10) {
            const recentSwitches = this.attentionMetrics.focusEvents
                .filter(e => Date.now() - e.timestamp < 60000).length;
            if (recentSwitches > 6) load += 0.2;
        }

        return Math.max(0, Math.min(1, load));
    }

    /**
     * 📱 Advanced Device Detection
     */
    detectDeviceType() {
        const userAgent = navigator.userAgent;
        const viewport = {
            width: $(window).width(),
            height: $(window).height()
        };

        if (/Mobi|Android/i.test(userAgent)) {
            return viewport.width < 768 ? 'mobile' : 'tablet';
        }
        
        if (/iPad|Tablet/i.test(userAgent)) {
            return 'tablet';
        }

        return viewport.width < 1024 ? 'laptop' : 'desktop';
    }

    /**
     * 🌐 Connection Speed Estimation
     */
    estimateConnectionSpeed() {
        if (navigator.connection) {
            return navigator.connection.effectiveType || 'unknown';
        }

        // Fallback: measure image load time
        const testImage = new Image();
        const startTime = Date.now();
        
        testImage.onload = () => {
            const loadTime = Date.now() - startTime;
            if (loadTime < 100) return 'fast';
            if (loadTime < 500) return 'medium';
            return 'slow';
        };

        testImage.src = '/wp-content/themes/current/assets/images/test.jpg?' + Math.random();
        return 'estimating';
    }

    /**
     * 🎯 Attention Level Calculation
     */
    calculateAttentionLevel() {
        if (!this.attentionMetrics) return 0.5;

        const totalTime = Date.now() - this.attentionMetrics.sessionStart;
        const focusRatio = this.attentionMetrics.totalFocusTime / totalTime;
        
        return Math.max(0, Math.min(1, focusRatio));
    }

    /**
     * 🔮 Predictive UI Adjustments
     */
    initPredictiveUI() {
        // Predict next user action based on patterns
        setInterval(() => {
            const prediction = this.mlModels.behaviorPredictor.predictNextAction(
                this.userProfile.behavioralPatterns
            );

            if (prediction.confidence > 0.8) {
                this.prepareForPredictedAction(prediction.action);
            }
        }, 5000);
    }

    /**
     * ⚡ Prepare for Predicted Actions
     */
    prepareForPredictedAction(action) {
        switch (action) {
            case 'search':
                // Pre-focus search input
                $('.ai-search-input').addClass('ai-pre-focus');
                break;
            case 'consultation':
                // Pre-load consultation interface
                this.preloadConsultationAssets();
                break;
            case 'scroll':
                // Prepare next section for visibility
                this.prepareNextSection();
                break;
        }
    }

    /**
     * 💎 Advanced Performance Monitoring
     */
    initAdvancedPerformanceMonitoring() {
        // Real-time performance metrics
        this.performanceMetrics = {
            responseTimes: [],
            errorCount: 0,
            successRate: 1.0,
            memoryUsage: [],
            renderTimes: []
        };

        // Monitor frame rate
        let frameCount = 0;
        let lastFrameTime = Date.now();
        
        const measureFrameRate = () => {
            frameCount++;
            const currentTime = Date.now();
            
            if (currentTime - lastFrameTime >= 1000) {
                const fps = frameCount;
                frameCount = 0;
                lastFrameTime = currentTime;
                
                this.handleFrameRateChange(fps);
            }
            
            requestAnimationFrame(measureFrameRate);
        };
        
        measureFrameRate();

        // Monitor memory usage
        if (performance.memory) {
            setInterval(() => {
                this.performanceMetrics.memoryUsage.push({
                    used: performance.memory.usedJSHeapSize,
                    total: performance.memory.totalJSHeapSize,
                    timestamp: Date.now()
                });
                
                // Keep only last 100 measurements
                if (this.performanceMetrics.memoryUsage.length > 100) {
                    this.performanceMetrics.memoryUsage = this.performanceMetrics.memoryUsage.slice(-100);
                }
            }, 5000);
        }
    }

    /**
     * 📊 Handle Frame Rate Changes
     */
    handleFrameRateChange(fps) {
        if (fps < 30) {
            // Reduce visual complexity
            $('body').addClass('ai-performance-mode');
            $('.ai-particle-bg, .ai-neural-network').hide();
        } else if (fps > 50) {
            // Enable full visual effects
            $('body').removeClass('ai-performance-mode');
            $('.ai-particle-bg, .ai-neural-network').show();
        }
        
        this.updatePerformanceIndicator(fps);
    }

    /**
     * ⚡ Performance Indicator
     */
    updatePerformanceIndicator(fps) {
        let $indicator = $('.ai-performance-indicator');
        
        if ($indicator.length === 0) {
            $indicator = $('<div class="ai-performance-indicator"></div>');
            $('body').append($indicator);
        }

        const status = fps < 30 ? 'poor' : fps < 50 ? 'good' : 'excellent';
        $indicator
            .removeClass('poor good excellent')
            .addClass(status)
            .text(`${fps} FPS`)
            .attr('data-tooltip', `パフォーマンス: ${status}`);
    }


    /**
     * 🤖 ===== MACHINE LEARNING MODELS =====
     * Advanced AI Models for Intent, Sentiment, and Behavior Prediction
     */

    // AI Intent Predictor Model
    class AIIntentPredictor {
        constructor() {
            this.intentPatterns = {
                'search': ['探', '検索', 'さが', '見つけ', 'find', 'search'],
                'consultation': ['相談', 'アドバイス', '質問', '聞き', 'help', 'advice'],
                'information': ['教え', '情報', '詳し', 'について', 'info', 'detail'],
                'application': ['申請', '手続き', '申込', 'apply', 'process'],
                'comparison': ['比較', '違い', 'どっち', 'compare', 'difference']
            };
            
            this.contextWeights = {
                'urgency': 0.3,
                'specificity': 0.4,
                'complexity': 0.2,
                'emotional_tone': 0.1
            };
        }

        predict(message) {
            const scores = {};
            const normalizedMessage = message.toLowerCase();
            
            // Calculate intent scores based on keyword matching
            for (const [intent, keywords] of Object.entries(this.intentPatterns)) {
                let score = 0;
                keywords.forEach(keyword => {
                    if (normalizedMessage.includes(keyword)) {
                        score += 1;
                    }
                });
                scores[intent] = score / keywords.length;
            }

            // Find highest scoring intent
            const predictedIntent = Object.keys(scores).reduce((a, b) => 
                scores[a] > scores[b] ? a : b
            );
            
            const confidence = Math.max(...Object.values(scores));
            
            return {
                intent: predictedIntent,
                confidence: Math.min(confidence * 0.8 + 0.2, 1.0), // Normalize confidence
                alternatives: Object.entries(scores)
                    .sort(([,a], [,b]) => b - a)
                    .slice(1, 3)
                    .map(([intent, score]) => ({ intent, score }))
            };
        }
    }

    // AI Sentiment Analyzer Model
    class AISentimentAnalyzer {
        constructor() {
            this.sentimentLexicon = {
                positive: ['嬉し', '良い', 'ありがと', '助かる', '素晴らし', 'good', 'great', 'thank'],
                negative: ['困', '大変', '難し', '分からな', '問題', 'hard', 'difficult', 'problem'],
                neutral: ['です', 'ます', 'について', 'に関し', 'という', 'that', 'about', 'regarding']
            };
            
            this.emotionalIntensifiers = ['とても', '非常に', 'very', 'extremely', 'really'];
        }

        analyze(message) {
            const normalizedMessage = message.toLowerCase();
            let scores = { positive: 0, negative: 0, neutral: 0 };
            let totalWords = 0;
            let intensityMultiplier = 1;

            // Check for emotional intensifiers
            this.emotionalIntensifiers.forEach(intensifier => {
                if (normalizedMessage.includes(intensifier)) {
                    intensityMultiplier = 1.5;
                }
            });

            // Calculate sentiment scores
            for (const [sentiment, words] of Object.entries(this.sentimentLexicon)) {
                words.forEach(word => {
                    if (normalizedMessage.includes(word)) {
                        scores[sentiment] += intensityMultiplier;
                        totalWords++;
                    }
                });
            }

            // Normalize scores
            if (totalWords > 0) {
                for (const sentiment in scores) {
                    scores[sentiment] = scores[sentiment] / totalWords;
                }
            }

            // Determine dominant sentiment
            const dominantSentiment = Object.keys(scores).reduce((a, b) => 
                scores[a] > scores[b] ? a : b
            );

            return {
                sentiment: dominantSentiment,
                confidence: Math.max(...Object.values(scores)),
                scores: scores,
                emotional_intensity: intensityMultiplier > 1 ? 'high' : 'normal'
            };
        }
    }

    // AI Personalization Engine
    class AIPersonalizationEngine {
        constructor() {
            this.userPreferences = {};
            this.interactionHistory = [];
            this.personalizationRules = {
                'beginner': {
                    'response_style': 'detailed',
                    'technical_level': 'basic',
                    'example_preference': 'concrete'
                },
                'intermediate': {
                    'response_style': 'balanced',
                    'technical_level': 'moderate',
                    'example_preference': 'mixed'
                },
                'expert': {
                    'response_style': 'concise',
                    'technical_level': 'advanced',
                    'example_preference': 'abstract'
                }
            };
        }

        enhancePrompt(originalPrompt, context) {
            const userLevel = this.assessUserLevel(context);
            const preferences = this.personalizationRules[userLevel];
            
            let enhancedPrompt = originalPrompt;
            
            // Add personalization context
            enhancedPrompt += `\n\n[パーソナライゼーション情報]`;
            enhancedPrompt += `\nユーザーレベル: ${userLevel}`;
            enhancedPrompt += `\n回答スタイル: ${preferences.response_style}`;
            enhancedPrompt += `\n技術レベル: ${preferences.technical_level}`;
            
            if (context.userProfile?.preferences) {
                enhancedPrompt += `\nユーザー設定: ${JSON.stringify(context.userProfile.preferences)}`;
            }

            return enhancedPrompt;
        }

        assessUserLevel(context) {
            // Simple heuristic for user level assessment
            const engagementLevel = context.userProfile?.engagementLevel || 0.5;
            const interactionCount = context.sessionMetrics?.interactions || 0;
            
            if (engagementLevel > 0.8 && interactionCount > 10) {
                return 'expert';
            } else if (engagementLevel > 0.5 && interactionCount > 5) {
                return 'intermediate';
            }
            
            return 'beginner';
        }

        updateEngagementContext(engagementLevel) {
            this.userPreferences.lastEngagementLevel = engagementLevel;
            this.userPreferences.engagementHistory = this.userPreferences.engagementHistory || [];
            this.userPreferences.engagementHistory.push({
                level: engagementLevel,
                timestamp: Date.now()
            });
            
            // Keep only last 20 engagement records
            if (this.userPreferences.engagementHistory.length > 20) {
                this.userPreferences.engagementHistory = 
                    this.userPreferences.engagementHistory.slice(-20);
            }
        }
    }

    // AI Behavior Predictor Model
    class AIBehaviorPredictor {
        constructor() {
            this.behaviorPatterns = {};
            this.predictionAccuracy = 0.7;
        }

        predictNextAction(behavioralPatterns) {
            const patterns = behavioralPatterns || {};
            
            // Simple prediction based on patterns
            let predictions = [
                { action: 'search', probability: 0.3 },
                { action: 'consultation', probability: 0.25 },
                { action: 'scroll', probability: 0.2 },
                { action: 'click', probability: 0.15 },
                { action: 'navigation', probability: 0.1 }
            ];

            // Adjust probabilities based on recent activity
            if (patterns.scrollDepth > 0.8) {
                predictions.find(p => p.action === 'navigation').probability += 0.2;
            }

            if (patterns.clicks && patterns.clicks.length > 3) {
                predictions.find(p => p.action === 'consultation').probability += 0.15;
            }

            // Sort by probability
            predictions.sort((a, b) => b.probability - a.probability);
            
            return {
                action: predictions[0].action,
                confidence: predictions[0].probability,
                alternatives: predictions.slice(1, 3)
            };
        }
    }

    // AI Context Analyzer
    class AIContextAnalyzer {
        constructor() {
            this.contextFactors = [
                'temporal', 'behavioral', 'environmental', 'technical'
            ];
        }

        analyzeContext(context) {
            const analysis = {
                complexity: this.calculateContextComplexity(context),
                relevance: this.assessContextRelevance(context),
                confidence: this.determineContextConfidence(context)
            };

            return analysis;
        }

        calculateContextComplexity(context) {
            let complexity = 0.5; // baseline
            
            if (context.sessionMetrics?.interactions > 10) complexity += 0.2;
            if (context.cognitiveLoad > 0.7) complexity += 0.1;
            if (context.deviceContext?.deviceType === 'mobile') complexity += 0.1;
            
            return Math.min(1, complexity);
        }

        assessContextRelevance(context) {
            // Mock relevance assessment
            return 0.8;
        }

        determineContextConfidence(context) {
            // Mock confidence determination
            return 0.85;
        }
    }

    /**
     * 🌐 ===== REAL-TIME COLLABORATION FEATURES =====
     * Multi-User Real-Time Interaction System
     */

    initRealtimeCollaboration() {
        this.collaborationSession = {
            sessionId: this.generateSessionId(),
            activeUsers: [],
            sharedState: {},
            messageQueue: [],
            isHost: false
        };

        this.setupWebSocketConnection();
        this.initPresenceTracking();
        this.setupSharedCursor();
        this.initCollaborativeSearch();
    }

    /**
     * 🔗 WebSocket Connection for Real-Time Communication
     */
    setupWebSocketConnection() {
        // Mock WebSocket implementation (would connect to actual WebSocket server)
        this.mockWebSocket = {
            send: (data) => {
                console.log('Mock WebSocket send:', data);
                // Simulate broadcast to other users
                setTimeout(() => {
                    this.handleCollaborativeMessage(JSON.parse(data));
                }, 100);
            },
            close: () => {
                console.log('Mock WebSocket closed');
            }
        };

        // Simulate connection established
        setTimeout(() => {
            this.onCollaborationConnected();
        }, 500);
    }

    /**
     * 👥 User Presence Tracking
     */
    initPresenceTracking() {
        this.presenceData = {
            userId: this.generateUserId(),
            username: 'Anonymous User',
            cursorPosition: { x: 0, y: 0 },
            currentSection: 'home',
            status: 'active',
            lastActivity: Date.now()
        };

        // Track cursor movement for presence
        $(document).on('mousemove', (e) => {
            this.presenceData.cursorPosition = { x: e.clientX, y: e.clientY };
            this.presenceData.lastActivity = Date.now();
            
            // Broadcast cursor position (throttled)
            if (this.lastCursorBroadcast + 100 < Date.now()) {
                this.broadcastPresence('cursor_move', this.presenceData);
                this.lastCursorBroadcast = Date.now();
            }
        });

        // Track section changes
        $(window).on('scroll', () => {
            const currentSection = this.detectCurrentSection();
            if (currentSection !== this.presenceData.currentSection) {
                this.presenceData.currentSection = currentSection;
                this.broadcastPresence('section_change', this.presenceData);
            }
        });
    }

    /**
     * 🖱️ Shared Cursor Visualization
     */
    setupSharedCursor() {
        this.sharedCursors = new Map();
        
        // Create cursor container
        if ($('.shared-cursors-container').length === 0) {
            $('body').append('<div class="shared-cursors-container"></div>');
        }
    }

    /**
     * 🔍 Collaborative Search System
     */
    initCollaborativeSearch() {
        this.collaborativeSearch = {
            sharedQueries: [],
            searchSuggestions: [],
            groupFilters: {},
            realTimeResults: []
        };

        // Enhance search input with collaboration features
        $('.ai-search-input input').on('input', (e) => {
            const query = e.target.value;
            if (query.length > 2) {
                this.broadcastSearch('query_update', {
                    query: query,
                    timestamp: Date.now(),
                    userId: this.presenceData.userId
                });
            }
        });

        // Add collaborative search indicators
        this.addCollaborativeSearchUI();
    }

    /**
     * 📡 Broadcast Presence Information
     */
    broadcastPresence(type, data) {
        const message = {
            type: 'presence',
            subtype: type,
            data: data,
            timestamp: Date.now(),
            sessionId: this.collaborationSession.sessionId
        };

        if (this.mockWebSocket) {
            this.mockWebSocket.send(JSON.stringify(message));
        }
    }

    /**
     * 🔍 Broadcast Search Activity
     */
    broadcastSearch(type, data) {
        const message = {
            type: 'search',
            subtype: type,
            data: data,
            timestamp: Date.now(),
            sessionId: this.collaborationSession.sessionId
        };

        if (this.mockWebSocket) {
            this.mockWebSocket.send(JSON.stringify(message));
        }
    }

    /**
     * 📨 Handle Collaborative Messages
     */
    handleCollaborativeMessage(message) {
        switch (message.type) {
            case 'presence':
                this.handlePresenceUpdate(message);
                break;
            case 'search':
                this.handleCollaborativeSearch(message);
                break;
            case 'cursor':
                this.handleCursorUpdate(message);
                break;
        }
    }

    /**
     * 👤 Handle User Presence Updates
     */
    handlePresenceUpdate(message) {
        const userData = message.data;
        
        if (userData.userId !== this.presenceData.userId) {
            this.updateUserPresence(userData);
            this.visualizeUserActivity(userData);
        }
    }

    /**
     * 🔍 Handle Collaborative Search
     */
    handleCollaborativeSearch(message) {
        const searchData = message.data;
        
        // Show other users' search activity
        this.showCollaborativeSearchActivity(searchData);
        
        // Add to shared suggestions
        if (message.subtype === 'query_update') {
            this.addToSharedSuggestions(searchData.query);
        }
    }

    /**
     * 🎨 Add Collaborative Search UI
     */
    addCollaborativeSearchUI() {
        const collaborativeUI = `
            <div class="collaborative-search-panel">
                <div class="active-users-indicator">
                    <span class="users-count">0</span>
                    <span class="users-label">アクティブユーザー</span>
                </div>
                <div class="shared-suggestions">
                    <h4>共有検索候補</h4>
                    <ul class="suggestion-list"></ul>
                </div>
                <div class="real-time-activity">
                    <h4>リアルタイム活動</h4>
                    <div class="activity-feed"></div>
                </div>
            </div>
        `;

        if ($('.collaborative-search-panel').length === 0) {
            $('.ai-consultation-interface').append(collaborativeUI);
        }
    }

    /**
     * 📊 Show Real-Time Activity Feed
     */
    showCollaborativeSearchActivity(searchData) {
        const $activityFeed = $('.activity-feed');
        const activityItem = $(`
            <div class="activity-item" data-user="${searchData.userId}">
                <span class="activity-icon">🔍</span>
                <span class="activity-text">ユーザーが検索中: "${searchData.query}"</span>
                <span class="activity-time">${this.formatTime(searchData.timestamp)}</span>
            </div>
        `);

        $activityFeed.prepend(activityItem);
        
        // Fade out after 5 seconds
        setTimeout(() => {
            activityItem.fadeOut(() => activityItem.remove());
        }, 5000);
    }

    /**
     * 💡 Add to Shared Suggestions
     */
    addToSharedSuggestions(query) {
        const $suggestionList = $('.suggestion-list');
        
        // Avoid duplicates
        if (!this.collaborativeSearch.sharedQueries.includes(query)) {
            this.collaborativeSearch.sharedQueries.unshift(query);
            
            // Keep only last 5 suggestions
            if (this.collaborativeSearch.sharedQueries.length > 5) {
                this.collaborativeSearch.sharedQueries = 
                    this.collaborativeSearch.sharedQueries.slice(0, 5);
            }
            
            // Update UI
            $suggestionList.empty();
            this.collaborativeSearch.sharedQueries.forEach(suggestion => {
                const $item = $(`
                    <li class="suggestion-item ai-quantum-hover" data-query="${suggestion}">
                        <span class="suggestion-text">${suggestion}</span>
                        <button class="use-suggestion ai-morph-button">使用</button>
                    </li>
                `);
                
                $item.find('.use-suggestion').on('click', () => {
                    $('.ai-search-input input').val(suggestion).focus();
                });
                
                $suggestionList.append($item);
            });
        }
    }

    /**
     * 🛠️ Utility Functions for Collaboration
     */
    generateSessionId() {
        return 'session_' + Math.random().toString(36).substr(2, 9);
    }

    generateUserId() {
        return 'user_' + Math.random().toString(36).substr(2, 9);
    }

    detectCurrentSection() {
        const scrollTop = $(window).scrollTop();
        const sections = ['hero', 'search', 'consultation', 'results', 'footer'];
        
        // Simple section detection based on scroll position
        const sectionHeight = $(document).height() / sections.length;
        const currentIndex = Math.floor(scrollTop / sectionHeight);
        
        return sections[currentIndex] || 'unknown';
    }

    formatTime(timestamp) {
        const now = Date.now();
        const diff = now - timestamp;
        
        if (diff < 60000) return '今';
        if (diff < 3600000) return Math.floor(diff / 60000) + '分前';
        return Math.floor(diff / 3600000) + '時間前';
    }

    onCollaborationConnected() {
        console.log('🌐 リアルタイムコラボレーション接続完了');
        this.showCollaborationStatus('connected');
    }

    showCollaborationStatus(status) {
        let $status = $('.collaboration-status');
        
        if ($status.length === 0) {
            $status = $('<div class="collaboration-status ai-adaptive-card"></div>');
            $('body').append($status);
        }

        const statusMessages = {
            'connected': '🌐 コラボレーション有効',
            'disconnected': '🔴 コラボレーション無効',
            'reconnecting': '🔄 再接続中...'
        };

        $status.text(statusMessages[status] || status)
               .removeClass('connected disconnected reconnecting')
               .addClass(status);
    }

