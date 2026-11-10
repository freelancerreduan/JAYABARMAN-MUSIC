// Fast local server on port 3000.
//   node dev-server.cjs           (PORT=3000 by default)
// Files in public/ (images, videos, css, js, uploads) are sent directly by Node:
// many at once, with video seeking (Range) and browser caching.
// Every other request (the pages, the admin) goes to "php artisan serve" on PORT+1.
// "php artisan serve" alone handles one request at a time and sends whole videos,
// so one video could block the whole site for seconds.

const http = require("http");
const fs = require("fs");
const path = require("path");
const { spawn } = require("child_process");

const PORT = parseInt(process.env.PORT || "3000", 10);
const PHP_PORT = PORT + 1;
const ROOT = path.join(__dirname, "public");

const TYPES = {
  ".css": "text/css; charset=utf-8", ".js": "text/javascript; charset=utf-8", ".json": "application/json",
  ".svg": "image/svg+xml", ".png": "image/png", ".jpg": "image/jpeg", ".jpeg": "image/jpeg", ".gif": "image/gif",
  ".webp": "image/webp", ".ico": "image/x-icon", ".avif": "image/avif",
  ".mp4": "video/mp4", ".webm": "video/webm", ".ogg": "audio/ogg", ".ogv": "video/ogg", ".mov": "video/quicktime",
  ".mp3": "audio/mpeg", ".m4a": "audio/mp4", ".wav": "audio/wav", ".aac": "audio/aac", ".flac": "audio/flac", ".oga": "audio/ogg",
  ".woff": "font/woff", ".woff2": "font/woff2", ".ttf": "font/ttf", ".txt": "text/plain; charset=utf-8", ".pdf": "application/pdf",
};

// ---------- Laravel behind us ----------
const php = spawn("php", ["artisan", "serve", "--host=127.0.0.1", `--port=${PHP_PORT}`], { cwd: __dirname, stdio: ["ignore", "pipe", "pipe"] });
php.stdout.on("data", (d) => process.stdout.write(`[php] ${d}`));
php.stderr.on("data", (d) => process.stderr.write(`[php] ${d}`));
php.on("exit", (code) => { console.log(`[php] stopped (${code})`); process.exit(code || 0); });
const stop = () => { php.kill(); process.exit(0); };
process.on("SIGINT", stop);
process.on("SIGTERM", stop);

// ---------- Static files ----------
function staticFile(req, res) {
  let rel;
  try { rel = decodeURIComponent(new URL(req.url, "http://x").pathname); } catch (e) { return false; }
  if (rel === "/" || rel.endsWith(".php") || rel.includes("\0")) return false;
  const file = path.normalize(path.join(ROOT, rel));
  if (!file.startsWith(ROOT + path.sep)) return false;

  let stat;
  try { stat = fs.statSync(file); } catch (e) { return false; }
  if (!stat.isFile()) return false;

  const type = TYPES[path.extname(file).toLowerCase()] || "application/octet-stream";
  const headers = {
    "Content-Type": type,
    "Accept-Ranges": "bytes",
    "Last-Modified": stat.mtime.toUTCString(),
    "Cache-Control": "public, max-age=604800",
  };

  if (req.headers["if-modified-since"] && new Date(req.headers["if-modified-since"]) >= new Date(stat.mtime.toUTCString())) {
    res.writeHead(304, headers);
    res.end();
    return true;
  }

  const range = /^bytes=(\d*)-(\d*)$/.exec(req.headers.range || "");
  if (range) {
    let start = range[1] === "" ? stat.size - parseInt(range[2], 10) : parseInt(range[1], 10);
    let end = range[1] !== "" && range[2] !== "" ? parseInt(range[2], 10) : stat.size - 1;
    if (isNaN(start) || start < 0) start = 0;
    end = Math.min(end, stat.size - 1);
    if (start > end) {
      res.writeHead(416, { "Content-Range": `bytes */${stat.size}` });
      res.end();
      return true;
    }
    res.writeHead(206, { ...headers, "Content-Range": `bytes ${start}-${end}/${stat.size}`, "Content-Length": end - start + 1 });
    if (req.method === "HEAD") return res.end(), true;
    fs.createReadStream(file, { start, end }).pipe(res);
    return true;
  }

  res.writeHead(200, { ...headers, "Content-Length": stat.size });
  if (req.method === "HEAD") return res.end(), true;
  fs.createReadStream(file).pipe(res);
  return true;
}

// ---------- Everything else goes to Laravel ----------
function proxy(req, res, tries = 0) {
  const up = http.request(
    { host: "127.0.0.1", port: PHP_PORT, method: req.method, path: req.url, headers: req.headers },
    (r) => { res.writeHead(r.statusCode, r.headers); r.pipe(res); }
  );
  up.on("error", () => {
    // Laravel may still be starting
    if (tries < 20 && !res.headersSent && (req.method === "GET" || req.method === "HEAD")) {
      return setTimeout(() => proxy(req, res, tries + 1), 250);
    }
    if (!res.headersSent) res.writeHead(502, { "Content-Type": "text/plain" });
    res.end("Laravel is not running.");
  });
  req.pipe(up);
}

http
  .createServer((req, res) => {
    if ((req.method === "GET" || req.method === "HEAD") && staticFile(req, res)) return;
    proxy(req, res);
  })
  .listen(PORT, "127.0.0.1", () => console.log(`Jaya Barman site: http://localhost:${PORT}  (admin: http://localhost:${PORT}/admin)`));
