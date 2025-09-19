<?php
/**
 * Search Section - Minimal Test Version
 * 
 * @package Grant_Insight_Professional
 * @version 7.1-minimal-test
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

echo '<div style="background: #f8f9fa; padding: 80px 20px; text-align: center;">';
echo '<h2 style="font-size: 2.5rem; margin-bottom: 20px; color: #333;">助成金検索システム</h2>';
echo '<p style="font-size: 1.1rem; margin-bottom: 30px; color: #666;">最新AIが最適な助成金を発見します</p>';
echo '<div style="max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">';
echo '<input type="text" placeholder="業種や目的を入力してください" style="width: 100%; padding: 15px; border: 2px solid #ddd; border-radius: 6px; font-size: 16px;">';
echo '<button style="width: 100%; margin-top: 15px; background: #333; color: white; padding: 15px; border: none; border-radius: 6px; font-size: 16px; cursor: pointer;">検索開始</button>';
echo '</div>';
echo '</div>';

// Debug output
echo '<div style="color: green; font-weight: bold; padding: 10px; background: #e8f5e8;">✅ Minimal Search Section Loaded Successfully</div>';
?>