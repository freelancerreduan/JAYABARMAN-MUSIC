// Jaya Barman admin: drag-and-drop order, upload boxes, video source switch, text editor.
// Everything that belongs to one page is set up again after each Turbo page change.
(function () {
  "use strict";

  let toastTimer;

  function showToast(text, isError) {
    const toast = document.getElementById("saveToast");
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
    return fetch(document.querySelector('meta[name="sort-url"]').content, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
        Accept: "application/json",
      },
      body: JSON.stringify(body),
    }).then((r) => {
      if (!r.ok) throw new Error();
    });
  }

  function initSortable() {
    if (!window.Sortable) return;
    document.querySelectorAll("[data-sortable]").forEach((list) => {
      if (Sortable.get(list)) return;
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
          lists.forEach((l) => l.classList.toggle("is-empty", !l.querySelector("[data-id]")));
        },
      });
    });
  }

  // ---------- Upload boxes: drag files in or click; shows what was picked ----------
  function initDropzones() {
    document.querySelectorAll(".dropzone:not([data-ready])").forEach((zone) => {
      zone.dataset.ready = "1";
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
  }

  // ---------- Video source: YouTube link or uploaded file ----------
  function initTypeSwitch() {
    document.querySelectorAll("[data-type-switch]").forEach((box) => {
      const panels = box.querySelectorAll("[data-when]");
      const pick = (v) => panels.forEach((p) => { p.hidden = p.dataset.when !== v; });
      box.querySelectorAll("input[type=radio]").forEach((r) => {
        r.addEventListener("change", () => pick(r.value));
        if (r.checked) pick(r.value);
      });
    });
  }

  // ---------- Simple text editor (bold, italic, link, lists) ----------
  function initEditors() {
    if (!window.Quill) return;
    document.querySelectorAll("[data-editor]:not([data-ready])").forEach((el) => {
      el.dataset.ready = "1";
      const field = document.getElementById(el.dataset.editor);
      const q = new Quill(el, {
        theme: "snow",
        modules: { toolbar: [["bold", "italic", "underline"], ["link"], [{ list: "ordered" }, { list: "bullet" }], ["clean"]] },
        placeholder: el.dataset.placeholder || "",
      });
      // copy the editor text into the form field before it is sent
      el.closest("form").addEventListener("submit", () => {
        const html = q.root.innerHTML;
        field.value = html === "<p><br></p>" ? "" : html;
      }, true);
    });
  }

  // ---------- Buttons show "Saving…" while a form is being sent ----------
  function busy(form) {
    const btn = form.querySelector("[type=submit]") || (form.id && document.querySelector('[type=submit][form="' + form.id + '"]'));
    if (btn && form.dataset.busy) {
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> ' + form.dataset.busy;
    }
  }
  if (window.Turbo) document.addEventListener("turbo:submit-start", (e) => busy(e.target));
  else document.addEventListener("submit", (e) => busy(e.target));

  function init() {
    initSortable();
    initDropzones();
    initTypeSwitch();
    initEditors();
  }

  if (window.Turbo) document.addEventListener("turbo:load", init);
  else document.addEventListener("DOMContentLoaded", init);
})();

// ---------- Album page: one small play button per song (one song plays at a time) ----------
(function () {
  "use strict";
  let audio = null;
  let current = null;

  function stop() {
    if (audio) audio.pause();
    if (current) {
      current.classList.remove("playing");
      current.querySelector("i").className = "bi bi-play-fill";
    }
    current = null;
  }

  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".song-play");
    if (!btn) return;
    if (btn === current) return stop();
    stop();
    audio = audio || new Audio();
    audio.src = btn.dataset.song;
    audio.play().catch(() => stop());
    audio.onended = stop;
    current = btn;
    btn.classList.add("playing");
    btn.querySelector("i").className = "bi bi-pause-fill";
  });

  document.addEventListener("turbo:before-visit", stop);
})();
