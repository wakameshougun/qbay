<?php
header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

try {
    // DB接続
    $mysqli = new mysqli(
        "mysql80-1.lolipop.lan",
        "LAA1689293",
        "dday194466",
        "LAA1689293-qbayproject"
    );

    if ($mysqli->connect_errno) {
        echo json_encode(["error" => "DB接続失敗: " . $mysqli->connect_error], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ID取得とバリデーション
    $id = $_GET['id'] ?? null;
    if (!$id || !is_numeric($id)) {
        echo json_encode(["error" => "有効なIDが指定されていません"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 投稿データ取得
    $stmt = $mysqli->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $post = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$post) {
        echo json_encode(["error" => "投稿が見つかりません"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 選択肢取得（modeがselectの場合のみ）
    $questions = [];
    if ($post["mode"] === "select") {
        $stmt = $mysqli->prepare("SELECT question_text FROM post_questions WHERE post_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $questions[] = trim($row["question_text"]);
        }
        $stmt->close();
    }
    $post["questions"] = $questions;

    // 回答取得
    $stmt = $mysqli->prepare("SELECT answer_value FROM answers WHERE post_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $answers = [];
    while ($row = $result->fetch_assoc()) {
        $answers[] = trim($row["answer_value"]);
    }
    $stmt->close();

    // JSON返却
    echo json_encode([
        "post" => $post,
        "answers" => $answers
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    $mysqli->close();

} catch (Exception $e) {
    echo json_encode(["error" => "サーバーエラー: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}