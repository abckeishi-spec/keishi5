<?php
/**
 * Grant Card Mobile Compact - Professional Design
 * 
 * 統一されたデザインシステムに基づくモバイル専用コンパクトカード
 * - プロフェッショナルなビジュアルデザイン
 * - 統一された色彩とタイポグラフィ
 * - タッチ操作最適化
 * - 44px以上のタッチターゲット
 * 
 * @package Grant_Insight_Perfect
 * @version 6.0-unified
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

// 必要なデータを取得
$post_id = get_the_ID();
$grant_amount = gi_safe_get_meta($post_id, 'grant_amount', 0);
$success_rate = gi_safe_get_meta($post_id, 'grant_success_rate', 0);
$difficulty = gi_safe_get_meta($post_id, 'grant_difficulty', 'normal');
$prefecture = gi_get_prefecture_name($post_id);
$category = gi_get_category_name($post_id);
$application_status = gi_safe_get_meta($post_id, 'application_status', 'closed');
$deadline = gi_safe_get_meta($post_id, 'application_deadline', '');

// ステータスの設定
$status_config = array(
    'open' => array(
        'icon' => 'fas fa-check-circle',
        'color' => 'var(--semantic-success)',
        'pulse' => true,
        'label' => '募集中'
    ),
    'upcoming' => array(
        'icon' => 'fas fa-clock',
        'color' => 'var(--semantic-warning)',
        'pulse' => false,
        'label' => '募集予定'
    ),
    'closed' => array(
        'icon' => 'fas fa-times-circle',
        'color' => 'var(--neutral-400)',
        'pulse' => false,
        'label' => '募集終了'
    )
);

$status_data = $status_config[$application_status] ?? $status_config['closed'];

// 難易度の設定
$difficulty_config = array(
    'easy' => array(
        'stars' => 1,
        'color' => 'var(--semantic-success)',
        'label' => '易しい'
    ),
    'normal' => array(
        'stars' => 2,
        'color' => 'var(--brand-primary)',
        'label' => '普通'
    ),
    'hard' => array(
        'stars' => 3,
        'color' => 'var(--semantic-warning)',
        'label' => '難しい'
    ),
    'expert' => array(
        'stars' => 4,
        'color' => 'var(--semantic-danger)',
        'label' => '専門的'
    )
);

$difficulty_data = $difficulty_config[$difficulty] ?? $difficulty_config['normal'];

// 採択率の色分け
$success_rate_color = 'var(--neutral-500)';
if ($success_rate >= 70) {
    $success_rate_color = 'var(--semantic-success)';
} elseif ($success_rate >= 50) {
    $success_rate_color = 'var(--semantic-warning)';
} elseif ($success_rate > 0) {
    $success_rate_color = 'var(--semantic-danger)';
}
?>

<div class="grant-card-mobile-compact relative rounded-xl p-4 transition-all" 
     style="background: var(--neutral-white); border: 1px solid var(--neutral-200); box-shadow: var(--shadow-sm);" 
     data-post-id="<?php echo esc_attr($post_id); ?>"
     onmouseover="this.style.boxShadow='var(--shadow-md)'; this.style.transform='translateY(-2px)';" 
     onmouseout="this.style.boxShadow='var(--shadow-sm)'; this.style.transform='translateY(0)';">
     
    <!-- 左上のステータスアイコン -->
    <div class="absolute top-3 left-3 w-8 h-8 rounded-full flex items-center justify-center z-10 border-2 border-white" 
         style="background: <?php echo $status_data['color']; ?>; box-shadow: var(--shadow-sm); <?php echo $status_data['pulse'] ? 'animation: pulse 2s infinite;' : ''; ?>">
        <i class="<?php echo esc_attr($status_data['icon']); ?> text-white text-xs"></i>
    </div>
    
    <!-- カードメインコンテンツ -->
    <div class="pl-12 pr-2">
        <!-- タイトル -->
        <h3 class="grant-card-title text-sm font-semibold leading-tight mb-3 line-clamp-2 min-h-[2.5rem]">
            <a href="<?php echo esc_url(get_permalink()); ?>" 
               class="transition-colors touch-manipulation block py-1" 
               style="min-height: 44px; color: var(--neutral-900);"
               onmouseover="this.style.color='var(--brand-primary)';" 
               onmouseout="this.style.color='var(--neutral-900)';">
                <?php echo esc_html(get_the_title()); ?>
            </a>
        </h3>
        
        <!-- メタ情報（簡略化） -->
        <div class="grant-card-meta flex flex-wrap gap-2 mb-3">
            <?php if ($prefecture): ?>
            <span class="grant-meta-item text-xs px-2 py-1 rounded" 
                  style="background: var(--brand-primary-100); color: var(--brand-primary-700);">
                📍 <?php echo esc_html($prefecture); ?>
            </span>
            <?php endif; ?>
            
            <?php if ($category): ?>
            <span class="grant-meta-item text-xs px-2 py-1 rounded" 
                  style="background: var(--brand-secondary-100); color: var(--brand-secondary-700);">
                🏷️ <?php echo esc_html(mb_strimwidth($category, 0, 10, '...')); ?>
            </span>
            <?php endif; ?>
        </div>
        
        <!-- 金額表示 -->
        <div class="grant-card-amount text-base font-bold mb-3" 
             style="color: <?php echo $grant_amount > 0 ? 'var(--brand-primary)' : 'var(--neutral-500)'; ?>;">
            💰 <?php echo $grant_amount > 0 ? gi_format_amount($grant_amount) : '要相談'; ?>
        </div>
        
        <!-- 採択率と難易度を横並び -->
        <div class="grant-card-stats flex justify-between items-center mb-4 gap-2">
            <!-- 採択率 -->
            <div class="success-rate-mobile flex items-center gap-1 text-xs px-2 py-1 rounded flex-1" 
                 style="background: var(--neutral-100);">
                <span class="text-xs">📊</span>
                <?php if ($success_rate > 0): ?>
                    <span class="font-medium" style="color: <?php echo $success_rate_color; ?>;"><?php echo $success_rate; ?>%</span>
                <?php else: ?>
                    <span style="color: var(--neutral-500);">未公開</span>
                <?php endif; ?>
            </div>
            
            <!-- 難易度 -->
            <div class="difficulty-mobile flex items-center gap-1 text-xs px-2 py-1 rounded flex-1" 
                 style="background: var(--neutral-100);">
                <div class="flex" style="color: <?php echo $difficulty_data['color']; ?>; font-size: 10px;">
                    <?php for ($i = 1; $i <= $difficulty_data['stars']; $i++): ?>
                        <i class="fas fa-star"></i>
                    <?php endfor; ?>
                </div>
                <span class="text-xs" style="color: var(--neutral-700);"><?php echo $difficulty_data['label']; ?></span>
            </div>
        </div>
        
        <!-- 締切情報（重要な場合のみ表示） -->
        <?php if ($deadline && $application_status === 'open'): ?>
            <?php
            $deadline_date = DateTime::createFromFormat('Y-m-d', $deadline);
            $now = new DateTime();
            if ($deadline_date && $deadline_date > $now):
                $interval = $now->diff($deadline_date);
                if ($interval->days <= 30):
            ?>
            <div class="mb-3 px-2 py-1 border rounded text-xs" 
                 style="background: var(--semantic-danger-50); border-color: var(--semantic-danger); color: var(--semantic-danger);">
                ⏰ 締切まで<?php echo $interval->days; ?>日
            </div>
            <?php endif; endif; ?>
        <?php endif; ?>
        
        <!-- アクションボタン -->
        <div class="grant-card-actions flex gap-2">
            <!-- 詳細ボタン -->
            <a href="<?php echo esc_url(get_permalink()); ?>" 
               class="grant-card-btn flex-1 py-2 px-3 text-center text-xs font-medium rounded touch-manipulation flex items-center justify-center transition-all" 
               style="min-height: 44px; background: var(--gradient-primary); color: var(--neutral-white);"
               onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='var(--shadow-md)';" 
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                詳細を見る
            </a>
            
            <!-- お気に入りボタン -->
            <button class="grant-card-btn w-11 h-11 border transition-all rounded touch-manipulation flex items-center justify-center favorite-btn" 
                    style="min-height: 44px; min-width: 44px; border-color: var(--neutral-300); color: var(--neutral-600); background: var(--neutral-white);" 
                    data-post-id="<?php echo esc_attr($post_id); ?>"
                    title="お気に入りに追加"
                    onmouseover="this.style.borderColor='var(--brand-primary)'; this.style.color='var(--brand-primary)';" 
                    onmouseout="this.style.borderColor='var(--neutral-300)'; this.style.color='var(--neutral-600)';">
                <i class="far fa-heart text-sm"></i>
            </button>
            
            <!-- シェアボタン -->
            <button class="grant-card-btn w-11 h-11 border transition-all rounded touch-manipulation flex items-center justify-center share-btn"
                    style="min-height: 44px; min-width: 44px; border-color: var(--neutral-300); color: var(--neutral-600); background: var(--neutral-white);"
                    data-url="<?php echo esc_url(get_permalink()); ?>"
                    data-title="<?php echo esc_attr(get_the_title()); ?>"
                    title="シェア"
                    onmouseover="this.style.borderColor='var(--brand-secondary)'; this.style.color='var(--brand-secondary)';" 
                    onmouseout="this.style.borderColor='var(--neutral-300)'; this.style.color='var(--neutral-600)';">
                <i class="fas fa-share text-sm"></i>
            </button>
        </div>
    </div>
    
    <!-- ホバー効果用のオーバーレイ -->
    <div class="absolute inset-0 opacity-0 transition-opacity rounded-xl pointer-events-none" 
         style="background: var(--brand-primary-50);"></div>
</div>

<style>
/* モバイルコンパクトカード専用スタイル */
.grant-card-mobile-compact {
    animation: slideInUp 0.4s ease-out forwards;
    animation-delay: calc(var(--card-index, 0) * 0.05s);
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

/* line-clampユーティリティ */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* タッチ操作の最適化 */
@media (hover: none) and (pointer: coarse) {
    .grant-card-mobile-compact .grant-card-btn:hover {
        transform: none !important;
        box-shadow: none !important;
    }
    
    .grant-card-mobile-compact:hover {
        transform: none !important;
        box-shadow: var(--shadow-sm) !important;
    }
}

/* 高コントラスト対応 */
@media (prefers-contrast: high) {
    .grant-card-mobile-compact {
        border-width: 2px !important;
    }
    
    .grant-card-mobile-compact .grant-card-btn {
        border-width: 2px !important;
    }
}

/* モーション軽減対応 */
@media (prefers-reduced-motion: reduce) {
    .grant-card-mobile-compact {
        animation: none;
    }
    
    .grant-card-mobile-compact,
    .grant-card-mobile-compact .grant-card-btn {
        transition: none !important;
    }
    
    .grant-card-mobile-compact .absolute div {
        animation: none !important;
    }
}

/* 小画面対応 */
@media (max-width: 380px) {
    .grant-card-mobile-compact {
        padding: 0.75rem;
    }
    
    .grant-card-mobile-compact .pl-12 {
        padding-left: 2.5rem;
    }
    
    .grant-card-mobile-compact .grant-card-actions {
        gap: 0.5rem;
    }
    
    .grant-card-mobile-compact .grant-card-btn {
        padding: 0.5rem;
        font-size: 0.75rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // お気に入りボタンの処理
    document.querySelectorAll('.favorite-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const postId = this.getAttribute('data-post-id');
            const isFavorited = this.classList.contains('favorited');
            
            // 既存のお気に入り機能があれば使用、なければローカルストレージ
            if (typeof gi_toggle_favorite === 'function') {
                gi_toggle_favorite(postId, this);
            } else {
                toggleLocalFavorite(postId, this);
            }
        });
    });
    
    // シェアボタンの処理
    document.querySelectorAll('.share-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const url = this.getAttribute('data-url');
            const title = this.getAttribute('data-title');
            
            if (navigator.share) {
                // Web Share API対応ブラウザ
                navigator.share({
                    title: title,
                    url: url
                }).catch(err => {
                    console.log('Share cancelled:', err);
                });
            } else {
                // フォールバック：クリップボードにコピー
                navigator.clipboard.writeText(url).then(() => {
                    showToast('URLをコピーしました');
                }).catch(() => {
                    // 古いブラウザ対応
                    const textArea = document.createElement('textarea');
                    textArea.value = url;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    showToast('URLをコピーしました');
                });
            }
        });
    });
    
    // ローカルお気に入り機能
    function toggleLocalFavorite(postId, btn) {
        let favorites = JSON.parse(localStorage.getItem('gi_favorites') || '[]');
        const isFavorited = btn.classList.contains('favorited');
        
        if (isFavorited) {
            favorites = favorites.filter(id => id !== postId);
            btn.classList.remove('favorited');
            btn.innerHTML = '<i class="far fa-heart text-sm"></i>';
            btn.title = 'お気に入りに追加';
            showToast('お気に入りから削除しました');
        } else {
            favorites.push(postId);
            btn.classList.add('favorited');
            btn.innerHTML = '<i class="fas fa-heart text-sm"></i>';
            btn.title = 'お気に入りから削除';
            btn.style.background = 'var(--brand-primary)';
            btn.style.borderColor = 'var(--brand-primary)';
            btn.style.color = 'var(--neutral-white)';
            showToast('お気に入りに追加しました');
        }
        
        localStorage.setItem('gi_favorites', JSON.stringify(favorites));
    }
    
    // 簡単なトースト通知
    function showToast(message) {
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--neutral-900);
            color: var(--neutral-white);
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            z-index: 1000;
            box-shadow: var(--shadow-lg);
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        document.body.appendChild(toast);
        
        // アニメーション
        setTimeout(() => toast.style.opacity = '1', 10);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => document.body.removeChild(toast), 300);
        }, 2000);
    }
    
    // カードのインデックス設定（アニメーション用）
    document.querySelectorAll('.grant-card-mobile-compact').forEach(function(card, index) {
        card.style.setProperty('--card-index', index);
    });
    
    // 初期お気に入り状態の設定
    const favorites = JSON.parse(localStorage.getItem('gi_favorites') || '[]');
    document.querySelectorAll('.favorite-btn').forEach(function(btn) {
        const postId = btn.getAttribute('data-post-id');
        if (favorites.includes(postId)) {
            btn.classList.add('favorited');
            btn.innerHTML = '<i class="fas fa-heart text-sm"></i>';
            btn.title = 'お気に入りから削除';
            btn.style.background = 'var(--brand-primary)';
            btn.style.borderColor = 'var(--brand-primary)';
            btn.style.color = 'var(--neutral-white)';
        }
    });
});
</script>