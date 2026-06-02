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
    header('Content-Type: application/json; charset=utf-8');
    exit(json_encode(["error" => "DB接続エラー: " . $e->getMessage()]));
}

// -----------------------------
// 投稿保存 (POST)
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = $_POST['name'] ?? '';
    $category   = $_POST['category'] ?? '';
    $comment    = $_POST['comment'] ?? '';
    $mode       = $_POST['mode'] ?? '';
    $created_at = date("Y-m-d H:i:s");

    if ($name !== '' && $comment !== '') {
        try {
            // postsテーブルに保存
            $stmt = $pdo->prepare("INSERT INTO posts (name, category, comment, mode, created_at) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $category, $comment, $mode, $created_at]);
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

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                "status"     => "ok",
                "post_id"    => intval($post_id),
                "created_at" => $created_at
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (PDOException $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(["error" => "保存エラー: " . $e->getMessage()]);
            exit;
        }
    } else {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(["error" => "名前と内容を入力してください。"]);
        exit;
    }
}

// -----------------------------
// 投稿取得 (GET by id)
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // ✅ カテゴリ変換マップ
    $categoryMap = [
        "1" => "生活",
        "2" => "健康、美容",
        "3" => "ファッション",
        "4" => "音楽",
        "5" => "趣味",
        "6" => "恋愛",
        "7" => "動物",
        "8" => "仕事",
        "9" => "学問",
        "10" => "政治",
        "11" => "スポーツ",
        "12" => "芸能人",
        "13" => "テレビ",
        "14" => "食事、グルメ",
        "15" => "その他"
    ];

    try {
        // 投稿取得
        $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(["error" => "投稿が見つかりません"]);
            exit;
        }

        // 質問取得
        $stmtQ = $pdo->prepare("SELECT question_text FROM post_questions WHERE post_id = ?");
        $stmtQ->execute([$post['id']]);
        $questions = $stmtQ->fetchAll(PDO::FETCH_COLUMN);

        // 回答数取得
        $stmtAns = $pdo->prepare("SELECT COUNT(*) FROM answers WHERE post_id = ?");
        $stmtAns->execute([$post['id']]);
        $answer_count = $stmtAns->fetchColumn();

        // ✅ カテゴリ番号を文字に変換
        $categoryName = $categoryMap[$post['category']] ?? $post['category'];

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            "post_id"      => intval($post['id']),
            "name"         => $post['name'],
            "category"     => $categoryName,   // ← 数字ではなく文字を返す
            "comment"      => $post['comment'],
            "mode"         => $post['mode'],
            "created_at"   => $post['created_at'],
            "questions"    => $questions,
            "answer_count" => intval($answer_count)
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    } catch (PDOException $e) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(["error" => "取得エラー: " . $e->getMessage()]);
        exit;
    }
}

// -----------------------------
// デフォルトレスポンス
// -----------------------------
header('Content-Type: application/json; charset=utf-8');
echo json_encode(["error" => "不正なリクエストです"]);
exit;
?>