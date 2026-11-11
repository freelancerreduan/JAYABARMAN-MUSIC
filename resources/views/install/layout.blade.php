<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>@yield('title') · Website setup</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    :root {
      --bg: #1c1b21; --bg-2: #24232a; --bg-3: #2e2c35; --line: rgba(255,255,255,.08);
      --text: #ecebf1; --muted: #a7a4b2; --teal: #1fd1b8; --violet: #9a5cf5; --pink: #e8509d;
      --grad: linear-gradient(90deg, var(--teal), var(--violet));
    }
    * { box-sizing: border-box; }
    body {
      margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 32px 16px;
      background: radial-gradient(circle at 15% 10%, rgba(31,209,184,.12), transparent 40%),
                  radial-gradient(circle at 85% 90%, rgba(154,92,245,.16), transparent 45%), var(--bg);
      color: var(--text); font: 300 15px/1.7 "Poppins", system-ui, sans-serif;
    }
    .card { width: 100%; max-width: 560px; background: var(--bg-2); border: 1px solid var(--line); border-radius: 20px; overflow: hidden; box-shadow: 0 30px 70px -30px rgba(0,0,0,.8); }
    .bar { height: 6px; background: var(--grad); }
    .body { padding: 34px 34px 30px; }
    .steps { display: flex; gap: 8px; margin-bottom: 22px; font-size: 12px; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); }
    .steps span { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 30px; border: 1px solid var(--line); }
    .steps span.on { color: #fff; border-color: transparent; background: var(--grad); }
    .steps span.done { color: var(--teal); border-color: rgba(31,209,184,.4); }
    h1 { margin: 0 0 6px; font-size: 26px; font-weight: 600; color: #fff; line-height: 1.25; }
    h1 .ic { display: inline-grid; place-items: center; width: 42px; height: 42px; margin-right: 10px; border-radius: 12px; background: var(--grad); font-size: 19px; vertical-align: -9px; }
    p.lead { margin: 0 0 22px; color: var(--muted); }
    label { display: block; margin: 0 0 6px; font-size: 12px; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); }
    .field { margin-bottom: 14px; }
    .row { display: grid; grid-template-columns: 1fr 120px; gap: 12px; }
    input {
      width: 100%; padding: 12px 16px; border-radius: 12px; border: 1px solid var(--line);
      background: var(--bg-3); color: #fff; font: inherit; font-weight: 400;
    }
    input:focus { outline: none; border-color: var(--teal); box-shadow: 0 0 0 3px rgba(31,209,184,.15); }
    input.key { font-family: ui-monospace, Consolas, monospace; letter-spacing: .06em; text-transform: uppercase; }
    .btn {
      display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%;
      padding: 13px 20px; border: 0; border-radius: 50px; cursor: pointer; font: 500 14px "Poppins", sans-serif;
      letter-spacing: .06em; text-transform: uppercase; color: #fff; background: var(--grad);
      box-shadow: 0 10px 25px -10px rgba(154,92,245,.7);
    }
    .btn.ghost { background: transparent; border: 1px solid var(--line); box-shadow: none; color: var(--muted); text-transform: none; letter-spacing: 0; }
    .btn:disabled { opacity: .6; cursor: wait; }
    .error, .ok { display: flex; gap: 10px; padding: 12px 14px; border-radius: 12px; margin-bottom: 18px; font-size: 14px; }
    .error { background: rgba(232,80,157,.12); border: 1px solid rgba(232,80,157,.4); color: #ffc2dc; }
    .ok { background: rgba(31,209,184,.12); border: 1px solid rgba(31,209,184,.35); color: #c9fbf2; }
    .box { padding: 16px 18px; border-radius: 14px; background: rgba(255,255,255,.03); border: 1px solid var(--line); margin-bottom: 20px; }
    .box h3 { margin: 0 0 10px; font-size: 13px; letter-spacing: .1em; text-transform: uppercase; color: var(--teal); font-weight: 500; }
    .info { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }
    .info li { display: flex; gap: 10px; align-items: center; font-size: 14px; word-break: break-word; }
    .info i { width: 30px; height: 30px; flex: none; display: grid; place-items: center; border-radius: 9px; background: rgba(255,255,255,.05); color: var(--teal); }
    .info b { color: #fff; font-weight: 500; }
    .price { font-size: 22px; font-weight: 600; color: #fff; }
    .hint { margin-top: 6px; font-size: 12.5px; color: var(--muted); }
    .sep { margin: 22px 0 16px; padding-top: 18px; border-top: 1px dashed var(--line); }
    .foot { margin-top: 18px; text-align: center; font-size: 12.5px; color: var(--muted); }
    .domain { font-family: ui-monospace, Consolas, monospace; color: #fff; background: var(--bg-3); padding: 2px 8px; border-radius: 6px; }
    @media (max-width: 520px) { .body { padding: 26px 18px 22px; } h1 { font-size: 22px; } .row { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <main class="card">
    <div class="bar"></div>
    <div class="body">
      @yield('content')
    </div>
  </main>
  <script>
    // Stop double submits while the server works (activation / database setup can take a few seconds)
    document.querySelectorAll("form[data-busy]").forEach((f) => f.addEventListener("submit", () => {
      const b = f.querySelector("button[type=submit]");
      if (b) { b.disabled = true; b.innerHTML = '<i class="bi bi-hourglass-split"></i> ' + f.dataset.busy; }
    }));
  </script>
</body>
</html>
