/* Jaya Barman – site scripts.
   Pages change without a full reload (Turbo): document-level listeners are added once,
   page parts (galleries, players) are set up again on every "turbo:load". */
(function () {
    'use strict';

    if (window.Turbo) Turbo.setProgressBarDelay(80);

    /* ---------- Lightbox: images, local videos, YouTube ---------- */
    let items = [];
    let index = 0;

    function lb() { return document.getElementById('lightbox'); }

    function render() {
        const box = lb();
        const body = box.querySelector('.lb-body');
        const el = items[index];
        const type = el.dataset.type || 'image';
        const src = el.dataset.src;
        const title = el.dataset.title || '';
        body.innerHTML = '';

        if (type === 'video') {
            const v = document.createElement('video');
            v.src = src;
            v.controls = true;
            v.autoplay = true;
            v.playsInline = true;
            body.appendChild(v);
        } else if (type === 'youtube') {
            const f = document.createElement('iframe');
            f.src = 'https://www.youtube-nocookie.com/embed/' + src + '?autoplay=1&rel=0';
            f.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
            f.allowFullscreen = true;
            f.title = title || 'Video';
            body.appendChild(f);
        } else {
            const img = document.createElement('img');
            img.src = src;
            img.alt = title;
            body.appendChild(img);
        }
        box.querySelector('.lb-caption').textContent = title;
        const many = items.length > 1;
        box.querySelector('.lb-prev').style.display = many ? '' : 'none';
        box.querySelector('.lb-next').style.display = many ? '' : 'none';
    }

    function step(d) { index = (index + d + items.length) % items.length; render(); }

    document.addEventListener('click', function (e) {
        if (e.target.closest('.lb-prev')) return step(-1);
        if (e.target.closest('.lb-next')) return step(1);

        const trigger = e.target.closest('[data-lightbox]');
        if (!trigger || !lb() || !window.bootstrap) return;
        e.preventDefault();
        const group = trigger.closest('[data-gallery]');
        // only items that are visible (respects gallery pagination)
        items = group
            ? Array.from(group.querySelectorAll('[data-lightbox]')).filter(function (n) { return n.offsetParent !== null; })
            : [trigger];
        index = Math.max(0, items.indexOf(trigger));
        render();
        bootstrap.Modal.getOrCreateInstance(lb()).show();
    });

    document.addEventListener('keydown', function (e) {
        const box = lb();
        if (!box || !box.classList.contains('show') || items.length < 2) return;
        if (e.key === 'ArrowLeft') step(-1);
        if (e.key === 'ArrowRight') step(1);
    });

    document.addEventListener('hidden.bs.modal', function (e) {
        if (e.target.id === 'lightbox') e.target.querySelector('.lb-body').innerHTML = '';
    });

    /* ---------- Album players (one shared audio for the whole site) ---------- */
    const audio = new Audio();
    audio.preload = 'none';
    let activePlayer = null;

    function fmt(s) {
        if (!isFinite(s)) return '00:00';
        const m = Math.floor(s / 60), r = Math.floor(s % 60);
        return String(m).padStart(2, '0') + ':' + String(r).padStart(2, '0');
    }

    audio.addEventListener('timeupdate', function () { if (activePlayer && activePlayer._update) activePlayer._update(); });
    audio.addEventListener('ended', function () { if (activePlayer && activePlayer._ended) activePlayer._ended(); });

    function initPlayer(player) {
        const tracks = Array.from(player.querySelectorAll('.player-list li'));
        if (!tracks.length) return;
        const playBtn = player.querySelector('.player-play');
        const nameEl = player.querySelector('.player-track .name');
        const timeEl = player.querySelector('.player-track .time');
        const bar = player.querySelector('.player-progress');
        const fill = bar.querySelector('span');
        const prevBtn = player.querySelector('[data-act="prev"]');
        const nextBtn = player.querySelector('[data-act="next"]');
        let t = 0;

        function setTrack(i, autoplay) {
            t = i;
            tracks.forEach(function (li, n) { li.classList.toggle('current', n === i); });
            nameEl.textContent = (i + 1) + '. ' + tracks[i].dataset.title;
            timeEl.textContent = '00:00 / ' + (tracks[i].dataset.length || '--:--');
            fill.style.width = '0';
            prevBtn.disabled = i === 0;
            nextBtn.disabled = i === tracks.length - 1;
            if (autoplay) play();
        }

        function play() {
            if (activePlayer && activePlayer !== player) activePlayer.dispatchEvent(new Event('stopplayer'));
            activePlayer = player;
            const src = new URL(tracks[t].dataset.src, location.href).href;
            if (audio.src !== src) audio.src = src;
            audio.play();
            playBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
        }

        function pause() {
            audio.pause();
            playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
        }

        player.addEventListener('stopplayer', function () {
            playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
            fill.style.width = '0';
        });

        playBtn.addEventListener('click', function () {
            if (activePlayer === player && !audio.paused) pause(); else play();
        });
        prevBtn.addEventListener('click', function () { if (t > 0) setTrack(t - 1, activePlayer === player && !audio.paused); });
        nextBtn.addEventListener('click', function () { if (t < tracks.length - 1) setTrack(t + 1, activePlayer === player && !audio.paused); });
        tracks.forEach(function (li, i) { li.addEventListener('click', function () { setTrack(i, true); }); });
        player.querySelector('.player-album').addEventListener('click', function () { player.classList.toggle('open'); });

        bar.addEventListener('click', function (e) {
            if (activePlayer !== player || !audio.duration) return;
            const r = bar.getBoundingClientRect();
            audio.currentTime = ((e.clientX - r.left) / r.width) * audio.duration;
        });

        player._update = function () {
            fill.style.width = (audio.currentTime / audio.duration * 100 || 0) + '%';
            timeEl.textContent = fmt(audio.currentTime) + ' / ' + fmt(audio.duration);
        };
        player._ended = function () {
            if (t < tracks.length - 1) setTrack(t + 1, true); else pause();
        };

        setTrack(0, false);
    }

    /* ---------- Gallery pagination (Portfolio) ---------- */
    function initPager(grid) {
        const per = parseInt(grid.dataset.perPage, 10) || 8;
        const tiles = Array.from(grid.children);
        const pages = Math.ceil(tiles.length / per);
        const pager = grid.parentElement.querySelector('.gallery-pager');
        if (!pager) return;
        if (pages < 2) { pager.innerHTML = ''; return; }

        function show(p) {
            tiles.forEach(function (tile, i) { tile.hidden = Math.floor(i / per) + 1 !== p; });
            pager.innerHTML = '';
            if (p > 1) addBtn('Previous', p - 1);
            for (let i = 1; i <= pages; i++) addBtn(String(i), i, i === p);
            if (p < pages) addBtn('Next', p + 1);
        }
        function addBtn(label, target, active) {
            const b = document.createElement('button');
            b.type = 'button';
            b.textContent = label;
            if (active) { b.className = 'active'; b.setAttribute('aria-current', 'page'); }
            b.addEventListener('click', function () { show(target); });
            pager.appendChild(b);
        }
        show(1);
    }

    /* ---------- Video tiles load their preview only when they come on screen ---------- */
    const lazy = 'IntersectionObserver' in window ? new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
            if (!en.isIntersecting) return;
            const v = en.target;
            lazy.unobserve(v);
            v.preload = 'metadata';
            v.src = v.dataset.src;
        });
    }, { rootMargin: '200px' }) : null;

    /* ---------- Run on every page ---------- */
    function init() {
        document.querySelectorAll('[data-per-page]').forEach(initPager);
        document.querySelectorAll('.player').forEach(initPlayer);
        document.querySelectorAll('video[data-src]').forEach(function (v) {
            if (v.getAttribute('src')) return;
            if (lazy) lazy.observe(v); else { v.preload = 'metadata'; v.src = v.dataset.src; }
        });
    }

    if (window.Turbo) {
        document.addEventListener('turbo:load', init);
        // leave the page tidy: stop sound, close the viewer and the phone menu
        document.addEventListener('turbo:before-visit', function () {
            audio.pause();
            activePlayer = null;
        });
        document.addEventListener('turbo:before-cache', function () {
            const box = lb();
            if (box && window.bootstrap) {
                const m = bootstrap.Modal.getInstance(box);
                if (m) m.dispose();
                box.classList.remove('show');
                box.style.display = '';
                box.querySelector('.lb-body').innerHTML = '';
            }
            document.querySelectorAll('.modal-backdrop').forEach(function (b) { b.remove(); });
            document.body.classList.remove('modal-open');
            document.body.removeAttribute('style');
            const nav = document.getElementById('mainNav');
            if (nav) nav.classList.remove('show');
        });
    } else if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
