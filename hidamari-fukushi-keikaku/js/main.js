const body = document.body;
const root = body.dataset.root || "./";
const page = body.dataset.page || "home";

const routes = {
  home: `${root}index.html`,
  aboutus: `${root}about_us.html`,
  price: `${root}price.html`,
  faq: `${root}faq.html`,
  contact: `${root}contact.html`,
  privacy: `${root}privacy.html`,
  facilities: `${root}facilities.html`,
  post: `${root}single.html`,
  archive: `${root}archive.html`,
};

const routeLink = (key, label, href = routes[key]) =>
  `<a href="${href}" data-nav="${key}">${label}</a>`;

// ここでヘッダーとフッターのHTMLを生成している。
// WordPressのテーマ開発では、header.phpとfooter.phpに相当する部分になる。
const brand = `
  <a class="brand" href="${routes.home}" aria-label="ひだまり福祉計画 TOP">
    <img class="brand-logo" src="${root}img/Logo.png" alt="ひだまりケア旭川">
  </a>
`;

const headerHost = document.querySelector("[data-site-header]");

if (headerHost) {
  headerHost.className = "site-header";
  headerHost.innerHTML = `
    <a class="skip-link" href="#main">本文へ移動</a>
    <div class="header-inner">
      ${brand}
      <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">
        <span class="nav-toggle-lines" aria-hidden="true"></span>
        <span class="visually-hidden">メニュー</span>
      </button>
      <nav class="site-nav" id="site-nav" aria-label="グローバルナビゲーション">
        ${routeLink("aboutus", "施設紹介")}
        ${routeLink("flow", "ご利用の流れ", `${routes.aboutus}#flow`)}
        ${routeLink("price", "料金表")}
        ${routeLink("facilities", "全施設一覧")}
        ${routeLink("faq", "よくあるご質問")}
        ${routeLink("contact", "お問い合わせ")}
      </nav>
    </div>
  `;
}

const footerHost = document.querySelector("[data-site-footer]");

if (footerHost) {
  footerHost.className = "footer";
  footerHost.innerHTML = `
    <div class="content-width grid-footer">
      <div>
        ${brand}
        <p class="footer-address">
          社会福祉法人 ひだまり福祉計画<br>
          ひだまりケア旭川<br>
          北海道旭川市 旭町2条7丁目 12-77<br>
          Tel 0166-xx-yyyy
        </p>
      </div>
      <nav class="footer-nav" aria-label="フッターナビゲーション">
        ${routeLink("aboutus", "施設紹介")}
        ${routeLink("home", "ご利用の流れ", `${routes.home}#flow`)}
        ${routeLink("price", "料金表")}
        ${routeLink("facilities", "全施設紹介")}
        ${routeLink("archive", "お知らせ一覧")}
        ${routeLink("faq", "よくあるご質問")}
        ${routeLink("contact", "お問い合わせ")}
        ${routeLink("privacy", "プライバシーポリシー")}
      </nav>
    </div>
    <p class="copyright">&copy; 社会福祉法人 ひだまり福祉計画 <span data-year></span>-</p>
  `;
}

// ここから下は、ページ共通のインタラクションを実装している。
// 年を自動で更新する処理。フッターのコピーライト部分にある。
document.querySelectorAll("[data-year]").forEach((node) => {
  node.textContent = new Date().getFullYear();
});

// 現在のページに対応するナビゲーションリンクにaria-current="page"を設定する処理。
const currentNav = page === "privacy" ? "contact" : page;
document.querySelectorAll(`[data-nav="${currentNav}"]`).forEach((link) => {
  link.setAttribute("aria-current", "page");
});

// ナビゲーションのトグル処理。モバイルでナビゲーションを開閉するためのもの。
const navToggle = document.querySelector(".nav-toggle");

navToggle?.addEventListener("click", () => {
  const expanded = navToggle.getAttribute("aria-expanded") === "true";
  navToggle.setAttribute("aria-expanded", String(!expanded));
  body.classList.toggle("nav-open", !expanded);
});

// FAQのアコーディオン処理。FAQページで質問をクリックすると回答が表示されるようにするためのもの。
document.querySelectorAll(".faq-question").forEach((button) => {
  button.addEventListener("click", () => {
    const answer = document.getElementById(button.getAttribute("aria-controls"));
    const expanded = button.getAttribute("aria-expanded") === "true";

    button.setAttribute("aria-expanded", String(!expanded));

    if (answer) {
      answer.hidden = expanded;
    }
  });
});


// お知らせ一覧の絞り込み機能
const archiveForm = document.querySelector("[data-archive-filter]");

if (archiveForm) {
  const category = archiveForm.querySelector("[name='category']");
  const month = archiveForm.querySelector("[name='month']");
  const result = document.querySelector("[data-archive-result]");
  const rows = [...document.querySelectorAll("[data-archive-item]")];

  const updateArchive = () => {
    let visible = 0;

    rows.forEach((row) => {
      const categoryMatch = category.value === "all" || row.dataset.category === category.value;
      const monthMatch = month.value === "all" || row.dataset.month === month.value;
      const show = categoryMatch && monthMatch;

      row.hidden = !show;

      if (show) {
        visible += 1;
      }
    });

    if (result) {
      result.textContent = `${visible}件を表示しています`;
    }
  };

  category.addEventListener("change", updateArchive);
  month.addEventListener("change", updateArchive);
  updateArchive();
}

// お問い合わせフォームの送信処理
// ここでは、実際の送信処理は実装せず、送信内容の確認メッセージを表示するだけにしている。
// CF7でやる
document.querySelectorAll("[data-contact-form]").forEach((form) => {
  form.addEventListener("submit", (event) => {
    event.preventDefault();

    if (!form.reportValidity()) {
      return;
    }

    const status = form.querySelector("[data-form-status]");

    if (status) {
      status.textContent = "送信内容を確認しました。実運用時に送信先処理を接続してください。";
    }

    form.reset();
  });
});
