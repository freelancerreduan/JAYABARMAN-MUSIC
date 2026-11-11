# License system — build recipe (Smart BD Host)

Give this file to Claude Code in any new Laravel project and say **"add the license system from this doc."**
It is a complete spec of the system built into the Jaya Barman Music project, so the same system can be
rebuilt the same way. Written in plain terms; the exact code is small and lives in the files listed below.

---

## 1. What it does (in one minute)

- Every page of the site is **closed** until the copy is (a) licensed for its domain and (b) its database
  is set up. Until then, any URL you open shows the setup card in place.
- **Live website (real domain):** needs a **license key**. The site asks the panel
  `lisence.smartbdhost.com`; the panel sends back a **signed** answer. The site keeps the answer and keeps
  working even if the panel is later offline. Only a real signed answer can lock or unlock a live site.
- **Developer machine (localhost / 127.0.0.1):** the panel is **never** called. You unlock it by typing the
  **developer name** (`reduan360bd`). This never unlocks a real domain.
- **One domain only.** The saved answer names the domain. Copy the site to another domain and it asks for a
  license again (the saved answer no longer matches the domain).
- After the license, a **connection page** asks for the database details (cPanel › MySQL) and the **admin
  login** to create, then saves everything to `.env` and creates the tables if the database is empty.
- **No route to find.** The license/setup pages are not routes — a global middleware draws them in place on
  whatever URL was opened, and the forms post back to the same URL. There is nothing like `/install` or
  `/license` to look up later.
- The saved license state is **encrypted and split into 3 files** in different folders, each with a checksum
  of the whole. Editing, deleting or copying one piece without the others makes the whole thing read as
  "nothing saved", so it is hard to find and hard to move.

---

## 2. How the panel talks to the site

The panel holds an **RSA private key**. The site holds only the matching **public key** (in
`config/license.php`). Everything the panel says is a JSON payload signed with the private key, sent as two
base64 strings: `p` = base64 of the JSON, `g` = base64 of the signature (RSA, SHA-256). The site verifies
`g` over `p` with the public key, so an answer cannot be faked or edited.

### 2a. Activation / re-check (site → panel), a "pull"
```
POST  https://lisence.smartbdhost.com/api/v1/sync
form: k = <license key>          (upper-cased)
      d = <domain>               e.g. jayabarman.com  (no www, no port)
      n = <random nonce, 32 chars>
→ 200 JSON: { "p": "<base64 json>", "g": "<base64 signature>" }
```
The site accepts the answer only when, after verifying the signature, **all** of these match:
`payload.n == n` (the answer is for this exact question), `payload.d == d`,
`payload.k == sha256(key)`. This stops a replay of some other answer.

### 2b. Push (panel → site), so the panel can change a live site at any time
```
POST  https://<the site>/license/push
form: p = <base64 json>, g = <base64 signature>
→ { "ok": true }                         accepted
→ { "ok": false, "error": "<reason>" }   422
```
The site accepts a push only when: signature valid, `payload.d` == this domain, the site is already
activated, the message is **fresh** (`payload.t` within ±1 hour of now), the message id `payload.n` was not
seen before (last 20 kept), and it is **not older** than the saved answer. This is the one real route in the
system (a machine-to-machine endpoint, not a page) — it is never gated by the license middleware.

### 2c. The signed payload (fields the panel puts in the JSON)
| field | meaning |
|---|---|
| `s`  | status: `active`, `not_started` (both = running), `expired`, `suspended`, `domain` (key is for another domain), `invalid` (unknown key) |
| `d`  | domain the answer is for |
| `k`  | `sha256(key)` — binds the answer to the key (pull answers) |
| `n`  | echoes the request nonce (pull) / unique message id (push) |
| `t`  | ISO time the panel signed it (freshness + "which is newer") |
| `name`, `email`, `phone` | license holder, shown on the expired / suspended page |
| `price` | renewal fee, shown on the expired page |
| `to` | expiry date, shown in the "expired on …" line |

> The site **never trusts its own clock to unlock** — only a signed answer does. The clock is only used to
> decide when to ask again and to reject stale pushes.

---

## 3. The files (what to create)

All paths are under the Laravel project root.

| File | Role |
|---|---|
| `config/license.php` | panel URL, **public key**, recheck hours, timeout, `local_hosts`, **`developer_name`**, **`pepper`**, vendor contact. |
| `app/Support/Vault.php` | saves/reads the license state **encrypted (AES-256-GCM) and split into 3 files** with a shared checksum. |
| `app/Support/License.php` | the brain: `valid()`, `activate($key)` (live), `activateDeveloper($name)` (local), `recheckIfDue()`, `acceptPush()`, `verify()`, domain helper. |
| `app/Support/Installer.php` | first-run DB setup: test connection, create tables if empty, create admin, write `.env`, hidden "installed" marker (domain-bound). |
| `app/Http/Middleware/RequireLicense.php` | **global** middleware. Draws the license + database pages in place (no route), handles their posts with a double-submit cookie token, and re-checks the panel in `terminate()`. |
| `app/Http/Controllers/LicensePushController.php` | the `/license/push` endpoint. |
| `resources/views/install/layout.blade.php` | the dark card shell for both setup pages. |
| `resources/views/install/license.blade.php` | developer-name field (local) **or** key field + vendor box (live), uses `$error` + `$token`. |
| `resources/views/install/database.blade.php` | DB details + admin login, uses `$error` + `$token`. |

### Where the 3 hidden pieces live (must survive `artisan optimize:clear`)
```
storage/app/.sysconf/core.dat
storage/framework/.license/ns.dat
bootstrap/cache/.sysmeta
```
Each file = base64 of `index(1 byte) + total(1 byte) + stamp(8 bytes) + its slice of the ciphertext`.
`stamp` = first 8 bytes of `sha256(whole ciphertext)`. On read: all three must be present, carry the same
`stamp`, and the re-joined ciphertext must match `stamp`, or it reads as empty. The encryption key is
`sha256(APP_KEY + '|' + pepper)` — so the pieces are useless without this project's `APP_KEY`.

The "installed" marker is a separate hidden file `storage/app/.sysconf/boot.dat` (base64 JSON naming the
domain). It only decides whether the DB page is shown; it holds nothing secret.

---

## 4. Wiring (3 edits)

1. **`bootstrap/app.php`**
   - At the top, make sure these folders exist (a fresh copy may not have them):
     `storage/app/.sysconf`, `storage/framework/.license`, the usual `storage/framework/*`, `bootstrap/cache`.
   - `->withRouting(..., then: fn () => Route::post('license/push', LicensePushController::class)->middleware('throttle:30,1')->name('license.push'))`
   - In `->withMiddleware(...)`: `$middleware->prepend(RequireLicense::class);`  ← **global**, runs before routing, so there is no route to the setup pages.
2. **`app/Providers/AppServiceProvider.php`** → in `register()`: `Installer::prepareRuntime();`
   (creates `APP_KEY` if missing; uses **file** session + cache until the DB is set up).
3. **`.gitignore`** → add the three piece folders and `bootstrap/cache/.sysmeta` so a clone starts unlicensed.

---

## 5. Per-project changes (the only things that differ between projects)

- `config/license.php` → set **`developer_name`** (default `reduan360bd`) and a unique **`pepper`** string.
  Leave `server` and the **public key** as-is so the **same panel controls the new project** too.
- `app/Support/Installer.php` → in `hasTables()` and the `$fresh` check, use a **table that this project
  always has** (here it is `settings`). Change it to the new project's core table.
- Seeder → read `ADMIN_EMAIL` / `ADMIN_PASSWORD` from the environment and create that admin when the DB is
  empty (the setup page passes them through). Keep any built-in developer login you want for yourself.
- Everything else is copy-paste.

---

## 6. The flow a buyer sees

1. Upload the files to the live domain. Open the site → **Activate your license** (key field + your contact).
2. Enter the key → the panel verifies it for that domain → **Connect the database** page.
3. Enter cPanel MySQL details + a new admin email/password → tables are created (if empty) and `.env` saved →
   sent to **/admin/login**.
4. From the panel you can later **renew, expire or suspend** the site; the change reaches it by push, or on the
   next background re-check (every 12 h). An expired/suspended site shows the holder's details and a renew button.

On your own machine (localhost) you skip all of that: open the site → **Developer access** → type `reduan360bd`
→ straight to the database page.

---

## 7. Panel side (for reference — already set up at lisence.smartbdhost.com)

The panel is managed from the Smart BD Host cPanel. It must, per registered site:
- answer `POST /api/v1/sync` with the signed payload described in §2a/§2c;
- let you set status (active / expired / suspended), expiry date, holder name/email/phone and renewal price;
- optionally **push** a change to the live site at `https://<domain>/license/push` (§2b).

Keep the panel's **private** key only on the panel. The site needs only the **public** key, which is already
in `config/license.php`. To use a brand-new key pair, replace the public key there and keep the private one on
the panel.
