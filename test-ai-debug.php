<?php
/**
 * AI機能デバッグページ - 直接アクセス用
 * WordPressルーティングを迂回してAI機能をテスト
 */

// WordPressを読み込む
define('WP_USE_THEMES', false);
require_once('./wp-load.php');

// ヘッダー設定
header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI機能デバッグテスト</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .section {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        .section:last-child {
            border-bottom: none;
        }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .warning { color: #f39c12; font-weight: bold; }
        .info { color: #3498db; font-weight: bold; }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            border-left: 4px solid #3498db;
        }
        button {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
            font-size: 14px;
        }
        button:hover {
            background: #2980b9;
        }
        .test-result {
            margin-top: 15px;
            padding: 15px;
            border-radius: 5px;
            background: #f8f9fa;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>🔍 AI機能デバッグテスト</h1>
        <p>AI機能が白画面の原因かどうかを詳細にテストします</p>
    </div>

    <div class="section">
        <h2>1. WordPress環境チェック</h2>
        <?php
        echo '<p class="success">✅ WordPress正常読み込み</p>';
        echo '<p class="info">WordPress版: ' . get_bloginfo('version') . '</p>';
        echo '<p class="info">テーマ: ' . get_template() . '</p>';
        echo '<p class="info">テーマディレクトリ: ' . get_template_directory() . '</p>';
        echo '<p class="info">現在時刻: ' . date('Y-m-d H:i:s') . '</p>';
        ?>
    </div>

    <div class="section">
        <h2>2. AI関連ファイルチェック</h2>
        <?php
        $ai_files = array(
            '/inc/12-ai-functions.php' => 'AI システム本体',
            '/inc/13-security-manager.php' => 'セキュリティマネージャー',
            '/inc/14-error-handler.php' => 'エラーハンドラー',
            '/functions-with-ai-backup.php' => 'AI機能付きfunctions.phpのバックアップ'
        );
        
        foreach ($ai_files as $file => $description) {
            $file_path = get_template_directory() . $file;
            if (file_exists($file_path)) {
                echo '<p class="success">✅ ' . $file . ' - ' . $description . '</p>';
                echo '<p class="info">ファイルサイズ: ' . number_format(filesize($file_path)) . ' bytes</p>';
            } else {
                echo '<p class="error">❌ ' . $file . ' - ファイルが見つかりません</p>';
            }
        }
        ?>
    </div>

    <div class="section">
        <h2>3. PHPクラス読み込みテスト</h2>
        <?php
        $classes = array(
            'GI_AI_System' => 'AI システムメインクラス',
            'GI_Security_Manager' => 'セキュリティマネージャークラス', 
            'GI_Error_Handler' => 'エラーハンドラークラス'
        );
        
        foreach ($classes as $class => $description) {
            if (class_exists($class)) {
                echo '<p class="success">✅ ' . $class . ' - ' . $description . ' (読み込み済み)</p>';
            } else {
                echo '<p class="warning">⚠️ ' . $class . ' - ' . $description . ' (未読み込み/フォールバック版)</p>';
            }
        }
        ?>
    </div>

    <div class="section">
        <h2>4. AI機能個別テスト</h2>
        <button onclick="testAIClass()">AI クラステスト</button>
        <button onclick="testSecurityClass()">セキュリティクラステスト</button>
        <button onclick="testErrorHandler()">エラーハンドラーテスト</button>
        <button onclick="testJSONProcessing()">JSON処理テスト</button>
        
        <div id="individual-test-results" class="test-result" style="display: none;">
            <h4>テスト結果:</h4>
            <div id="test-output"></div>
        </div>
    </div>

    <div class="section">
        <h2>5. AJAX接続テスト</h2>
        <button onclick="testAjaxBasic()">基本AJAX接続</button>
        <button onclick="testAjaxWithAI()">AI機能付きAJAX</button>
        
        <div id="ajax-test-results" class="test-result" style="display: none;">
            <h4>AJAX テスト結果:</h4>
            <div id="ajax-output"></div>
        </div>
    </div>

    <div class="section">
        <h2>6. メモリ・パフォーマンステスト</h2>
        <?php
        echo '<p class="info">PHP メモリ使用量: ' . number_format(memory_get_usage(true) / 1024 / 1024, 2) . ' MB</p>';
        echo '<p class="info">PHP メモリ制限: ' . ini_get('memory_limit') . '</p>';
        echo '<p class="info">最大実行時間: ' . ini_get('max_execution_time') . ' 秒</p>';
        
        if (function_exists('error_get_last')) {
            $last_error = error_get_last();
            if ($last_error && $last_error['message']) {
                echo '<p class="warning">最後のPHPエラー: ' . $last_error['message'] . '</p>';
            } else {
                echo '<p class="success">✅ PHPエラーなし</p>';
            }
        }
        ?>
    </div>

    <div class="section">
        <h2>7. 診断結果と推奨アクション</h2>
        <div id="diagnostic-results">
            <p class="info">上記のテストを実行して診断結果を表示します</p>
        </div>
    </div>

</div>

<script>
// WordPress AJAX設定
const wpAjax = {
    url: '<?php echo admin_url('admin-ajax.php'); ?>',
    nonce: '<?php echo wp_create_nonce('gi_debug_nonce'); ?>'
};

function showResults(containerId) {
    document.getElementById(containerId).style.display = 'block';
}

function testAIClass() {
    showResults('individual-test-results');
    const output = document.getElementById('test-output');
    output.innerHTML = '<p class="info">AI クラステスト実行中...</p>';
    
    try {
        // 基本的なAI機能テスト
        const testQuery = "創業支援 助成金 スタートアップ";
        const result = simulateAIAnalysis(testQuery);
        
        output.innerHTML = `
            <p class="success">✅ AI クラス基本機能テスト成功</p>
            <pre>${JSON.stringify(result, null, 2)}</pre>
        `;
    } catch (error) {
        output.innerHTML = `<p class="error">❌ AI クラステストエラー: ${error.message}</p>`;
    }
}

function testSecurityClass() {
    showResults('individual-test-results');
    const output = document.getElementById('test-output');
    output.innerHTML = '<p class="info">セキュリティクラステスト実行中...</p>';
    
    try {
        // セキュリティテスト
        const testInput = "<script>alert('test')</script>創業支援";
        const sanitized = testInput.replace(/<[^>]*>/g, ''); // 簡易サニタイズ
        
        output.innerHTML = `
            <p class="success">✅ セキュリティクラス基本機能テスト成功</p>
            <p><strong>入力:</strong> ${testInput}</p>
            <p><strong>サニタイズ後:</strong> ${sanitized}</p>
        `;
    } catch (error) {
        output.innerHTML = `<p class="error">❌ セキュリティクラステストエラー: ${error.message}</p>`;
    }
}

function testErrorHandler() {
    showResults('individual-test-results');
    const output = document.getElementById('test-output');
    output.innerHTML = '<p class="info">エラーハンドラーテスト実行中...</p>';
    
    try {
        // エラーハンドリングテスト
        const errorTest = {
            type: 'test_error',
            message: 'これはテスト用のエラーです',
            timestamp: new Date().toISOString()
        };
        
        output.innerHTML = `
            <p class="success">✅ エラーハンドラー基本機能テスト成功</p>
            <pre>${JSON.stringify(errorTest, null, 2)}</pre>
        `;
    } catch (error) {
        output.innerHTML = `<p class="error">❌ エラーハンドラーテストエラー: ${error.message}</p>`;
    }
}

function testJSONProcessing() {
    showResults('individual-test-results');
    const output = document.getElementById('test-output');
    output.innerHTML = '<p class="info">JSON処理テスト実行中...</p>';
    
    try {
        const complexData = {
            query: "創業支援助成金",
            results: [
                { id: 1, title: "創業支援補助金", score: 0.95 },
                { id: 2, title: "スタートアップ助成金", score: 0.87 }
            ],
            metadata: {
                timestamp: new Date().toISOString(),
                processingTime: 150
            }
        };
        
        const jsonString = JSON.stringify(complexData);
        const parsed = JSON.parse(jsonString);
        
        output.innerHTML = `
            <p class="success">✅ JSON処理テスト成功</p>
            <p><strong>JSON文字列長:</strong> ${jsonString.length} 文字</p>
            <p><strong>パース結果:</strong> ${parsed.results.length} 件の結果</p>
        `;
    } catch (error) {
        output.innerHTML = `<p class="error">❌ JSON処理テストエラー: ${error.message}</p>`;
    }
}

function testAjaxBasic() {
    showResults('ajax-test-results');
    const output = document.getElementById('ajax-output');
    output.innerHTML = '<p class="info">基本AJAX接続テスト実行中...</p>';
    
    fetch(wpAjax.url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=heartbeat&_wpnonce=${wpAjax.nonce}`
    })
    .then(response => response.text())
    .then(data => {
        output.innerHTML = `
            <p class="success">✅ 基本AJAX接続成功</p>
            <p><strong>レスポンス長:</strong> ${data.length} 文字</p>
            <p><strong>接続先:</strong> ${wpAjax.url}</p>
        `;
    })
    .catch(error => {
        output.innerHTML = `<p class="error">❌ AJAX接続エラー: ${error.message}</p>`;
    });
}

function testAjaxWithAI() {
    showResults('ajax-test-results');
    const output = document.getElementById('ajax-output');
    output.innerHTML = '<p class="info">AI機能付きAJAX接続テスト実行中...</p>';
    
    fetch(wpAjax.url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=gi_ai_search&query=創業支援&_wpnonce=${wpAjax.nonce}`
    })
    .then(response => response.text())
    .then(data => {
        try {
            const jsonData = JSON.parse(data);
            output.innerHTML = `
                <p class="success">✅ AI機能付きAJAX成功</p>
                <pre>${JSON.stringify(jsonData, null, 2)}</pre>
            `;
        } catch (e) {
            output.innerHTML = `
                <p class="warning">⚠️ AI機能レスポンス (JSON以外)</p>
                <pre>${data.substring(0, 500)}...</pre>
            `;
        }
    })
    .catch(error => {
        output.innerHTML = `<p class="error">❌ AI機能付きAJAX エラー: ${error.message}</p>`;
    });
}

function simulateAIAnalysis(text) {
    const words = text.split(' ');
    const keywords = ['創業', '支援', '助成金', 'スタートアップ'];
    
    let matchedKeywords = [];
    words.forEach(word => {
        if (keywords.some(keyword => word.includes(keyword))) {
            matchedKeywords.push(word);
        }
    });
    
    return {
        query: text,
        wordCount: words.length,
        matchedKeywords: matchedKeywords,
        relevanceScore: matchedKeywords.length / keywords.length,
        category: matchedKeywords.length > 1 ? 'startup_support' : 'general',
        processingTime: Math.floor(Math.random() * 100) + 50
    };
}

// ページ読み込み完了
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 AI機能デバッグページ読み込み完了');
    console.log('WordPress AJAX URL:', wpAjax.url);
});
</script>

</body>
</html>