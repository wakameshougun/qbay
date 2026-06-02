<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>QBay</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<h1>
  <a href="../index.html">
    <img src="../assets/images/title.png" alt="title" style="width: 10%; height: auto;">
  </a>
</h1>

<p class="large-text">投稿</p>

<!-- ★ここからフォーム開始 -->
<form id="post-form" method="POST" action="../php/test2.php">

  <p>お名前：
    <input type="text" id="name" name="name" size="30" maxlength="20">
  </p>

  <p>カテゴリー：
    <select id="category" name="category">
      <option value="" selected disabled hidden>選択してください</option>
      <option value="1">生活</option>
      <option value="2">健康、美容</option>
      <option value="3">ファッション</option>
      <option value="4">音楽</option>
      <option value="5">趣味</option>
      <option value="6">恋愛</option>
      <option value="7">動物</option>
      <option value="8">仕事</option>
      <option value="9">学問</option>
      <option value="10">政治</option>
      <option value="11">スポーツ</option>
      <option value="12">芸能人</option>
      <option value="13">テレビ</option>
      <option value="14">食事、グルメ</option>
      <option value="15">その他</option>
    </select>
  </p>

  <p>内容：</p>
  <textarea id="comment" name="comment" cols="60" rows="8" maxlength="100"></textarea>

  <p>回答を選択肢式かコメント式にするかを選択</p>
  <label for="mode-select">入力方法を選択:</label>
  <select id="mode-select" name="mode" onchange="handleModeChange()">
    <option value="" selected disabled hidden>選択してください</option>
    <option value="select">選択肢式</option>
    <option value="other">コメント式</option>
  </select>

  <!-- 質問数セレクトボックス（選択式のときだけ表示） -->
  <div id="question-count-container" style="display: none;">
    <label for="question-count">選択肢数:</label>
    <select id="question-count" onchange="generateTextBoxes()">
      <option value="" selected disabled hidden>選択してください</option>
      <option value="2">2個</option>
      <option value="3">3個</option>
      <option value="4">4個</option>
      <option value="5">5個</option>
      <option value="6">6個</option>
      <option value="7">7個</option>
      <option value="8">8個</option>
    </select>
  </div>

  <!-- 動的に生成される選択肢入力欄 -->
  <div id="textbox-container"></div>

  <!-- 警告表示エリア -->
  <div id="warning-area" style="color:red;"></div>

  <br>
  <button type="submit">投稿</button>
</form>
<!-- ★ここでフォーム終了 -->

<script>
function handleModeChange() {
  const mode = document.getElementById('mode-select').value;
  const questionCountContainer = document.getElementById('question-count-container');
  const textboxContainer = document.getElementById('textbox-container');

  // 初期化
  questionCountContainer.style.display = 'none';
  textboxContainer.innerHTML = '';

  // 「選択肢式」が選ばれた場合のみ表示
  if (mode === 'select') {
    questionCountContainer.style.display = 'block';
  }
}

function generateTextBoxes() {
  const count = parseInt(document.getElementById('question-count').value, 10);
  const container = document.getElementById('textbox-container');
  container.innerHTML = ''; // 前回の内容をクリア

  if (isNaN(count)) return;

  for (let i = 1; i <= count; i++) {
    const label = document.createElement('label');
    label.textContent = `${i}. `;
    label.style.marginRight = '8px';

    const input = document.createElement('input');
    input.type = 'text';
    input.name = `question${i}`;
    input.className = "question-input";
    input.style.marginBottom = '8px';

    container.appendChild(label);
    container.appendChild(input);
    container.appendChild(document.createElement('br'));
  }
}

// ✅ 未記入チェック（名前・内容・カテゴリー・入力方法・選択肢）
document.getElementById("post-form").addEventListener("submit", function(e) {
  const name = document.getElementById("name").value.trim();
  const comment = document.getElementById("comment").value.trim();
  const category = document.getElementById("category").value;
  const mode = document.getElementById("mode-select").value;
  const warningArea = document.getElementById("warning-area");
  warningArea.innerHTML = "";

  let errorMsg = "";

  if (!name) {
    errorMsg += "⚠️ 名前は必須です。\n";
  }
  if (!category) {
    errorMsg += "⚠️ カテゴリーは必須です。\n";
  }
  if (!comment) {
    errorMsg += "⚠️ 内容は必須です。\n";
  }
  if (!mode) {
    errorMsg += "⚠️ 入力方法は必須です。\n";
  }

  // 選択肢式の場合は選択肢数と内容も必須
  if (mode === "select") {
    const count = document.getElementById("question-count").value;
    if (!count) {
      errorMsg += "⚠️ 選択肢数を選んでください。\n";
    } else {
      const inputs = document.querySelectorAll(".question-input");
      inputs.forEach((input, index) => {
        if (!input.value.trim()) {
          errorMsg += `⚠️ 選択肢 ${index + 1} を入力してください。\n`;
        }
      });
    }
  }

  if (errorMsg) {
    e.preventDefault(); // 送信を止める
    warningArea.textContent = errorMsg;
  }
});
</script>

</body>
</html>