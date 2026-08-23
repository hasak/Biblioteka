# Biblioteka

A self-hosted catalogue for a physical book collection. It tracks what you own, where each
book actually sits on the shelf, where you bought it, and whether you have read it.

It was built for one specific home library and is published in case the parts around ISBN
handling — barcode scanning, metadata lookup and cover fetching — are useful to someone else.

## What it does

- **Books** with title, original title, author, series and part number, publisher, year,
  country, language, genre and ISBN.
- **Physical position** on a 6×6 shelf grid, picked by clicking the grid rather than typing
  coordinates. The same grid renders read-only on the book page.
- **Read tracking** — a read flag and the date it was finished.
- **Provenance** — the city, country and date a book was bought.
- **Covers**, either uploaded by hand or fetched automatically from the ISBN.
- **Soft deletes**, so a removed book stays restorable, cover file and all.
- **Series, genres, countries and languages** as first-class resources, each listing the books
  that belong to it.
- **Roles** — only users with the `admin` role reach the panel.

## The ISBN pipeline

This is the part worth looking at.

**Scanning.** `/admin/scan` opens the device camera and runs
[QuaggaJS](https://github.com/serratus/quaggaJS) over the video. A detected barcode is not
trusted on sight: the page recomputes the EAN-13 or ISBN-10 check digit itself and ignores
anything that fails, which keeps a misread digit from creating a wrong book. A valid code
redirects to the create form with the ISBN already filled in.

**Metadata.** `BookApi::fromIsbn()` asks Google Books first and falls back to Open Library,
returning title, author, publisher, year and — where the language maps to a known record —
the language. A dead or slow third party is logged and treated as "no result" rather than
being allowed to take the page down with it.

**Covers.** Three sources are tried in order: Google Books, Open Library, Longitood. The form
walks them one request at a time so the browser can show which source is being tried and offer
a Cancel button, instead of freezing on a slow host. Each candidate passes a quality gate —
images narrower than 100px are rejected as placeholders, the search stops early once something
at least 300px wide turns up, and anything over 16 MB is refused. Google's `zoom` parameter is
exploited to ask for sizes larger than the thumbnail its API advertises. Accepted covers land
in `storage/app/public/books/covers/<isbn>.<ext>`, with older spellings of the same ISBN
cleaned up so orphans do not accumulate.

## Built with

Laravel 12, Filament 5, PHP 8.2, MySQL, Tailwind and Alpine through Vite. Authentication is
Laravel Breeze; roles come from `spatie/laravel-permission`. Everything that matters lives in
the Filament panel under `/admin` — the root URL just redirects there.

## Getting started

```bash
git clone https://github.com/hasak/Biblioteka.git
cd Biblioteka

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Then edit `.env`. The file still ships Laravel's SQLite default, so point it at your database:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=biblioteka
DB_USERNAME=
DB_PASSWORD=

# Optional. Google Books answers without a key, but rate-limits harder.
GOOGLE_BOOKS_API_KEY=

# Optional. Leave blank and the seeder generates a password and prints it once.
SEED_AIDA_PASSWORD=
SEED_HASAK_PASSWORD=
SEED_DEMO_PASSWORD=
```

Create the schema, link the cover storage and build the assets:

```bash
php artisan migrate --seed
php artisan storage:link      # required, or no cover will ever display
npm run build
```

`composer dev` then runs the server, queue listener, log tail and Vite together on
<http://localhost:8000>. `composer setup` is a shortcut for the install-and-build steps, but it
does not seed or create the storage link — do those yourself.

### Seeded accounts

Seeding creates two admins (`Aida`, `Hasak`) and one ordinary user (`user`), plus a small set of
sample books, genres, series, countries and languages. The sample books are demo data, not a
real collection — clear them out before adding your own. **Change the seeded passwords before
putting this anywhere public.**

## Tests

```bash
composer test
```

Pest runs against an in-memory SQLite database, so the `pdo_sqlite` extension must be enabled
for the CLI even when the app itself is on MySQL.

## Deployment notes

Nothing unusual — `composer install --no-dev --optimize-autoloader`, `npm run build`, then
`php artisan migrate --force` and the config, route and view caches. Two things are easy to
forget: `php artisan storage:link` must exist on the server too, and `APP_DEBUG` must be
`false`, since a Laravel debug page exposes environment variables including `APP_KEY` and the
database credentials.

## Licence

MIT — see [LICENSE](LICENSE). Note that the licence covers this code only. Book metadata and
cover images retrieved at runtime come from Google Books, Open Library and Longitood, and carry
those services' own terms.
