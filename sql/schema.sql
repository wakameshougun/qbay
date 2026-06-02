-- テーブル作成
CREATE TABLE posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 初期データ投入（任意）
INSERT INTO posts (name, message) VALUES ('管理者', '掲示板へようこそ！');

-- 投稿テーブル作成
CREATE TABLE posts (
  id INT AUTO_INCREMENT PRIMARY KEY,   -- 投稿ID（自動採番）
  name VARCHAR(50) NOT NULL,           -- 投稿者名
  category VARCHAR(50) NOT NULL,       -- カテゴリー名
  comment TEXT NOT NULL,               -- 投稿内容
  questions JSON,                      -- 選択肢式の質問（JSON形式で保存）
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- 投稿日時
);