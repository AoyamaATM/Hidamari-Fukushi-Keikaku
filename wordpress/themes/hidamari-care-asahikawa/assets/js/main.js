(() => {
  "use strict";

  const body = document.body;

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

  initMobileNavigation();
  initFaqAccordions();
})();
