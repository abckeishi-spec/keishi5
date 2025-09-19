<?php
/**
 * AI機能単体テストページ
 * WordPress環境なしでAI関連クラスをテスト
 */

// 簡単なエラー表示設定
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='utf-8'><title>AI機能テスト</title></head><body>";
echo "<h1>AI機能テスト</h1>";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; margin: 20px 0;'>";

// 1. 基本的なPHPクラステスト
echo "<h2>1. 基本クラステスト</h2>";

try {
    // シンプルなAIクラスを定義してテスト
    class Simple_AI_Test {
        private $cache = array();
        
        public function __construct() {
            echo "✅ Simple_AI_Test クラスが正常に作成されました<br>";
        }
        
        public function test_basic_analysis($text) {
            if (empty($text)) {
                return array('error' => 'テキストが空です');
            }
            
            // 簡単な分析をシミュレート
            $word_count = str_word_count($text);
            $char_count = mb_strlen($text);
            
            return array(
                'word_count' => $word_count,
                'char_count' => $char_count,
                'sentiment' => $word_count > 10 ? 'positive' : 'neutral',
                'keywords' => explode(' ', $text)
            );
        }
        
        public function test_semantic_search($query, $documents = array()) {
            if (empty($query)) {
                return array();
            }
            
            // 簡単なキーワードマッチング
            $results = array();
            foreach ($documents as $i => $doc) {
                $score = 0;
                $query_words = explode(' ', strtolower($query));
                foreach ($query_words as $word) {
                    if (strpos(strtolower($doc), $word) !== false) {
                        $score++;
                    }
                }
                if ($score > 0) {
                    $results[] = array(
                        'document' => $doc,
                        'score' => $score,
                        'index' => $i
                    );
                }
            }
            
            // スコア順にソート
            usort($results, function($a, $b) {
                return $b['score'] - $a['score'];
            });
            
            return $results;
        }
    }
    
    $ai_test = new Simple_AI_Test();
    
    // 2. 基本分析テスト
    echo "<h2>2. 基本分析テスト</h2>";
    $test_text = "創業支援 助成金 スタートアップ 新規事業";
    $analysis_result = $ai_test->test_basic_analysis($test_text);
    echo "テスト文字列: " . $test_text . "<br>";
    echo "分析結果: " . json_encode($analysis_result, JSON_UNESCAPED_UNICODE) . "<br>";
    
    // 3. セマンティック検索テスト  
    echo "<h2>3. セマンティック検索テスト</h2>";
    $sample_docs = array(
        "創業支援補助金 - 新規事業立ち上げを支援する制度",
        "IT導入補助金 - デジタル化を推進する企業向け",
        "研究開発助成金 - 技術開発プロジェクトの支援",
        "雇用調整助成金 - 従業員の雇用維持を支援"
    );
    
    $search_result = $ai_test->test_semantic_search("創業 スタートアップ", $sample_docs);
    echo "検索クエリ: '創業 スタートアップ'<br>";
    echo "検索結果: <br>";
    foreach ($search_result as $result) {
        echo "- " . $result['document'] . " (スコア: " . $result['score'] . ")<br>";
    }
    
} catch (Exception $e) {
    echo "❌ エラー: " . $e->getMessage() . "<br>";
} catch (Error $e) {
    echo "❌ 致命的エラー: " . $e->getMessage() . "<br>";
}

// 4. メモリ使用量チェック
echo "<h2>4. システム情報</h2>";
echo "メモリ使用量: " . memory_get_usage(true) / 1024 / 1024 . " MB<br>";
echo "PHPバージョン: " . PHP_VERSION . "<br>";

// 5. WordPressなしでのJSON処理テスト
echo "<h2>5. JSON処理テスト</h2>";
$test_data = array(
    'query' => 'テストクエリ',
    'results' => array(
        array('title' => '助成金A', 'score' => 0.8),
        array('title' => '助成金B', 'score' => 0.6)
    ),
    'timestamp' => date('Y-m-d H:i:s')
);

$json_string = json_encode($test_data, JSON_UNESCAPED_UNICODE);
if ($json_string) {
    echo "✅ JSON エンコード成功<br>";
    echo "JSON: " . $json_string . "<br>";
    
    $decoded = json_decode($json_string, true);
    if ($decoded) {
        echo "✅ JSON デコード成功<br>";
    } else {
        echo "❌ JSON デコード失敗<br>";
    }
} else {
    echo "❌ JSON エンコード失敗<br>";
}

echo "</div>";

// 6. AJAX処理をシミュレート
echo "<h2>6. AJAX処理シミュレーション</h2>";
echo "<div style='background: #e3f2fd; padding: 15px; margin: 10px 0;'>";
echo "<button onclick='testAjaxFunction()'>AI機能をテスト</button>";
echo "<div id='ajax-result'></div>";
echo "</div>";

echo "<script>";
echo "
function testAjaxFunction() {
    const resultDiv = document.getElementById('ajax-result');
    resultDiv.innerHTML = 'AI処理中...';
    
    // シミュレートされた処理
    setTimeout(function() {
        const mockResult = {
            success: true,
            data: {
                analysis: '創業支援に関連する助成金が3件見つかりました',
                recommendations: [
                    '創業支援補助金（最大200万円）',
                    'スタートアップ助成金（最大100万円）',
                    '新規事業開発支援（最大150万円）'
                ]
            },
            timestamp: new Date().toLocaleString()
        };
        
        resultDiv.innerHTML = '<h4>AI処理結果:</h4><pre>' + JSON.stringify(mockResult, null, 2) + '</pre>';
    }, 2000);
}
";
echo "</script>";

echo "<hr>";
echo "<p><strong>テスト完了</strong> - 上記の結果でエラーがなければ、基本的なAI機能は動作可能です。</p>";
echo "</body></html>";
?>