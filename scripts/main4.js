document.addEventListener("DOMContentLoaded", () => {
  const container = document.getElementById("answer-container");

  // URLから id を取得
  const params = new URLSearchParams(window.location.search);
  const id = params.get("id");

  console.log("URL:", window.location.href);
  console.log("取得したid:", id);

  if (!id) {
    container.innerHTML = "<p>投稿が指定されていません。</p>";
    return;
  }

  // DBから投稿を取得
  fetch(`../php/api.php?id=${encodeURIComponent(id)}`)
    .then(async res => {
      const text = await res.text();
      const contentType = res.headers.get("content-type");
      if (!res.ok) throw new Error(`HTTPエラー: ${res.status}`);
      if (!contentType || !contentType.includes("application/json")) {
        throw new Error("JSON以外のレスポンス: " + text);
      }
      return JSON.parse(text);
    })
    .then(post => {
      console.log("APIレスポンス:", post);

      if (post.error !== undefined) {
        container.innerHTML = `<p>${post.error}</p>`;
        return;
      }

      // 投稿情報の表示
      const block = document.createElement("div");
      block.className = "post-info";
      block.innerHTML = `
        <div class="name">名前: ${post.name || "未入力"}</div>
        <div class="category">カテゴリ: ${post.category || "未入力"}</div>
        <div class="comment">コメント: ${post.comment || "未入力"}</div>
      `;
      container.appendChild(block);

      // 回答UIの生成
      const ui = document.createElement("div");
      ui.className = "answer-container";

      const isChoice = Array.isArray(post.questions) && post.questions.length > 0;
      let submitBtn;

      if (isChoice) {
        ui.innerHTML = `<p>選択肢から回答してください。</p>`;
        post.questions.forEach(q => {
          const label = document.createElement("label");
          label.innerHTML = `<input type="radio" name="answer" value="${q}"> ${q}`;
          ui.appendChild(label);
        });
      } else {
        ui.innerHTML = `
          <p>コメントを入力してください。</p>
          <textarea id="text-answer" rows="4" cols="50" placeholder="コメントを入力..."></textarea>
        `;
      }

      const buttonArea = document.createElement("div");
      buttonArea.className = "button-area";

      submitBtn = document.createElement("button");
      submitBtn.id = "style3-submit";
      submitBtn.type = "button";
      submitBtn.textContent = "送信";

      buttonArea.appendChild(submitBtn);
      ui.appendChild(buttonArea);
      container.appendChild(ui);

      // 回答送信処理
      submitBtn.addEventListener("click", () => {
        let answer = "";

        if (isChoice) {
          const selected = document.querySelector('input[name="answer"]:checked');
          answer = selected ? selected.value : "";
        } else {
          const textarea = document.getElementById("text-answer");
          answer = textarea ? textarea.value.trim() : "";
        }

        if (!answer) {
          let warning = document.querySelector(".warning");
          if (!warning) {
            warning = document.createElement("p");
            warning.className = "warning";
            warning.style.color = "red";
            warning.textContent = "⚠️ 回答を入力してください。";
            ui.appendChild(warning);
          }
          return;
        }

        if (!post.post_id) {
          alert("post_idが取得できていません。");
          console.error("post.post_id is missing:", post);
          return;
        }

        console.log("送信するpost_id:", post.post_id);

        fetch("../php/ans.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({
            post_id: post.post_id, 
            answer: answer,
            created_at: new Date().toISOString()
          })
        })
          .then(async res => {
            const contentType = res.headers.get("content-type") || "";
            if (!res.ok) throw new Error(`HTTPエラー: ${res.status}`);
            return contentType.includes("application/json") ? res.json() : Promise.reject("JSON以外のレスポンス");
          })
          .then(result => {
            if (result.status === "ok") {
              alert("回答を保存しました。");
              window.location.href = "../index.html";
            } else {
              alert("保存に失敗しました: " + (result.error || ""));
            }
          })
          .catch(err => {
            console.error("送信エラー:", err);
            alert("通信エラー: " + err.message);
          });
      });
    })
    .catch(err => {
      console.error("取得エラー:", err);
      container.innerHTML = "<p>データ取得に失敗しました。</p>";
    });
});