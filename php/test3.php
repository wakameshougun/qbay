<?php
// 必ずファイルの先頭に何も出力しないこと（空白・改行なし）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json; charset=UTF-8");

// DB接続
$mysqli = new mysqli(
    "mysql80-1.lolipop.lan",
    "LAA1689293",
    "dday194466",
    "LAA1689293-qbayproject"
);

if ($mysqli->connect_error) {
    echo json_encode(["error" => "接続失敗: " . $mysqli->connect_error]);
    exit;
}

// カテゴリ変換マップ
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

$posts = [];

// ✅ 並び順を created_at DESC に変更
$result = $mysqli->query("SELECT * FROM posts ORDER BY created_at DESC");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // 選択肢を取得
        $questions = [];
        $qResult = $mysqli->query(
            "SELECT question_text FROM post_questions WHERE post_id=" . intval($row["id"])
        );
        if ($qResult) {
            while ($q = $qResult->fetch_assoc()) {
                $questions[] = $q["question_text"];
            }
        }

        // 回答数を取得
        $answerCount = 0;
        $stmtAns = $mysqli->prepare("SELECT COUNT(*) FROM answers WHERE post_id = ?");
        if ($stmtAns) {
            $stmtAns->bind_param("i", $row["id"]);
            if ($stmtAns->execute()) {
                $stmtAns->bind_result($answerCount);
                $stmtAns->fetch();
            }
            $stmtAns->close();
        }

        // カテゴリ番号を名詞に変換
        $categoryName = $categoryMap[$row["category"]] ?? $row["category"];

        // 投稿データをまとめる
        $posts[] = [
            "id" => $row["id"],
            "name" => $row["name"],
            "category" => $categoryName,
            "comment" => $row["comment"],   // 内容欄には常に本文
            "questions" => $questions,      // 選択肢は別フィールド
            "mode" => $row["mode"],
            "created_at" => $row["created_at"],
            "answer_count" => $answerCount  // 回答数を追加
        ];
    }
}

echo json_encode($posts, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

$mysqli->close();
?>