# Community Waste Collection Management System

A Web Based Waste Collection Management System for Urban Residential Communities.

**Group 6**

| Name | Registration Number |
|---|---|
| Phillip Ssempereza | VU-BCS-2407-0707-EVE |
| Mwondha Andrew | VU-BIT-2411-0560-EVE |
| Sserunjogi Muhammad | VU-BCS-2407-0417-EVE |
| Kimoga Sudais | VU-BIT-2311-0902-EVE |

This is a working prototype built to accompany the group's Software Engineering
class project report. It implements the system described in the report's
Chapter One and Chapter Four (architecture, use case diagram, data flow
diagrams, entity relationship diagram, and class diagram) as plain PHP pages
with no framework, so it can be understood file by file and deployed to
ordinary shared hosting.

## 1. What This Is

- Plain procedural PHP 8.1+, one file per page, no framework, no Composer.
- MySQL/MariaDB accessed only through PDO with prepared statements.
- Bootstrap 5 (loaded from a CDN) plus a small custom stylesheet for the
  project's brand colors, and a few lines of vanilla JavaScript for confirm
  dialogs.
- Three roles: **Resident**, **Waste Collector**, **Administrator**, each with
  their own dashboard and pages.
- Payments are **simulated** — no real mobile money or card gateway is
  contacted. Every payment page says so explicitly.

## 2. Folder Structure

```
config.php / config.sample.php   database credentials, session start, constants
index.php, login.php, register.php, logout.php, about.php
includes/            db.php, auth.php, header.php, footer.php
assets/css/          style.css (brand colors)
resident/            dashboard, request_pickup, my_requests, schedule, pay, complaints
collector/           dashboard, my_requests
admin/               dashboard, zones, schedules, vehicles, collectors, requests, complaints, reports
sql/                 schema.sql, seed.sql
```

## 3. Local Setup (XAMPP / WAMP / Laragon)

1. Install a local PHP 8.1+ and MySQL/MariaDB stack (XAMPP, WAMP, or
   Laragon all work).
2. Create a database, for example `waste_collection`.
3. Import the schema, then the seed data, in that order:
   - In phpMyAdmin: open the database, use the Import tab to run
     `sql/schema.sql`, then run `sql/seed.sql`.
   - Or from a terminal: `mysql -u root waste_collection < sql/schema.sql`
     followed by `mysql -u root waste_collection < sql/seed.sql`.
4. `config.php` already contains working defaults for a typical local setup
   (host `localhost`, database `waste_collection`, user `root`, no password).
   Edit it if your local setup differs. `config.sample.php` is kept as a
   placeholder reference so real credentials are never committed to version
   control if the group uses git.
5. Point your local server's document root at this folder (or copy the
   folder into `htdocs` / `www`) and visit `index.php` in a browser.

## 4. Demo Logins

All seeded from `sql/seed.sql`. Use these to log in live during the
presentation without hunting through the database.

| Role | Email | Password |
|---|---|---|
| Administrator | admin@group6.test | Admin@123 |
| Collector (Zone A) | collector1@group6.test | Collector@123 |
| Collector (Zone B) | collector2@group6.test | Collector@123 |
| Collector (Zone C) | collector3@group6.test | Collector@123 |
| Resident (Zone A) | resident1@group6.test | Resident@123 |
| Resident (Zone A) | resident2@group6.test | Resident@123 |
| Resident (Zone B) | resident3@group6.test | Resident@123 |
| Resident (Zone B) | resident4@group6.test | Resident@123 |
| Resident (Zone C) | resident5@group6.test | Resident@123 |
| Resident (Zone C) | resident6@group6.test | Resident@123 |

The seed data includes a mix of pending, assigned, completed, and cancelled
requests, a few paid payments, and a couple of complaints (one resolved, one
open), so every page has something to show right after import. Resident 5
(`resident5@group6.test`) has a completed, unpaid request so the simulated
payment flow can be demonstrated live.

## 5. Deploying to Hostinger Shared Hosting

1. In Hostinger hPanel, create a MySQL database and a database user. Note
   the generated database name, username, password, and host (usually
   `localhost`).
2. Open phpMyAdmin from hPanel, select the new database, and import
   `sql/schema.sql` first, then `sql/seed.sql`, using the Import tab (or the
   SQL tab, pasting the file contents).
3. Edit `config.php` and fill in the real `DB_HOST`, `DB_NAME`, `DB_USER`,
   and `DB_PASS` from step 1.
4. Upload every file and folder in this project to `public_html` (or a
   subfolder such as `public_html/wastecollect` if you want the site at a
   sub path) using the Hostinger File Manager or an FTP client. Keep the
   folder structure exactly as it is; the app works out the correct link
   paths automatically whether it's installed at the domain root or in a
   subfolder.
5. In hPanel, confirm the PHP version for the domain is set to 8.1 or later.
6. Visit the site's URL and confirm `index.php` loads, then log in as the
   seeded admin account to confirm the database connection works.
7. Log in once as each seeded demo account (resident, collector,
   administrator) to confirm every role's dashboard loads without errors
   before the live presentation.

## 6. Design Notes and Choices Made

A few small, deliberate calls made while building this prototype, noted here
per the build spec rather than left unexplained:

- **BASE_URL**: `config.php` works out the web path to the project folder
  automatically (comparing `__DIR__` to `$_SERVER['DOCUMENT_ROOT']`) and
  every internal link is built from it. This is what lets the exact same
  files work whether the site sits at the domain root or in a subfolder like
  `public_html/wastecollect`, with no extra configuration step.
- **Pickup fees**: a flat fee per waste type (`WASTE_FEES` in `config.php`):
  Household UGX 5,000, Plastic UGX 3,000, Organic UGX 3,000, Other
  UGX 4,000. Defined once so the group can change prices in one place.
  Currency is UGX to match the MTN Mobile Money / Airtel Money payment
  options in the report.
- **Cancelling a pending request**: the ERD's `pickup_requests.status`
  column already includes a `cancelled` value, and the build spec's own
  example of "genuinely needed" JavaScript is a confirm dialog before
  cancelling a request. `resident/my_requests.php` adds a small Cancel
  button (with that confirm dialog) on a resident's own pending requests, so
  that enum value and that JS example both have a real feature behind them.
- **"Notify the resident" requirement**: per the build spec, no real
  notification system is built. When an administrator assigns a collector to
  a request, a flash message says "A notification would be sent to the
  resident here" instead.
- **"Next scheduled collection" on the resident dashboard**: schedules don't
  carry an actual calendar date (only a weekday, time, and frequency), so
  the dashboard shows the zone's first schedule row as a simple
  representative example rather than computing a literal next date.

## 7. Security Notes (Deliberately Simple)

- Every query touching user input uses PDO prepared statements.
- Passwords are hashed with `password_hash()` and checked with
  `password_verify()`; nothing is ever stored or compared in plain text.
- Every page under `resident/`, `collector/`, and `admin/` calls
  `require_role()` as the very first thing it does, before any output, and
  is redirected to `login.php` if the session doesn't match that role.
- All dynamic output is escaped with `htmlspecialchars()`.
- `config.php` turns off `display_errors` and turns on `log_errors`, so
  visitors never see a raw PHP error, but problems are still logged
  server-side for debugging.

Out of scope for this class prototype, per the build spec: CSRF tokens, rate
limiting, email verification, and password reset. These would be natural
next steps for a production version of this system.

## 8. Explicitly Out of Scope

Real mobile money/payment gateway integration, real SMS/email notifications,
a REST/JSON API, any JavaScript framework or build step, multi-language
support, file/image uploads, and automated tests are all intentionally not
built, per the project's scope as a class prototype.
