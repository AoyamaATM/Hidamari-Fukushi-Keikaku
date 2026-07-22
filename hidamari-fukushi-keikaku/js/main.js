const body = document.body;
const root = body.dataset.root || "./";
const page = body.dataset.type || "home";

const routes = Object.freeze({
  home: `${root}index.html`,
  aboutus: `${root}about_us.html`,
  price: `${root}price.html`,
  faq: `${root}faq.html`,
  contact: `${root}contact.html`,
  privacy: `${root}privacy.html`,
  facilities: `${root}facilities.html`,
  post: `${root}single.html`,
  archive: `${root}archive.html`,
});

const navigationItems = Object.freeze({
  header: [
    { key: "aboutus", label: "施設紹介" },
    { key: "flow", label: "ご利用の流れ", href: `${routes.aboutus}#flow__about` },
    { key: "price", label: "料金表" },
    { key: "facilities", label: "全施設一覧" },
    { key: "faq", label: "よくあるご質問" },
    { key: "contact", label: "お問い合わせ" },
  ],
  footer: [
    { key: "aboutus", label: "施設紹介" },
    { key: "home", label: "ご利用の流れ", href: `${routes.home}#flow` },
    { key: "price", label: "料金表" },
    { key: "facilities", label: "全施設紹介" },
    { key: "archive", label: "お知らせ一覧" },
    { key: "faq", label: "よくあるご質問" },
    { key: "contact", label: "お問い合わせ" },
    { key: "privacy", label: "プライバシーポリシー" },
  ],
});

const currentNavigationByPage = Object.freeze({
  post: "archive",
  privacy: "contact",
});

const renderRouteLink = ({ key, label, href = routes[key] }) =>
  `<a href="${href}" data-nav="${key}">${label}</a>`;

const renderNavigationLinks = (items) => items.map(renderRouteLink).join("");

const renderBrand = () => `
  <a class="brand" href="${routes.home}" aria-label="ひだまり福祉計画 TOP">
    <img class="brand-logo" src="${root}img/Logo.png" alt="ひだまりケア旭川">
  </a>
`;

const renderHeader = () => {
  const host = document.querySelector("[data-site-header]");

  if (!host) {
    return;
  }

  host.classList.add("site-header");
  host.innerHTML = `
    <a class="skip-link" href="#main-content">本文へ移動</a>
    <div class="header-inner">
      ${renderBrand()}
      <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">
        <span class="nav-toggle-lines" aria-hidden="true"></span>
        <span class="visually-hidden">メニュー</span>
      </button>
      <nav class="site-nav" id="site-nav" aria-label="グローバルナビゲーション">
        ${renderNavigationLinks(navigationItems.header)}
      </nav>
    </div>
  `;
};

const renderFooter = () => {
  const host = document.querySelector("[data-site-footer]");

  if (!host) {
    return;
  }

  host.classList.add("footer");
  host.innerHTML = `
    <div class="content-width grid-footer">
      <div>
        ${renderBrand()}
        <p class="footer-address">
          社会福祉法人 ひだまり福祉計画<br>
          ひだまりケア旭川<br>
          北海道旭川市 旭町2条7丁目 12-77<br>
          Tel 0166-xx-yyyy
        </p>
      </div>
      <nav class="footer-nav" aria-label="フッターナビゲーション">
        ${renderNavigationLinks(navigationItems.footer)}
      </nav>
    </div>
    <p class="copyright">&copy; 社会福祉法人 ひだまり福祉計画 <span data-year></span></p>
  `;
};

const updateCurrentYear = () => {
  const currentYear = new Date().getFullYear();

  document.querySelectorAll("[data-year]").forEach((node) => {
    node.textContent = currentYear;
  });
};

const updateCurrentNavigation = () => {
  const currentNavigation = currentNavigationByPage[page] || page;

  document.querySelectorAll(`[data-nav="${currentNavigation}"]`).forEach((link) => {
    link.setAttribute("aria-current", "page");
  });
};

const initMobileNavigation = () => {
  const toggle = document.querySelector(".nav-toggle");

  if (!toggle) {
    return;
  }

  const desktopNavigation = window.matchMedia("(min-width: 1025px)");
  const setNavigationState = (isOpen) => {
    toggle.setAttribute("aria-expanded", String(isOpen));
    body.classList.toggle("nav-open", isOpen);
  };

  toggle.addEventListener("click", () => {
    const isOpen = toggle.getAttribute("aria-expanded") === "true";
    setNavigationState(!isOpen);
  });

  document.addEventListener("keydown", (event) => {
    const isOpen = toggle.getAttribute("aria-expanded") === "true";

    if (event.key !== "Escape" || !isOpen) {
      return;
    }

    setNavigationState(false);
    toggle.focus();
  });

  desktopNavigation.addEventListener("change", ({ matches }) => {
    if (matches) {
      setNavigationState(false);
    }
  });
};

const initFaqAccordions = () => {
  document.querySelectorAll(".faq-question").forEach((button) => {
    button.addEventListener("click", () => {
      const answerId = button.getAttribute("aria-controls");
      const answer = answerId ? document.getElementById(answerId) : null;
      const isExpanded = button.getAttribute("aria-expanded") === "true";

      button.setAttribute("aria-expanded", String(!isExpanded));

      if (answer) {
        answer.hidden = isExpanded;
      }
    });
  });
};

const initArchiveFilter = () => {
  const form = document.querySelector("[data-archive-filter]");

  if (!form) {
    return;
  }

  const category = form.querySelector("[name='category']");
  const month = form.querySelector("[name='month']");
  const result = document.querySelector("[data-archive-result]");
  const rows = [...document.querySelectorAll("[data-archive-item]")];

  if (!category || !month) {
    return;
  }

  const updateArchive = () => {
    let visibleCount = 0;

    rows.forEach((row) => {
      const matchesCategory = category.value === "all" || row.dataset.category === category.value;
      const matchesMonth = month.value === "all" || row.dataset.month === month.value;
      const isVisible = matchesCategory && matchesMonth;

      row.hidden = !isVisible;
      visibleCount += Number(isVisible);
    });

    if (result) {
      result.textContent = `${visibleCount}件を表示しています`;
    }
  };

  category.addEventListener("change", updateArchive);
  month.addEventListener("change", updateArchive);
  updateArchive();
};

const initContactForms = () => {
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
};

const initSite = () => {
  renderHeader();
  renderFooter();
  updateCurrentYear();
  updateCurrentNavigation();
  initMobileNavigation();
  initFaqAccordions();
  initArchiveFilter();
  initContactForms();
};

initSite();
