<?php
// エラー表示（開発中のみ）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DB接続情報
$dsn = 'mysql:host=mysql80-1.lolipop.lan;dbname=LAA1689293-qbayproject;charset=utf8mb4';
$user = 'LAA1689293';
$password = 'dday194466';

try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit("<script>alert('DB接続エラー: " . $e->getMessage() . "'); window.location.href='../index.html';</script>");
}

// -----------------------------
// 投稿処理
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $comment  = $_POST['comment'] ?? '';
    $mode     = $_POST['mode'] ?? '';

    // ✅ 未記入チェック
    if (trim($name) === '' || trim($comment) === '' || trim($category) === '' || trim($mode) === '') {
        echo "<script>alert('⚠️ 必須項目が入力されていません。'); window.location.href='../index.html';</script>";
        exit;
    }

    try {
        // postsテーブルに保存
        $stmt = $pdo->prepare("INSERT INTO posts (name, category, comment, mode, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$name, $category, $comment, $mode]);
        $post_id = $pdo->lastInsertId();

        // 選択肢式の場合、post_questionsに保存
        if ($mode === 'select') {
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'question') === 0 && $value !== '') {
                    $stmtQ = $pdo->prepare("INSERT INTO post_questions (post_id, question_text) VALUES (?, ?)");
                    $stmtQ->execute([$post_id, $value]);
                }
            }
        }

        // ✅ 投稿完了後にポップアップを表示してトップへ戻す
        echo "<script>alert('投稿を保存しました。'); window.location.href='../index.html';</script>";
        exit;

    } catch (PDOException $e) {
        echo "<script>alert('保存エラー: " . $e->getMessage() . "'); window.location.href='../index.html';</script>";
        exit;
    }
}

// -----------------------------
// 投稿一覧表示
// -----------------------------
try {
    // 新しい順に並べ替え、回答数も取得
    $stmt = $pdo->query("
        SELECT p.*, 
               (SELECT COUNT(*) FROM answers a WHERE a.post_id = p.id) AS answer_count
        FROM posts p
        ORDER BY created_at DESC
    ");

    foreach ($stmt as $row) {
        echo "<hr>";
        echo "ID: " . htmlspecialchars($row['id']) . "<br>";
        echo "名前: " . htmlspecialchars($row['name']) . "<br>";
        echo "カテゴリー: " . htmlspecialchars($row['category']) . "<br>";
        echo "内容: " . nl2br(htmlspecialchars($row['comment'])) . "<br>";
        echo "入力方法: " . htmlspecialchars($row['mode']) . "<br>";
        echo "日時: " . $row['created_at'] . "<br>";
        echo "回答数: " . intval($row['answer_count']) . "<br>";

        // 選択肢がある場合は表示
        $stmtQ = $pdo->prepare("SELECT question_text FROM post_questions WHERE post_id = ?");
        $stmtQ->execute([$row['id']]);
        $questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);
        if ($questions) {
            echo "選択肢:<br>";
            foreach ($questions as $q) {
                echo "- " . htmlspecialchars($q['question_text']) . "<br>";
            }
        }
    }
} catch (PDOException $e) {
    echo "<p style='color:red;'>一覧表示エラー: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>