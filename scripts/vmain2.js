document.addEventListener("DOMContentLoaded", async () => {
  const info = document.getElementById("result-info");
  const answerArea = document.getElementById("answer-area");

  const params = new URLSearchParams(window.location.search);
  const id = params.get("id");

  if (!id || !info || !answerArea) return;

  try {
    const res = await fetch(`../php/view2.php?id=${encodeURIComponent(id)}`, {
      cache: "no-store"
    });
    const data = await res.json();

    if (data.error || !data.post) {
      info.textContent = "データが見つかりません。";
      return;
    }

    const post = data.post;
    const answers = (data.answers || []).map(a => a.trim());

    // ✅ カテゴリ変換マップ
    const categoryMap = {
      1: "生活",
      2: "健康、美容",
      3: "ファッション",
      4: "音楽",
      5: "趣味",
      6: "恋愛",
      7: "動物",
      8: "仕事",
      9: "学問",
      10: "政治",
      11: "スポーツ",
      12: "芸能人",
      13: "テレビ",
      14: "食事、グルメ",
      15: "その他"
    };
    const categoryText = categoryMap[post.category] || "（未設定）";

    const isChoice = post.mode === "select" && Array.isArray(post.questions) && post.questions.length > 0;

    // ✅ 投稿情報表示
    info.innerHTML = `
      <div>名前：${post.name || "（未設定）"}</div>
      <div>カテゴリ：${categoryText}</div>
      <div>内容：${post.comment || "（なし）"}</div>
      <div>回答数：${answers.length}</div>
    `;

    answerArea.innerHTML = "";

    if (isChoice) {
      // ✅ 選択肢集計
      const counts = {};
      post.questions.forEach(q => { counts[q] = 0; });
      answers.forEach(ans => { if (counts.hasOwnProperty(ans)) counts[ans]++; });

      const entries = Object.entries(counts);
      const total = entries.reduce((sum, [, c]) => sum + c, 0);

      // ✅ 表の描画
      const table = document.createElement("table");
      table.className = "survey-table";
      table.innerHTML = `
        <thead><tr><th>選択肢</th><th>回答</th></tr></thead>
        <tbody>
          ${entries.map(([label, count]) => `<tr><td>${label}</td><td>${count}</td></tr>`).join("")}
        </tbody>
      `;
      answerArea.appendChild(table);

      // ✅ グラフ描画
      const canvas = document.createElement("canvas");
      canvas.id = "result-chart";
      canvas.width = 1000;
      canvas.height = 600;
      answerArea.appendChild(canvas);

      const ctx = canvas.getContext("2d");
      const cx = canvas.width / 2;
      const cy = canvas.height / 2;
      const radius = 180;
      const innerLabelRad = 120;
      const outerLabelRad = 210;
      const leaderRad = 195;
      const labelPadding = 12;
      const minInsideAngle = Math.PI / 6;
      const colors = ["#4a90e2", "#f5a623", "#7ed321", "#d0021b", "#9013fe", "#50e3c2"];

      ctx.font = "16px sans-serif";
      ctx.textBaseline = "middle";

      let startAngle = 0;
      const slices = entries.map(([label, count], i) => {
        const ratio = total > 0 ? count / total : 0;
        return { label, count, ratio, color: colors[i % colors.length] };
      });

      // 円グラフ描画
      slices.forEach(sl => {
        if (sl.ratio <= 0) return;
        const sliceAngle = sl.ratio * 2 * Math.PI;
        ctx.beginPath();
        ctx.moveTo(cx, cy);
        ctx.arc(cx, cy, radius, startAngle, startAngle + sliceAngle);
        ctx.closePath();
        ctx.fillStyle = sl.color;
        ctx.fill();
        ctx.strokeStyle = "#333";
        ctx.lineWidth = 2;
        ctx.stroke();
        sl.midAngle = startAngle + sliceAngle / 2;
        sl.sliceAngle = sliceAngle;
        startAngle += sliceAngle;
      });

      // ラベル描画
      slices.forEach(sl => {
        const percentText = total > 0 ? `${Math.round(sl.ratio * 100)}%` : "0%";
        const text = `${sl.label} ${percentText}`;
        const metrics = ctx.measureText(text);
        const textWidth = metrics.width + labelPadding;
        const forceOutside = sl.ratio <= 0;
        const arcLenInside = sl.sliceAngle * innerLabelRad;
        const canPlaceInside = !forceOutside && sl.sliceAngle >= minInsideAngle && textWidth <= arcLenInside;

        if (canPlaceInside) {
          const x = cx + Math.cos(sl.midAngle) * innerLabelRad;
          const y = cy + Math.sin(sl.midAngle) * innerLabelRad;
          ctx.fillStyle = "#000";
          ctx.textAlign = "center";
          ctx.fillText(text, x, y);
        } else {
          const ax = cx + Math.cos(sl.midAngle) * leaderRad;
          const ay = cy + Math.sin(sl.midAngle) * leaderRad;
          const lx = cx + Math.cos(sl.midAngle) * outerLabelRad;
          const ly = cy + Math.sin(sl.midAngle) * outerLabelRad;
          ctx.strokeStyle = sl.color;
          ctx.lineWidth = 1.5;
          ctx.beginPath();
          ctx.moveTo(ax, ay);
          ctx.lineTo(lx, ly);
          const horizLen = 16;
          const isLeft = lx < cx;
          const hx = isLeft ? lx - horizLen : lx + horizLen;
          ctx.lineTo(hx, ly);
          ctx.stroke();
          ctx.fillStyle = "#000";
          ctx.textAlign = isLeft ? "right" : "left";
          ctx.fillText(text, hx, ly);
        }
      });
    } else {
      // ✅ コメント式リスト表示
      const ul = document.createElement("ul");
      ul.id = "comment-list";
      answers.forEach(ans => {
        const li = document.createElement("li");
        li.textContent = ans;
        ul.appendChild(li);
      });
      answerArea.appendChild(ul);
    }
  } catch (e) {
    console.error("通信エラー:", e);
    info.textContent = "サーバー通信エラー。";
  }
});