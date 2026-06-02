<?php
header("Content-Type: application/json; charset=utf-8");

// DB接続情報（環境に合わせて変更）
$host = "mysql80-1.lolipop.lan";
$user = "LAA1689293";
$pass = "dday194466";
$db   = "LAA1689293-qbayproject";

try {
    // DB接続
    $mysqli = new mysqli($host, $user, $pass, $db);

    if ($mysqli->connect_errno) {
        echo json_encode(["success" => false, "error" => "DB接続失敗: " . $mysqli->connect_error], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // POSTデータ受け取り
    $post_id = $_POST["post-id"] ?? null;
    $email   = $_POST["email"] ?? null;
    $details = $_POST["details"] ?? null;

    // 必須チェック
    if (empty($details)) {
        echo json_encode(["success" => false, "error" => "内容は必須です"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // SQL準備
    $stmt = $mysqli->prepare("INSERT INTO inquiries (post_id, email, details, created_at) VALUES (?, ?, ?, NOW())");
    if (!$stmt) {
        echo json_encode(["success" => false, "error" => "SQL準備失敗: " . $mysqli->error], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // バインド
    $stmt->bind_param("sss", $post_id, $email, $details);

    // 実行
    if ($stmt->execute()) {
        echo json_encode(["success" => true], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["success" => false, "error" => "保存失敗: " . $stmt->error], JSON_UNESCAPED_UNICODE);
    }

    $stmt->close();
    $mysqli->close();

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "サーバーエラー: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}