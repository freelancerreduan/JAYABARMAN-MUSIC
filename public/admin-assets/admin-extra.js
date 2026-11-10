// Jaya Barman admin: drag-and-drop order, upload boxes, video source switch, text editor
(function () {
  "use strict";

  const csrf = document.querySelector('meta[name="csrf-token"]').content;
  const sortUrl = document.querySelector('meta[name="sort-url"]').content;
  const toast = document.getElementById("saveToast");
  let toastTimer;

  function showToast(text, isError) {
    if (!toast) return;
    toast.querySelector("span").textContent = text;
    toast.classList.toggle("error", !!isError);
    toast.classList.add("show");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove("show"), 2200);
  }

  // ---------- Drag and drop order ----------
  // <div data-sortable="gallery"> with children <div data-id="5">. Saved right away.
  function saveOrder(list) {
    const ids = [...list.children].filter((el) => el.dataset.id).map((el) => el.dataset.id);
    const body = { type: list.dataset.sortable, ids: ids };
    if (list.dataset.side) body.side = list.dataset.side;
    // keep the "1, 2, 3" numbers on cards in step
    [...list.children].filter((el) => el.dataset.id).forEach((el, i) => {
      const no = el.querySelector("[data-no]");
      if (no) no.textContent = i + 1;
    });
    if (!ids.length) return Promise.resolve();
    return fetch(sortUrl, {
      method: "POST",
      headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrf, Accept: "application/json" },
      body: JSON.stringify(body),
    }).then((r) => {
      if (!r.ok) throw new Error();
    });
  }

  if (window.Sortable) {
    document.querySelectorAll("[data-sortable]").forEach((list) => {
      Sortable.create(list, {
        animation: 160,
        handle: list.dataset.handle || null,
        draggable: "[data-id]",
        group: list.dataset.group || undefined,
        ghostClass: "sort-ghost",
        chosenClass: "sort-chosen",
        delay: 120,
        delayOnTouchOnly: true,
        filter: "input, textarea, select, .no-drag",
        preventOnFilter: false,
        onEnd: (e) => {
          const lists = e.from === e.to ? [e.to] : [e.from, e.to];
          Promise.all(lists.map(saveOrder))
            .then(() => showToast("Order saved"))
            .catch(() => showToast("Could not save the order. Reload and try again.", true));
          lists.forEach((l) => l.closest("[data-empty-wrap]")?.classList.toggle("is-empty", !l.querySelector("[data-id]")));
        },
      });
    });
  }

  // ---------- Upload boxes: drag files in or click; shows what was picked ----------
  document.querySelectorAll(".dropzone").forEach((zone) => {
    const input = zone.querySelector("input[type=file]");
    const list = zone.parentElement.querySelector(".drop-picked");
    ["dragenter", "dragover"].forEach((ev) => zone.addEventListener(ev, () => zone.classList.add("over")));
    ["dragleave", "drop"].forEach((ev) => zone.addEventListener(ev, () => zone.classList.remove("over")));

    input.addEventListener("change", () => {
      if (!list) return;
      list.innerHTML = "";
      const files = [...input.files];
      zone.classList.toggle("has-files", files.length > 0);
      zone.querySelector(".dz-count").textContent = files.length
        ? files.length + (files.length === 1 ? " file chosen" : " files chosen")
        : "";
      files.forEach((f) => {
        const item = document.createElement("div");
        item.className = "picked";
        if (f.type.startsWith("image/")) {
          const img = document.createElement("img");
          img.src = URL.createObjectURL(f);
          item.appendChild(img);
        } else {
          item.innerHTML = '<i class="bi ' + (f.type.startsWith("video/") ? "bi-film" : "bi-music-note") + '"></i>';
          const name = document.createElement("span");
          name.textContent = f.name;
          item.appendChild(name);
          item.classList.add("file");
        }
        list.appendChild(item);
      });
    });
  });

  // ---------- Show the button only when a form has new files ----------
  document.querySelectorAll("form[data-busy]").forEach((form) =>
    form.addEventListener("submit", () => {
      const btn = form.querySelector("[type=submit]") || (form.id && document.querySelector('[type=submit][form="' + form.id + '"]'));
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> ' + (form.dataset.busy || "Saving…");
      }
    })
  );

  // ---------- Video source: YouTube link or uploaded file ----------
  document.querySelectorAll("[data-type-switch]").forEach((box) => {
    const panels = box.querySelectorAll("[data-when]");
    const pick = (v) => panels.forEach((p) => { p.hidden = p.dataset.when !== v; });
    box.querySelectorAll("input[type=radio]").forEach((r) => {
      r.addEventListener("change", () => pick(r.value));
      if (r.checked) pick(r.value);
    });
  });

  // ---------- Simple text editor (bold, italic, link, lists) ----------
  if (window.Quill) {
    document.querySelectorAll("[data-editor]").forEach((el) => {
      const field = document.getElementById(el.dataset.editor);
      const q = new Quill(el, {
        theme: "snow",
        modules: { toolbar: [["bold", "italic", "underline"], ["link"], [{ list: "ordered" }, { list: "bullet" }], ["clean"]] },
        placeholder: el.dataset.placeholder || "",
      });
      el.closest("form").addEventListener("submit", () => {
        const html = q.root.innerHTML;
        field.value = html === "<p><br></p>" ? "" : html;
      });
    });
  }
})();
