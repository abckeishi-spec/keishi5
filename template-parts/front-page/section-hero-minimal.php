<?php
/**
 * Hero Section - Minimal Test Version
 * 
 * @package Grant_Insight_Professional
 * @version 7.1-minimal-test
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

echo '<div style="background: linear-gradient(135deg, #000000, #2d2d30); color: white; padding: 100px 20px; text-align: center; min-height: 80vh;">';
echo '<h1 style="font-size: 3rem; margin-bottom: 20px;">補助金・助成金をAIが瞬時に発見</h1>';
echo '<p style="font-size: 1.2rem; margin-bottom: 40px;">あなたのビジネスに最適な支援制度を、最新AIテクノロジーが瞬時に発見。</p>';
echo '<a href="#search-section" style="display: inline-block; background: white; color: black; padding: 16px 32px; text-decoration: none; border-radius: 8px; font-weight: bold;">無料で助成金を探す</a>';
echo '</div>';

// Debug output
echo '<div style="color: green; font-weight: bold; padding: 10px; background: #e8f5e8;">✅ Minimal Hero Section Loaded Successfully</div>';
?>