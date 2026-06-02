document.addEventListener("DOMContentLoaded", () => {
  const tbody = document.querySelector("#post-table tbody");
  const searchInput = document.getElementById("search-input");
  const categorySelect = document.getElementById("category-select");

  let posts = [];

  // カテゴリ変換マップ
  const categoryMap = {
    "1": "生活",
    "2": "健康、美容",
    "3": "ファッション",
    "4": "音楽",
    "5": "趣味",
    "6": "恋愛",
    "7": "動物",
    "8": "仕事",
    "9": "学問",
    "10": "政治",
    "11": "スポーツ",
    "12": "芸能人",
    "13": "テレビ",
    "14": "食事、グルメ",
    "15": "その他"
  };

  // DBから投稿一覧を取得
  fetch("../php/test3.php")
    .then(async res => {
      const text = await res.text();
      const contentType = res.headers.get("content-type");
      if (!res.ok) {
        throw new Error(`HTTPエラー: ${res.status}`);
      }
      if (!contentType || !contentType.includes("application/json")) {
        throw new Error("JSON以外のレスポンス: " + text);
      }
      return JSON.parse(text);
    })
    .then(data => {
      // カテゴリ番号を名詞に変換
      posts = data.map(post => ({
        ...post,
        category: categoryMap[post.category] || post.category
      }));
      // ✅ 最新20件を新しい順のまま表示
      renderPosts(posts.slice(0, 20));
      populateCategories(posts);
    })
    .catch(err => {
      console.error("取得エラー:", err);
      tbody.innerHTML = "<tr><td colspan='5'>データ取得に失敗しました</td></tr>";
    });

  // カテゴリ選択肢を動的に追加
  function populateCategories(data) {
    const categories = [...new Set(data.map(p => p.category).filter(cat => cat && cat.trim() !== ""))];
    categories.forEach(cat => {
      const option = document.createElement("option");
      option.value = cat;
      option.textContent = cat;
      categorySelect.appendChild(option);
    });
  }

// テーブル描画
function renderPosts(filteredPosts) {
  tbody.innerHTML = "";
  filteredPosts.forEach(post => {
    const comment = post.comment || "";
    const name = post.name || "";
    const category = post.category || "";
    const type = post.mode === "select" ? "選択肢式" : "コメント式";
    const count = post.answer_count || 0;

    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${comment}</td>
      <td>${name}</td>
      <td>${category}</td>
      <td>${type}</td>
      <td>${count}</td>
    `;
    // ✅ クリックで view2.html に飛ぶよう修正
    tr.addEventListener("click", () => {
      window.location.href = `view2.html?id=${post.id}`;
    });
    tbody.appendChild(tr);
  });
}
  // 検索・カテゴリ絞り込み
  function filterPosts() {
    const keyword = searchInput.value.trim().toLowerCase();
    const selectedCategory = categorySelect.value;

    const filtered = posts.filter(post => {
      const matchKeyword =
        keyword === "" ||
        (post.comment && post.comment.toLowerCase().includes(keyword)) ||
        (post.name && post.name.toLowerCase().includes(keyword));
      const matchCategory = selectedCategory === "" || post.category === selectedCategory;
      return matchKeyword && matchCategory;
    });

    // ✅ 絞り込み結果も新しい順のまま表示
    renderPosts(filtered.slice(0, 40));
  }

  searchInput.addEventListener("input", filterPosts);
  categorySelect.addEventListener("change", filterPosts);
});