<?php
header('Content-Type: application/json; charset=utf-8');

// --- DB接続 ---
$servername = "mysql80-1.lolipop.lan";
$username   = "LAA1689293";
$password   = "dday194466";
$dbname     = "LAA1689293-qbayproject";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "error"  => "DB接続に失敗しました: " . $conn->connect_error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// --- POST処理（回答保存） ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id   = $_POST['post_id']   ?? null;
    $answer    = $_POST['answer']    ?? null;
    $timestamp = $_POST['created_at'] ?? null;

    // バリデーション
    if (!is_numeric($post_id)) {
        echo json_encode([
            "status" => "error",
            "error"  => "post_idは数値である必要があります"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($answer === null || $timestamp === null) {
        echo json_encode([
            "status" => "error",
            "error"  => "パラメータ不足"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $post_id = intval($post_id);

    // SQL準備
    $stmt = $conn->prepare(
        "INSERT INTO answers (post_id, answer_value, created_at) VALUES (?, ?, ?)"
    );
    if (!$stmt) {
        echo json_encode([
            "status" => "error",
            "error"  => "SQL準備に失敗しました: " . $conn->error
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt->bind_param("iss", $post_id, $answer, $timestamp);

    if ($stmt->execute()) {
        echo json_encode(["status" => "ok"], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            "status" => "error",
            "error"  => "保存に失敗しました: " . $stmt->error
        ], JSON_UNESCAPED_UNICODE);
    }

    $stmt->close();
} else {
    echo json_encode([
        "status" => "error",
        "error"  => "POSTメソッドのみ対応しています"
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();