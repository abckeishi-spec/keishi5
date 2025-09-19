<?php
/**
 * Template Name: AI機能テストページ
 * WordPress環境でのAI機能テストページ
 */

get_header(); ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>WordPress AI機能テスト</title>
    <style>
        .test-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            font-family: monospace;
        }
        .test-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #007bff; }
        pre { background: #e9ecef; padding: 10px; border-radius: 3px; }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>

<div class="test-container">
    <h1>WordPress AI機能テスト</h1>
    
    <div class="test-section">
        <h2>1. WordPress基本機能チェック</h2>
        <?php
        echo '<p class="success">✅ WordPress環境: OK</p>';
        echo '<p class="info">テーマディレクトリ: ' . get_template_directory() . '</p>';
        echo '<p class="info">WordPress版: ' . get_bloginfo('version') . '</p>';
        
        // AI関連ファイルの存在確認
        $ai_files = array(
            '/inc/12-ai-functions.php',
            '/inc/13-security-manager.php', 
            '/inc/14-error-handler.php'
        );
        
        foreach ($ai_files as $file) {
            $file_path = get_template_directory() . $file;
            if (file_exists($file_path)) {
                echo '<p class="success">✅ ' . $file . ' 存在</p>';
            } else {
                echo '<p class="error">❌ ' . $file . ' 不存在</p>';
            }
        }
        ?>
    </div>

    <div class="test-section">
        <h2>2. AI関連クラス読み込みテスト</h2>
        <?php
        // AI関連クラスの存在確認
        $ai_classes = array(
            'GI_AI_System',
            'GI_Security_Manager', 
            'GI_Error_Handler'
        );
        
        foreach ($ai_classes as $class_name) {
            if (class_exists($class_name)) {
                echo '<p class="success">✅ ' . $class_name . ' クラス読み込み成功</p>';
            } else {
                echo '<p class="warning">⚠️ ' . $class_name . ' クラス未読み込み（フォールバック版使用中）</p>';
            }
        }
        ?>
    </div>

    <div class="test-section">
        <h2>3. WordPress関数テスト</h2>
        <?php
        // AJAX URL の確認
        if (function_exists('admin_url')) {
            $ajax_url = admin_url('admin-ajax.php');
            echo '<p class="success">✅ AJAX URL: ' . $ajax_url . '</p>';
        }
        
        // wp_nonce の確認
        if (function_exists('wp_create_nonce')) {
            $nonce = wp_create_nonce('gi_ai_nonce');
            echo '<p class="success">✅ Nonce生成: ' . substr($nonce, 0, 10) . '...</p>';
        }
        
        // wp_enqueue_script の確認
        if (function_exists('wp_enqueue_script')) {
            echo '<p class="success">✅ wp_enqueue_script 利用可能</p>';
        }
        ?>
    </div>

    <div class="test-section">
        <h2>4. AI機能簡単テスト</h2>
        <button onclick="testBasicAI()">基本AI機能テスト</button>
        <button onclick="testAjaxConnection()">AJAX接続テスト</button>
        <div id="ai-test-results"></div>
    </div>

    <div class="test-section">
        <h2>5. 現在の状態</h2>
        <div id="error-log">
            <?php
            echo '<p class="info">🔧 AI機能は現在一時的に無効化されています</p>';
            echo '<p class="info">📄 front-page.php: シンプル版を使用中</p>';
            echo '<p class="info">⚙️ functions.php: AI機能無効版を使用中</p>';
            
            // PHP エラーログの確認
            if (function_exists('error_get_last')) {
                $last_error = error_get_last();
                if ($last_error) {
                    echo '<p class="warning">最後のPHPエラー:</p>';
                    echo '<pre>' . print_r($last_error, true) . '</pre>';
                } else {
                    echo '<p class="success">✅ PHPエラーなし</p>';
                }
            }
            ?>
        </div>
    </div>

    <div class="test-section">
        <h2>6. テストリンク</h2>
        <p><a href="<?php echo home_url('/ai-test-standalone.php'); ?>" target="_blank">🔗 AI単体テストページ</a></p>
        <p><a href="<?php echo home_url(); ?>" target="_blank">🔗 メインページ（修復版）</a></p>
    </div>

</div>

<script>
// AJAX URL と nonce を JavaScript に渡す
const ajaxSettings = {
    url: '<?php echo admin_url('admin-ajax.php'); ?>',
    nonce: '<?php echo wp_create_nonce('gi_ai_nonce'); ?>'
};

function testBasicAI() {
    const resultsDiv = document.getElementById('ai-test-results');
    resultsDiv.innerHTML = '<p class="info">基本AI機能をテスト中...</p>';
    
    // 基本的なJavaScript AI処理
    const testData = {
        query: "創業支援 助成金",
        timestamp: new Date().toISOString()
    };
    
    // シンプルなテキスト分析
    const analysis = analyzeText(testData.query);
    
    resultsDiv.innerHTML = `
        <h4>基本AI分析結果:</h4>
        <pre>${JSON.stringify({
            input: testData.query,
            analysis: analysis,
            status: 'success - AI機能なしでも基本分析可能'
        }, null, 2)}</pre>
    `;
}

function analyzeText(text) {
    const words = text.toLowerCase().split(' ');
    const keywords = ['創業', '支援', '助成金', 'スタートアップ', '補助金'];
    
    let relevantKeywords = [];
    let score = 0;
    
    words.forEach(word => {
        if (keywords.includes(word)) {
            relevantKeywords.push(word);
            score += 1;
        }
    });
    
    return {
        wordCount: words.length,
        relevantKeywords: relevantKeywords,
        relevanceScore: score,
        category: score > 1 ? 'startup_support' : 'general',
        confidence: Math.min(score * 0.3, 1.0)
    };
}

function testAjaxConnection() {
    const resultsDiv = document.getElementById('ai-test-results');
    resultsDiv.innerHTML = '<p class="info">AJAX接続をテスト中...</p>';
    
    // テスト用のAJAXリクエスト
    const xhr = new XMLHttpRequest();
    xhr.open('POST', ajaxSettings.url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                resultsDiv.innerHTML = `
                    <h4>AJAX接続テスト結果:</h4>
                    <p class="success">✅ AJAX接続成功 (ステータス: ${xhr.status})</p>
                    <p class="info">レスポンス長: ${xhr.responseText.length} 文字</p>
                    <pre>${xhr.responseText.substring(0, 300)}...</pre>
                `;
            } else {
                resultsDiv.innerHTML = `
                    <p class="error">❌ AJAX エラー: ${xhr.status} ${xhr.statusText}</p>
                `;
            }
        }
    };
    
    xhr.onerror = function() {
        resultsDiv.innerHTML = '<p class="error">❌ AJAX 接続エラー</p>';
    };
    
    // テスト用のデータを送信
    const data = 'action=gi_test_basic&nonce=' + ajaxSettings.nonce + '&test_data=' + encodeURIComponent('基本テスト');
    xhr.send(data);
}

// ページ読み込み時にログ出力
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 WordPress AI機能テストページ読み込み完了');
    console.log('AJAX設定:', ajaxSettings);
});
</script>

</body>
</html>

<?php get_footer(); ?>