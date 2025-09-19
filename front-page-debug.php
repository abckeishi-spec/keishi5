<?php
/**
 * Debug Front Page
 * 白画面問題のデバッグ用
 */

// 直接出力テスト
echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<meta charset='utf-8'>";
echo "<title>Debug Test</title>";
echo "</head>";
echo "<body>";
echo "<h1>デバッグテスト</h1>";
echo "<p>このテキストが表示されれば基本的なPHPは動作しています。</p>";

// WordPressが使えるかテスト
if (function_exists('get_header')) {
    echo "<p>✅ WordPress関数が使用可能です</p>";
} else {
    echo "<p>❌ WordPress関数が使用できません</p>";
}

// テンプレートパーツの存在確認
$hero_path = get_template_directory() . '/template-parts/front-page/section-hero.php';
if (file_exists($hero_path)) {
    echo "<p>✅ Hero template exists: " . $hero_path . "</p>";
} else {
    echo "<p>❌ Hero template missing: " . $hero_path . "</p>";
}

echo "</body>";
echo "</html>";