-- 投稿テーブル
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    category INT,
    comment TEXT,
    mode VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 投稿に紐づく質問テーブル
CREATE TABLE post_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    question_text VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id)
);

-- 回答テーブル
CREATE TABLE answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    answer_value TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id)
);