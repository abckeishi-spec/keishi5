/**
 * Grant Insight - Robust Error Handling & Fallback System
 * 
 * 高度なエラーハンドリングとフォールバック機能
 * - 指数バックオフによるリトライ
 * - サーキットブレーカーパターン
 * - オフラインサポート
 * - ユーザーフレンドリーなエラー表示
 * 
 * @version 1.0.0
 */

class RobustErrorHandler {
    constructor(config = {}) {
        this.config = {
            maxRetries: config.maxRetries || 3,
            baseDelay: config.baseDelay || 1000,
            maxDelay: config.maxDelay || 10000,
            backoffMultiplier: config.backoffMultiplier || 2,
            enableJitter: config.enableJitter !== false,
            circuitBreakerThreshold: config.circuitBreakerThreshold || 5,
            circuitBreakerTimeout: config.circuitBreakerTimeout || 60000,
            ...config
        };
        
        this.circuitBreakers = new Map();
        this.errorLog = [];
        this.isOnline = navigator.onLine;
        this.fallbackResponses = this.initFallbackResponses();
        
        this.initEventListeners();
        this.initCircuitBreakers();
    }
    
    /**
     * フォールバック応答の初期化
     */
    initFallbackResponses() {
        return {
            consultation: {
                general: "申し訳ございません。現在AIサービスが一時的に利用できません。しばらく経ってから再度お試しください。",
                network_error: "ネットワーク接続に問題があります。インターネット接続をご確認ください。",
                server_error: "サーバーで問題が発生しています。開発チームに報告済みです。",
                timeout: "処理に時間がかかっています。もう一度お試しください。",
                rate_limit: "リクエスト数の制限に達しました。しばらく待ってから再度お試しください。"
            },
            search: {
                results: [],
                message: "検索サービスが一時的に利用できません。",
                suggestions: [
                    "IT導入補助金",
                    "小規模事業者持続化補助金", 
                    "ものづくり補助金",
                    "事業再構築補助金"
                ]
            },
            offline: {
                message: "現在オフラインです。インターネット接続を確認してください。",
                cached_message: "キャッシュされたデータを表示しています。"
            }
        };
    }
    
    /**
     * イベントリスナーの初期化
     */
    initEventListeners() {
        // オンライン/オフライン状態の監視
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.onNetworkStateChange('online');
        });
        
        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.onNetworkStateChange('offline');
        });
        
        // グローバルエラーハンドラー
        window.addEventListener('error', (event) => {
            this.handleGlobalError(event);
        });
        
        // 未処理のPromise拒否
        window.addEventListener('unhandledrejection', (event) => {
            this.handleUnhandledRejection(event);
        });
    }
    
    /**
     * サーキットブレーカーの初期化
     */
    initCircuitBreakers() {
        const services = ['ai_consultation', 'ai_search', 'database'];
        
        services.forEach(service => {
            this.circuitBreakers.set(service, {
                state: 'closed', // closed, open, half-open
                failureCount: 0,
                failureThreshold: this.config.circuitBreakerThreshold,
                lastFailureTime: 0,
                timeout: this.config.circuitBreakerTimeout,
                successCount: 0,
                successThreshold: 3
            });
        });
    }
    
    /**
     * 堅牢なAJAXリクエスト
     */
    async makeRobustAjaxRequest(requestData, type, customRetries = null) {
        const retries = customRetries || this.config.maxRetries;
        let delay = this.config.baseDelay;
        
        // オフライン状態のチェック
        if (!this.isOnline) {
            return this.getFallbackResponse(requestData, type, 'offline');
        }
        
        // サーキットブレーカーのチェック
        if (this.isCircuitBreakerOpen(type)) {
            return this.getFallbackResponse(requestData, type, 'circuit_breaker_open');
        }
        
        for (let attempt = 1; attempt <= retries; attempt++) {
            try {
                const response = await this.executeRequest(requestData, type, attempt);
                
                if (response && response.success) {
                    this.recordSuccess(type);
                    return response;
                } else {
                    throw new Error(response.data || 'Request failed');
                }
                
            } catch (error) {
                this.recordFailure(type, error.message);
                
                if (attempt === retries) {
                    // 最終フォールバック
                    return this.getFallbackResponse(requestData, type, error.message);
                }
                
                // 指数バックオフ with ジッター
                const actualDelay = this.calculateBackoffDelay(delay, attempt);
                await this.sleep(actualDelay);
                
                delay = Math.min(delay * this.config.backoffMultiplier, this.config.maxDelay);
            }
        }
        
        return this.getFallbackResponse(requestData, type, 'max_retries_exceeded');
    }
    
    /**
     * リクエストの実行
     */
    async executeRequest(requestData, type, attempt) {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 30000); // 30秒タイムアウト
        
        try {
            // CSRFトークンの取得
            const nonce = this.getCsrfToken();
            
            const formData = new FormData();
            formData.append('action', `gi_ai_${type}`);
            formData.append('gi_nonce', nonce);
            
            // リクエストデータの追加
            Object.keys(requestData).forEach(key => {
                formData.append(key, requestData[key]);
            });
            
            const response = await fetch(giAjax.ajax_url, {
                method: 'POST',
                body: formData,
                signal: controller.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            clearTimeout(timeoutId);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            return data;
            
        } catch (error) {
            clearTimeout(timeoutId);
            
            if (error.name === 'AbortError') {
                throw new Error('Request timeout');
            }
            
            throw error;
        }
    }
    
    /**
     * CSRFトークンの取得
     */
    getCsrfToken() {
        // WordPress nonceの取得
        return window.giAjax?.nonce || document.querySelector('meta[name="csrf-token"]')?.content || '';
    }
    
    /**
     * バックオフ遅延の計算
     */
    calculateBackoffDelay(baseDelay, attempt) {
        let delay = baseDelay * Math.pow(this.config.backoffMultiplier, attempt - 1);
        
        if (this.config.enableJitter) {
            // ジッターを追加 (±25%)
            const jitter = (delay * 0.25) * (Math.random() * 2 - 1);
            delay += jitter;
        }
        
        return Math.min(delay, this.config.maxDelay);
    }
    
    /**
     * スリープ関数
     */
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    /**
     * サーキットブレーカーの状態チェック
     */
    isCircuitBreakerOpen(service) {
        const breaker = this.circuitBreakers.get(service);
        if (!breaker) return false;
        
        const now = Date.now();
        
        switch (breaker.state) {
            case 'open':
                if (now - breaker.lastFailureTime >= breaker.timeout) {
                    breaker.state = 'half-open';
                    breaker.successCount = 0;
                    return false;
                }
                return true;
                
            case 'half-open':
                return false;
                
            case 'closed':
            default:
                return false;
        }
    }
    
    /**
     * 成功の記録
     */
    recordSuccess(service) {
        const breaker = this.circuitBreakers.get(service);
        if (!breaker) return;
        
        switch (breaker.state) {
            case 'half-open':
                breaker.successCount++;
                if (breaker.successCount >= breaker.successThreshold) {
                    breaker.state = 'closed';
                    breaker.failureCount = 0;
                }
                break;
                
            case 'closed':
                breaker.failureCount = Math.max(0, breaker.failureCount - 1);
                break;
        }
        
        console.log(`✅ ${service} request succeeded`);
    }
    
    /**
     * 失敗の記録
     */
    recordFailure(service, errorMessage) {
        const breaker = this.circuitBreakers.get(service);
        if (!breaker) return;
        
        breaker.failureCount++;
        breaker.lastFailureTime = Date.now();
        
        if (breaker.failureCount >= breaker.failureThreshold) {
            breaker.state = 'open';
            console.warn(`⚠️ Circuit breaker opened for ${service}`);
        }
        
        if (breaker.state === 'half-open') {
            breaker.state = 'open';
        }
        
        // エラーログに記録
        this.logError(service, errorMessage);
    }
    
    /**
     * フォールバック応答の取得
     */
    getFallbackResponse(requestData, type, errorType) {
        const fallback = {
            success: true,
            data: {},
            fallback: true,
            error_type: errorType,
            timestamp: Date.now()
        };
        
        switch (type) {
            case 'consultation':
                fallback.data = {
                    response: this.getConsultationFallback(errorType),
                    suggestions: [
                        '具体的な業種や事業内容をお聞かせください',
                        '予算規模はどのくらいでしょうか',
                        'どのような用途での利用をお考えですか'
                    ],
                    fallback: true
                };
                break;
                
            case 'search':
                fallback.data = {
                    ...this.fallbackResponses.search,
                    fallback: true
                };
                break;
                
            default:
                fallback.data = {
                    response: this.fallbackResponses.consultation.general,
                    fallback: true
                };
        }
        
        return fallback;
    }
    
    /**
     * 相談用フォールバック応答の取得
     */
    getConsultationFallback(errorType) {
        const consultationFallbacks = this.fallbackResponses.consultation;
        
        switch (errorType) {
            case 'offline':
                return consultationFallbacks.network_error;
            case 'timeout':
                return consultationFallbacks.timeout;
            case 'rate_limit':
                return consultationFallbacks.rate_limit;
            case 'circuit_breaker_open':
                return consultationFallbacks.server_error;
            default:
                return consultationFallbacks.general;
        }
    }
    
    /**
     * ネットワーク状態変化の処理
     */
    onNetworkStateChange(state) {
        if (state === 'online') {
            this.showNotification('インターネット接続が復旧しました', 'success');
            this.syncOfflineData();
        } else {
            this.showNotification('オフラインモードになりました', 'warning');
            this.enableOfflineMode();
        }
    }
    
    /**
     * オフラインデータの同期
     */
    async syncOfflineData() {
        try {
            const offlineData = this.getOfflineData();
            if (offlineData.length === 0) return;
            
            console.log('🔄 Syncing offline data...');
            
            for (const item of offlineData) {
                try {
                    await this.makeRobustAjaxRequest(item.data, item.type, 1);
                    this.removeOfflineData(item.id);
                } catch (error) {
                    console.warn('Failed to sync offline item:', item.id);
                }
            }
            
            this.showNotification('オフラインデータを同期しました', 'success');
            
        } catch (error) {
            console.error('Offline data sync failed:', error);
        }
    }
    
    /**
     * オフラインモードの有効化
     */
    enableOfflineMode() {
        // オフライン用のUI調整
        document.body.classList.add('offline-mode');
        
        // 重要でない機能を無効化
        const nonEssentialButtons = document.querySelectorAll('[data-requires-network]');
        nonEssentialButtons.forEach(btn => {
            btn.disabled = true;
            btn.title = 'オフライン中は利用できません';
        });
    }
    
    /**
     * オフラインデータの取得
     */
    getOfflineData() {
        try {
            return JSON.parse(localStorage.getItem('gi_offline_data') || '[]');
        } catch (error) {
            return [];
        }
    }
    
    /**
     * オフラインデータの保存
     */
    saveOfflineData(data, type) {
        try {
            const offlineData = this.getOfflineData();
            const item = {
                id: Date.now() + Math.random().toString(36).substr(2, 9),
                data: data,
                type: type,
                timestamp: Date.now()
            };
            
            offlineData.push(item);
            
            // 最大100件まで保持
            if (offlineData.length > 100) {
                offlineData.shift();
            }
            
            localStorage.setItem('gi_offline_data', JSON.stringify(offlineData));
            
        } catch (error) {
            console.error('Failed to save offline data:', error);
        }
    }
    
    /**
     * オフラインデータの削除
     */
    removeOfflineData(itemId) {
        try {
            const offlineData = this.getOfflineData();
            const filteredData = offlineData.filter(item => item.id !== itemId);
            localStorage.setItem('gi_offline_data', JSON.stringify(filteredData));
        } catch (error) {
            console.error('Failed to remove offline data:', error);
        }
    }
    
    /**
     * グローバルエラーの処理
     */
    handleGlobalError(event) {
        const error = {
            message: event.message,
            filename: event.filename,
            lineno: event.lineno,
            colno: event.colno,
            error: event.error?.stack,
            timestamp: Date.now()
        };
        
        this.logError('javascript_error', error);
        
        // 開発モードでのみコンソールに詳細出力
        if (window.giDebug) {
            console.error('Global error caught:', error);
        }
    }
    
    /**
     * 未処理のPromise拒否の処理
     */
    handleUnhandledRejection(event) {
        const error = {
            reason: event.reason,
            promise: event.promise,
            timestamp: Date.now()
        };
        
        this.logError('unhandled_promise_rejection', error);
        
        // 開発モードでのみコンソールに詳細出力
        if (window.giDebug) {
            console.error('Unhandled promise rejection:', error);
        }
        
        // デフォルトの処理を防ぐ
        event.preventDefault();
    }
    
    /**
     * エラーの記録
     */
    logError(type, error) {
        const errorEntry = {
            type: type,
            error: error,
            timestamp: Date.now(),
            url: window.location.href,
            userAgent: navigator.userAgent
        };
        
        this.errorLog.push(errorEntry);
        
        // 最大100件まで保持
        if (this.errorLog.length > 100) {
            this.errorLog.shift();
        }
        
        // ローカルストレージに保存
        try {
            localStorage.setItem('gi_error_log', JSON.stringify(this.errorLog));
        } catch (e) {
            // ストレージエラーは無視
        }
    }
    
    /**
     * 通知の表示
     */
    showNotification(message, type = 'info', duration = 5000) {
        // 既存の通知システムがあれば使用
        if (window.giNotification) {
            window.giNotification.show(message, type, duration);
            return;
        }
        
        // フォールバック: シンプルな通知
        const notification = document.createElement('div');
        notification.className = `gi-notification gi-notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 6px;
            color: white;
            z-index: 10000;
            font-family: sans-serif;
            font-size: 14px;
            max-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            background-color: ${type === 'success' ? '#22c55e' : type === 'warning' ? '#f59e0b' : '#3b82f6'};
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, duration);
    }
    
    /**
     * エラー統計の取得
     */
    getErrorStats() {
        const now = Date.now();
        const oneDayAgo = now - (24 * 60 * 60 * 1000);
        
        const recentErrors = this.errorLog.filter(error => error.timestamp > oneDayAgo);
        const errorCounts = {};
        
        recentErrors.forEach(error => {
            errorCounts[error.type] = (errorCounts[error.type] || 0) + 1;
        });
        
        return {
            totalErrors: this.errorLog.length,
            recentErrors: recentErrors.length,
            errorCounts: errorCounts,
            circuitBreakers: Object.fromEntries(this.circuitBreakers),
            isOnline: this.isOnline
        };
    }
}

// グローバルインスタンスの作成
window.giErrorHandler = new RobustErrorHandler({
    maxRetries: 3,
    baseDelay: 1000,
    maxDelay: 10000,
    backoffMultiplier: 2,
    enableJitter: true
});

// デバッグモードの設定
if (window.location.search.includes('debug=1')) {
    window.giDebug = true;
    console.log('🔧 Grant Insight Debug Mode Enabled');
}