// Jaya Barman — admin panel scripts
(function () {
  "use strict";

  const root = document.documentElement;

  // Dark / light theme, remembered per browser
  document.querySelectorAll("[data-theme-toggle]").forEach((btn) =>
    btn.addEventListener("click", () => {
      const next = root.dataset.theme === "dark" ? "light" : "dark";
      root.dataset.theme = next;
      try { localStorage.setItem("admin-theme", next); } catch (e) {}
      document.dispatchEvent(new CustomEvent("themechange"));
    })
  );

  // Mobile sidebar
  document.querySelectorAll("[data-sidebar-toggle]").forEach((el) =>
    el.addEventListener("click", () => document.body.classList.toggle("sidebar-open"))
  );

  // Sidebar groups
  document.querySelectorAll(".side-group > .side-link").forEach((btn) =>
    btn.addEventListener("click", () => btn.parentElement.classList.toggle("open"))
  );

  // Dropdowns (bell, user menu)
  document.addEventListener("click", (e) => {
    const toggle = e.target.closest("[data-dropdown]");
    document.querySelectorAll(".dropdown.open").forEach((d) => {
      if (!toggle || d !== toggle.parentElement) d.classList.remove("open");
    });
    if (toggle) toggle.parentElement.classList.toggle("open");
  });

  // Ask before deleting
  document.addEventListener("submit", (e) => {
    const msg = e.target.dataset.confirm;
    if (msg && !window.confirm(msg)) e.preventDefault();
  });

  // Live preview for image fields (file chosen or path typed)
  document.querySelectorAll(".media-field").forEach((field) => {
    const preview = field.querySelector(".media-preview");
    const file = field.querySelector("input[type=file]");
    const path = field.querySelector("input[type=text]");
    const base = field.dataset.base || "/";
    const show = (url) => {
      preview.style.backgroundImage = url ? `url("${url}")` : "";
      preview.classList.toggle("has-img", !!url);
    };
    file?.addEventListener("change", () => {
      const f = file.files[0];
      if (f && f.type.startsWith("image/")) show(URL.createObjectURL(f));
    });
    path?.addEventListener("input", () => {
      const v = path.value.trim();
      if (!field.dataset.video) show(v ? (/^(https?:)?\/\//.test(v) ? v : base + v.replace(/^\//, "")) : "");
    });
  });

  // Video source: upload a file OR paste a link. Only the chosen panel is shown and sent.
  document.querySelectorAll("[data-video-source]").forEach((box) => {
    const panels = box.querySelectorAll(".source-panel");
    const pick = (mode) => panels.forEach((p) => {
      const on = p.dataset.mode === mode;
      p.hidden = !on;
      p.querySelectorAll("input").forEach((i) => { i.disabled = !on; });
    });
    box.querySelectorAll("input[type=radio]").forEach((r) => r.addEventListener("change", () => pick(r.value)));
  });

  // Repeatable rows (CV blocks)
  document.querySelectorAll("[data-repeat]").forEach((wrap) => {
    const tpl = document.getElementById(wrap.dataset.repeat);
    let i = wrap.querySelectorAll(".repeat-item").length + 100;
    document.querySelector(`[data-repeat-add="${wrap.dataset.repeat}"]`)?.addEventListener("click", () => {
      wrap.insertAdjacentHTML("beforeend", tpl.innerHTML.replaceAll("__i__", i++));
    });
    wrap.addEventListener("click", (e) => {
      if (e.target.closest("[data-repeat-remove]")) e.target.closest(".repeat-item").remove();
    });
  });

  // Label every table cell with its column name (phones show rows as cards)
  document.querySelectorAll("table.table").forEach((table) => {
    const heads = [...table.querySelectorAll("thead th")].map((th) => th.textContent.trim());
    table.querySelectorAll("tbody tr").forEach((tr) =>
      [...tr.children].forEach((td, i) => { if (!td.hasAttribute("data-label")) td.dataset.label = heads[i] || ""; })
    );
  });

  // Close the mobile menu after picking a page
  document.querySelectorAll(".side-nav a").forEach((a) =>
    a.addEventListener("click", () => document.body.classList.remove("sidebar-open"))
  );

  // Flash messages fade out
  document.querySelectorAll(".flash[data-autohide]").forEach((el) =>
    setTimeout(() => { el.style.transition = "opacity .4s"; el.style.opacity = "0"; setTimeout(() => el.remove(), 400); }, 4000)
  );

  // "/" focuses the search box
  document.addEventListener("keydown", (e) => {
    if (e.key === "/" && !/input|textarea|select/i.test(document.activeElement.tagName)) {
      const s = document.querySelector(".search input");
      if (s) { e.preventDefault(); s.focus(); }
    }
  });
})();
