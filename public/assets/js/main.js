/* Jaya Barman – site scripts (no build step) */
(function () {
    'use strict';

    /* ---------- Lightbox: images, local videos, YouTube ---------- */
    const lbEl = document.getElementById('lightbox');
    if (lbEl && window.bootstrap) {
        const modal = new bootstrap.Modal(lbEl);
        const body = lbEl.querySelector('.lb-body');
        const caption = lbEl.querySelector('.lb-caption');
        const prev = lbEl.querySelector('.lb-prev');
        const next = lbEl.querySelector('.lb-next');
        let items = [];
        let index = 0;

        function render() {
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
            caption.textContent = title;
            const many = items.length > 1;
            prev.style.display = many ? '' : 'none';
            next.style.display = many ? '' : 'none';
        }

        document.addEventListener('click', function (e) {
            const trigger = e.target.closest('[data-lightbox]');
            if (!trigger) return;
            e.preventDefault();
            const group = trigger.closest('[data-gallery]');
            // only items that are visible (respects gallery pagination)
            items = group
                ? Array.from(group.querySelectorAll('[data-lightbox]')).filter(function (n) { return n.offsetParent !== null; })
                : [trigger];
            index = Math.max(0, items.indexOf(trigger));
            render();
            modal.show();
        });

        prev.addEventListener('click', function () { index = (index - 1 + items.length) % items.length; render(); });
        next.addEventListener('click', function () { index = (index + 1) % items.length; render(); });

        document.addEventListener('keydown', function (e) {
            if (!lbEl.classList.contains('show') || items.length < 2) return;
            if (e.key === 'ArrowLeft') prev.click();
            if (e.key === 'ArrowRight') next.click();
        });

        lbEl.addEventListener('hidden.bs.modal', function () { body.innerHTML = ''; });
    }

    /* ---------- Gallery pagination (Portfolio) ---------- */
    document.querySelectorAll('[data-per-page]').forEach(function (grid) {
        const per = parseInt(grid.dataset.perPage, 10) || 8;
        const tiles = Array.from(grid.children);
        const pages = Math.ceil(tiles.length / per);
        const pager = grid.parentElement.querySelector('.gallery-pager');
        if (!pager) return;
        if (pages < 2) { pager.remove(); return; }

        let current = 1;
        function show(p) {
            current = p;
            tiles.forEach(function (t, i) { t.hidden = Math.floor(i / per) + 1 !== p; });
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
    });

    /* ---------- Album players (Music) ---------- */
    const audio = new Audio();
    audio.preload = 'none';
    let activePlayer = null;

    function fmt(s) {
        if (!isFinite(s)) return '00:00';
        const m = Math.floor(s / 60), r = Math.floor(s % 60);
        return String(m).padStart(2, '0') + ':' + String(r).padStart(2, '0');
    }

    document.querySelectorAll('.player').forEach(function (player) {
        const tracks = Array.from(player.querySelectorAll('.player-list li'));
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
            const src = tracks[t].dataset.src;
            if (!audio.src.endsWith(src)) audio.src = src;
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
    });

    audio.addEventListener('timeupdate', function () { if (activePlayer) activePlayer._update(); });
    audio.addEventListener('ended', function () { if (activePlayer) activePlayer._ended(); });

    /* ---------- Blog search (Lights The Life) ---------- */
    const search = document.querySelector('.search-form');
    if (search) {
        search.addEventListener('submit', function (e) {
            e.preventDefault();
            const q = search.querySelector('input').value.trim().toLowerCase();
            let found = 0;
            document.querySelectorAll('.post').forEach(function (p) {
                const match = !q || p.textContent.toLowerCase().includes(q);
                p.hidden = !match;
                if (match) found++;
            });
            document.querySelector('.no-results').style.display = found ? 'none' : 'block';
        });
    }

    /* ---------- Contact form: opens the visitor's mail app ---------- */
    const form = document.querySelector('.contact-form');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!form.checkValidity()) { form.classList.add('was-validated'); return; }
            const d = new FormData(form);
            const subject = (d.get('subject') || 'Message') + ' – from ' + d.get('name');
            const text = d.get('message') + '\n\n' + d.get('name') + '\n' + d.get('email');
            window.location.href = 'mailto:' + form.dataset.to +
                '?subject=' + encodeURIComponent(subject) + '&body=' + encodeURIComponent(text);
            form.querySelector('.form-note').textContent = 'Your mail app is opening with the message ready to send.';
        });
    }
})();
