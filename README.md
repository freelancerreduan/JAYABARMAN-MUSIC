# Jaya Barman – website with admin panel

Laravel 13 + Bootstrap 5.3. The website looks the same as the static version (`jaiyabarman Updated 2026`), and everything on it is edited from the admin panel.

## Run on port 3000 (fast)
```
node dev-server.cjs
```
Opens http://localhost:3000 (admin: /admin). Node sends photos and videos itself (many at once, video seeking) and passes the pages to `php artisan serve` on port 3001. Pages and admin forms load with Turbo, so the page changes without a full reload and shows a thin loading bar.

## Open on Laragon
- Website: http://localhost/jayabarman-music/public/
- Admin: http://localhost/jayabarman-music/public/admin
- After Laragon "Reload" (Menu → Apache → Reload), also: http://jayabarman-music.test

## Admin logins
| User | Email | First password |
|---|---|---|
| Developer (testing) | developer@gmail.com | same as the email |
| Client | admin@gmail.com | same as the email |

Each user changes their own email and password in **Footer links & login**.

## What the admin can change
| Page | In the admin |
|---|---|
| Home | Top photo, name, small line, title, text (editor with bold, links, lists) |
| Music | Albums → upload songs (name + audio file, many at once). Music Video → YouTube link **or** uploaded video file |
| Visual Art / Theatre | Upload many photos at once, delete |
| Portfolio | Groups: title + many photos. Colours alternate on the site: 1st grey, 2nd white, 3rd grey… |
| About Me | Photo; Personal Details (simple boxes: name, role, e-mail, phone, location, more lines); text blocks, each in the left or right column |
| Contacts | Photo, heading, location, WhatsApp, e-mail |
| Footer links & login | Social icons in the footer, your login email and password |

Drag and drop sets the order of albums, songs, videos, photos, groups and About blocks; the website shows the same order.

## Files
- Uploads go to `public/uploads/` (works on any host, no `storage:link` needed).
- The original photos and videos are in `public/assets/`.

## First install on another computer or host
```
composer install
cp .env.example .env      # set DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan key:generate
php artisan migrate --seed
```
`migrate --seed` adds the two logins and the content of the static site. Running the seeder again does not duplicate or overwrite content.
