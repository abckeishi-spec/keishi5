<?php
/**
 * Categories Section - Minimal Test Version
 * 
 * @package Grant_Insight_Professional
 * @version 7.1-minimal-test
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

$categories = array(
    '製造業', 'IT・テクノロジー', '医療・介護', 'サービス業', 
    '飲食業', '建設業', '農業', '小売業'
);

echo '<div style="background: #fff; padding: 80px 20px; text-align: center;">';
echo '<h2 style="font-size: 2.5rem; margin-bottom: 40px; color: #333;">カテゴリ別検索</h2>';
echo '<div style="max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">';

foreach ($categories as $category) {
    echo '<div style="background: #f8f9fa; padding: 30px 20px; border-radius: 8px; border: 2px solid #e9ecef; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.background=\'#e9ecef\'" onmouseout="this.style.background=\'#f8f9fa\'">';
    echo '<h3 style="margin: 0; color: #333; font-size: 1.1rem;">' . esc_html($category) . '</h3>';
    echo '</div>';
}

echo '</div>';
echo '</div>';

// Debug output
echo '<div style="color: green; font-weight: bold; padding: 10px; background: #e8f5e8;">✅ Minimal Categories Section Loaded Successfully</div>';
?>