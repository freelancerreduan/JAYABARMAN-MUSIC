// Jaya Barman — admin panel base scripts.
// Pages load with Turbo (no full reload): clicks are handled on the document once,
// so they keep working after every page change.
(function () {
  "use strict";

  const root = document.documentElement;
  if (window.Turbo) Turbo.setProgressBarDelay(80);

  document.addEventListener("click", (e) => {
    // Dark / light theme, remembered per browser
    if (e.target.closest("[data-theme-toggle]")) {
      const next = root.dataset.theme === "dark" ? "light" : "dark";
      root.dataset.theme = next;
      try { localStorage.setItem("admin-theme", next); } catch (err) {}
      return;
    }

    // Mobile sidebar
    if (e.target.closest("[data-sidebar-toggle]")) {
      document.body.classList.toggle("sidebar-open");
      return;
    }

    // Dropdowns (user menu)
    const toggle = e.target.closest("[data-dropdown]");
    document.querySelectorAll(".dropdown.open").forEach((d) => {
      if (!toggle || d !== toggle.parentElement) d.classList.remove("open");
    });
    if (toggle) toggle.parentElement.classList.toggle("open");
  });

  // "/" focuses the search box (if a page has one)
  document.addEventListener("keydown", (e) => {
    if (e.key === "/" && !/input|textarea|select/i.test(document.activeElement.tagName)) {
      const s = document.querySelector(".search input");
      if (s) { e.preventDefault(); s.focus(); }
    }
  });

  function init() {
    // keep the saved theme after a page change
    try { root.dataset.theme = localStorage.getItem("admin-theme") === "dark" ? "dark" : "light"; } catch (err) {}

    // Flash messages fade out
    document.querySelectorAll(".flash[data-autohide]").forEach((el) =>
      setTimeout(() => { el.style.transition = "opacity .4s"; el.style.opacity = "0"; setTimeout(() => el.remove(), 400); }, 4000)
    );
  }

  if (window.Turbo) document.addEventListener("turbo:load", init);
  else document.addEventListener("DOMContentLoaded", init);
})();
