# Build Specification: Community Waste Collection Management System

## For Claude Code

This file is a complete build specification. Read it fully before writing any code. If
anything is genuinely ambiguous, make the simplest reasonable choice and note it in the
README you create, rather than stopping to ask, since this is a student class project
and not a production system.

## 1. Project Context

This is a working prototype to accompany a university Software Engineering class
project report and presentation. The report already contains the full design (Chapter
One introduction and Chapter Four system analysis and design), including the
architecture diagram, use case diagram, data flow diagrams, entity relationship
diagram, and class diagram. This build must implement that design faithfully so the
prototype clearly matches the submitted report and defense slides.

Project title: A Web Based Waste Collection Management System for Urban Residential
Communities

Group: Group 6

Members:
- Phillip Ssempereza, VU-BCS-2407-0707-EVE
- Mwondha Andrew, VU-BIT-2411-0560-EVE
- Sserunjogi Muhammad, VU-BCS-2407-0417-EVE
- Kimoga Sudais, VU-BIT-2311-0902-EVE

Include an About page (see section 7) that credits Group 6 and lists these members
with their registration numbers, so the system is clearly identifiable as this group's
own work during the class defense.

## 2. Guiding Principle: Keep It Simple

This is a class presentation project, not a production system. Prioritize clarity and
readability over cleverness or scale.

- Plain PHP only. No frameworks (no Laravel, no Symfony, no Slim).
- No Composer and no external PHP packages. The target is ordinary shared hosting
  (Hostinger) with no SSH or Composer access, so everything must run from plain files
  uploaded as is.
- No JavaScript frameworks. Bootstrap 5 loaded from a CDN link for styling, plus small
  amounts of vanilla JavaScript only where genuinely needed (for example a confirm
  dialog before cancelling a request).
- One PHP file per page rather than a routing framework or front controller. A student
  or lecturer should be able to open any file and immediately see what it does.
- Every PHP file should start with a short comment block explaining what the page does,
  who is allowed to view it, and which database tables it touches.
- Prefer straightforward procedural PHP with small helper functions over classes and
  design patterns. A few small classes are fine if they genuinely simplify things (for
  example a single Database class wrapping PDO), but do not build an object oriented
  framework of your own.
- Comment non obvious logic in plain language. Assume the reader is a student who knows
  basic PHP and SQL but has not seen this codebase before.

## 3. Technology Stack

- Backend: PHP 8.1 or later, procedural style with small helper functions
- Database: MySQL or MariaDB, accessed only through PDO with prepared statements
- Frontend: HTML5, Bootstrap 5 (CDN), a small custom stylesheet for brand colors,
  minimal vanilla JavaScript
- Sessions: native PHP sessions for authentication, no third party auth libraries
- No API layer, no JSON endpoints are required. This is a traditional multi page
  server rendered site (submit a form, reload the page, see the result).

## 4. Visual Identity

Reuse the same palette as the project report and presentation so the prototype reads
as part of the same submission:

- Primary (forest green): `#2C5F2D`
- Secondary (moss green): `#97BC62`
- Accent (warm gold): `#C98A2C`
- Light background: `#FFFFFF` or `#F4F6F2`
- Body text: `#222222`

Use a simple top navbar in the primary color with white text, Bootstrap cards and
tables for content, and the accent color sparingly for buttons or highlights (for
example the Pay button, or status badges). Keep every page visually consistent: same
navbar, same footer, same fonts. Do not add decorative elements beyond simple cards,
badges, and buttons. No sidebars are required; a top navbar with role appropriate links
is enough.

Footer text on every page: `Group 6 | Community Waste Collection Management System`

## 5. Folder Structure

Build exactly this structure (file names may only change if there is a good reason,
explain it in the README if so):

```
/ (site root, e.g. public_html or a subfolder)
  config.php              connects to the database, starts the session, defines
                           small constants such as SITE_NAME
  index.php                public landing page
  login.php                single login form for all roles
  register.php              resident self registration
  logout.php                destroys the session and redirects to index.php
  about.php                 project and Group 6 team info
  includes/
    db.php                  returns a PDO connection using config.php credentials
    auth.php                helper functions: current_user(), require_role($role),
                             password checks, redirect helpers
    header.php               opening <html>, <head>, navbar (navbar links change
                             based on logged in role)
    footer.php                closing tags and the footer text
  assets/
    css/style.css            brand colors and small layout tweaks
  resident/
    dashboard.php             summary of the resident's recent requests and zone
    request_pickup.php        form to submit a new pickup request
    my_requests.php            list of the resident's requests with status and a
                               Pay button when a request is completed and unpaid
    schedule.php                read only view of the collection schedule for the
                               resident's zone
    pay.php                    simulated payment form and processing for one
                               request
    complaints.php              list of the resident's complaints plus a form to
                               submit a new one
  collector/
    dashboard.php               summary of requests assigned to this collector
    my_requests.php              list of assigned requests with a button to mark
                               a request completed
  admin/
    dashboard.php               summary counters (residents, collectors, pending
                               requests, complaints, total payments)
    zones.php                    list, add, edit, delete zones
    schedules.php                list, add, edit, delete collection schedules
    vehicles.php                  list, add, edit, delete vehicles
    collectors.php                list, add, edit, delete collector accounts and
                               assign a zone and vehicle to each
    requests.php                  list all pickup requests, assign a collector to
                               a pending request
    complaints.php                 list complaints, mark as resolved with an
                               optional short response
    reports.php                   simple summary report: counts of requests by
                               status, total payments received, complaints open
                               versus resolved, optionally filtered by a date
                               range
  sql/
    schema.sql                    CREATE TABLE statements, see section 6
    seed.sql                       sample data, see section 8
  README.md                       setup and deployment steps, written for
                               someone who has never seen this project before
```

## 6. Database Schema

Implement the schema below. It mirrors the entities, attributes, and relationships in
the project's Entity Relationship Diagram exactly, with authentication fields
(email and password_hash) added to the resident, collector, and administrator tables
so each role can log in.

```sql
CREATE TABLE zones (
  zone_id INT AUTO_INCREMENT PRIMARY KEY,
  zone_name VARCHAR(100) NOT NULL,
  location_description VARCHAR(255)
);

CREATE TABLE administrators (
  admin_id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(50) DEFAULT 'administrator',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE vehicles (
  vehicle_id INT AUTO_INCREMENT PRIMARY KEY,
  registration_number VARCHAR(50) NOT NULL,
  vehicle_type VARCHAR(50),
  capacity_kg INT
);

CREATE TABLE residents (
  resident_id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  phone_number VARCHAR(30) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  address VARCHAR(255),
  zone_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (zone_id) REFERENCES zones(zone_id)
);

CREATE TABLE collectors (
  collector_id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  phone_number VARCHAR(30) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  zone_id INT NOT NULL,
  vehicle_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (zone_id) REFERENCES zones(zone_id),
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id)
);

CREATE TABLE schedules (
  schedule_id INT AUTO_INCREMENT PRIMARY KEY,
  zone_id INT NOT NULL,
  collection_day VARCHAR(20) NOT NULL,
  collection_time VARCHAR(20) NOT NULL,
  frequency VARCHAR(30) NOT NULL,
  FOREIGN KEY (zone_id) REFERENCES zones(zone_id)
);

CREATE TABLE pickup_requests (
  request_id INT AUTO_INCREMENT PRIMARY KEY,
  resident_id INT NOT NULL,
  collector_id INT,
  request_date DATE NOT NULL,
  waste_type VARCHAR(50) NOT NULL,
  status ENUM('pending', 'assigned', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (resident_id) REFERENCES residents(resident_id),
  FOREIGN KEY (collector_id) REFERENCES collectors(collector_id)
);

CREATE TABLE payments (
  payment_id INT AUTO_INCREMENT PRIMARY KEY,
  resident_id INT NOT NULL,
  request_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  payment_method ENUM('mtn_mobile_money', 'airtel_money', 'cash') NOT NULL,
  status ENUM('pending', 'paid') NOT NULL DEFAULT 'paid',
  FOREIGN KEY (resident_id) REFERENCES residents(resident_id),
  FOREIGN KEY (request_id) REFERENCES pickup_requests(request_id)
);

CREATE TABLE complaints (
  complaint_id INT AUTO_INCREMENT PRIMARY KEY,
  resident_id INT NOT NULL,
  description TEXT NOT NULL,
  date_submitted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('open', 'resolved') NOT NULL DEFAULT 'open',
  admin_response VARCHAR(255),
  FOREIGN KEY (resident_id) REFERENCES residents(resident_id)
);
```

Small nullable additions like `admin_response` are fine if they make a feature work
cleanly. Do not otherwise change the entities or remove attributes from this schema,
since it needs to match the ERD in the submitted report.

## 7. Pages and Features by Role

Implement every item below. Each maps to a functional requirement already documented
in the report, so completeness matters more than adding extra features.

### Public (not logged in)

- `index.php`: short explanation of the system and the problem it solves, with
  buttons to Register and Login.
- `about.php`: one paragraph about the project plus a simple list of the four Group 6
  members and their registration numbers.
- `register.php`: resident self registration (full name, phone number, email,
  password, address, zone selected from a dropdown). Hash the password with
  `password_hash()` before saving. After successful registration, log the resident in
  and send them to `resident/dashboard.php`.
- `login.php`: a single form with email and password, plus a role selector
  (Resident, Waste Collector, Administrator). Check the matching table for that role,
  verify the password with `password_verify()`, start the session, and redirect to
  that role's dashboard. Show a clear error message on failure, do not reveal whether
  the email or the password was wrong.
- `logout.php`: destroy the session and redirect to `index.php`.

Collector and administrator accounts are not self registered. They are created by an
administrator from `admin/collectors.php` (collectors) or exist from the seed data
(the first administrator). This matches the report, which only lists account creation
as a resident facing requirement.

### Resident (logged in as resident)

- `resident/dashboard.php`: a welcome message, a count of the resident's pending and
  completed requests, and their zone's next scheduled collection.
- `resident/request_pickup.php`: a form to create a pickup request (waste type chosen
  from a short fixed list such as Household, Plastic, Organic, Other, plus a preferred
  date). On submit, insert a `pickup_requests` row with status `pending`.
- `resident/my_requests.php`: a table of the resident's own requests with date, waste
  type, status badge, and the assigned collector's name once assigned. Show a Pay
  button next to any request that is completed and does not yet have a paid payment
  row, linking to `pay.php?request_id=...`.
- `resident/schedule.php`: a read only table of the collection schedule rows for the
  resident's own zone.
- `resident/pay.php`: shows the request being paid for and its amount (use a fixed
  simple fee, for example a flat rate depending on waste type, defined once as a PHP
  constant or lookup array so it is easy to change), a payment method choice (MTN
  Mobile Money, Airtel Money, Cash), and a Confirm Payment button. On submit, insert a
  `payments` row with status `paid` and show a clear success message. This is a
  simulated payment, no real gateway call is made. Say so plainly in a small note on
  the page, for example "This is a simulated payment for demonstration purposes."
- `resident/complaints.php`: a form to submit a new complaint (free text description)
  and a table listing the resident's own complaints with status and, once resolved,
  the administrator's response.

### Waste Collector (logged in as collector)

- `collector/dashboard.php`: a welcome message and a count of pending versus
  completed requests assigned to this collector.
- `collector/my_requests.php`: a table of requests assigned to this collector
  (status `assigned`), each with a Mark Completed button that updates the request's
  status to `completed`. Also show the collector's own recently completed requests
  below for reference.

### Administrator (logged in as administrator)

- `admin/dashboard.php`: counters for total residents, total collectors, pending
  requests, open complaints, and total payments received (sum of paid amounts).
- `admin/zones.php`: list all zones in a table, with a form to add a zone and edit or
  delete buttons on each row.
- `admin/schedules.php`: list all schedules with their zone name, day, time, and
  frequency, with a form to add a schedule and edit or delete buttons.
- `admin/vehicles.php`: list, add, edit, and delete vehicles.
- `admin/collectors.php`: list all collectors with their zone and vehicle, a form to
  add a new collector account (this is where collector passwords are set, hashed with
  `password_hash()`), and edit or delete buttons.
- `admin/requests.php`: a table of every pickup request across all zones, with a
  dropdown on each pending request to assign a collector (only collectors from the
  same zone as the request's resident should be offered), which updates the request's
  status to `assigned` and sets its `collector_id`.
- `admin/complaints.php`: a table of every complaint with resident name, description,
  status, and a small form to write a response and mark it resolved.
- `admin/reports.php`: a simple summary report page. Show, for a chosen date range
  (default the last 30 days) using plain HTML date inputs: number of requests by
  status, total payments received, and number of complaints by status. Present this
  as Bootstrap cards or a simple table, not a chart library. This satisfies the
  reporting functional requirement without adding a charting dependency.

## 8. Seed Data

Provide `sql/seed.sql` that inserts, after `schema.sql` has been run:

- 3 zones with realistic but generic names (for example Zone A - Central, Zone B -
  Eastside, Zone C - Riverside) and short location descriptions
- 1 administrator account: email `admin@group6.test`, a known demo password, clearly
  stated in the README (for example `Admin@123`), hashed correctly with
  `password_hash()` in the SQL (generate the hash with PHP and paste the resulting
  string into the seed file, do not store a plain password)
- 2 to 3 vehicles
- 2 to 3 collectors, one per zone, each with a demo password stated in the README,
  linked to a zone and a vehicle
- 4 to 6 residents spread across the zones, each with a demo password stated in the
  README
- 2 schedules per zone
- 6 to 10 pickup requests in a mix of statuses (pending, assigned, completed), spread
  across residents
- 2 to 3 payments linked to completed requests
- 2 to 3 complaints, at least one resolved with an admin response and at least one
  still open

State every demo login (email and password, per role) clearly in the README so the
group can log in live during the presentation without hunting through the database.

## 9. Security Basics (Required, Keep Simple)

- Use PDO prepared statements for every query that includes any user supplied value.
  Never concatenate user input into SQL.
- Hash all passwords with `password_hash()` and verify with `password_verify()`.
  Never store or compare plain text passwords.
- Every page under `resident/`, `collector/`, and `admin/` must call a
  `require_role('resident' | 'collector' | 'administrator')` helper at the very top,
  before any output, which redirects to `login.php` if the user is not logged in as
  that role.
- Escape all dynamic output in HTML with `htmlspecialchars()` to avoid cross site
  scripting.
- Validate form input on the server side even though HTML5 form attributes
  (`required`, `type="email"`, and so on) are also used for a better user experience.
- Turn PHP error display off in the production `config.php` (`display_errors = 0`),
  but log errors so problems can still be diagnosed.

Advanced security features are explicitly out of scope: no CSRF tokens, no rate
limiting, no email verification, no password reset flow. Mention these as
possible future improvements in the README rather than building them.

## 10. Explicitly Out of Scope

Do not build any of the following. They are either not required by the report's
design, or deliberately simplified per the project scope:

- Real mobile money or payment gateway integration (payments are simulated, see
  section 7)
- Real SMS or email notifications (if you want to show the "notify resident"
  requirement, display an in app message or banner instead, for example "A
  notification would be sent to the resident here")
- A REST or JSON API
- Any JavaScript framework or build step (React, Vue, npm bundlers)
- Multi language support
- File or image uploads
- Automated tests (not required for this class demo, skip them)

## 11. Deployment Instructions for Hostinger Shared Hosting

Write these steps into the README in your own words, adapted to whatever folder
names you actually used, but the flow is:

1. In Hostinger hPanel, create a MySQL database and a database user, and note the
   generated database name, username, password, and host (usually `localhost`).
2. Open phpMyAdmin from hPanel, select the new database, and run `sql/schema.sql`
   followed by `sql/seed.sql` using the Import tab or the SQL tab.
3. Edit `config.php` and fill in the real database name, username, password, and
   host from step 1. Keep a `config.sample.php` with placeholder values in the
   repository so the real credentials are never committed if the group uses git.
4. Upload all project files to `public_html` (or a subfolder such as
   `public_html/wastecollect` if the group wants it at a sub path) using the
   Hostinger File Manager or an FTP client. Preserve the folder structure exactly.
5. In hPanel, confirm the PHP version is set to 8.1 or later for the domain.
6. Visit the site's URL in a browser and confirm `index.php` loads, then log in with
   the seeded admin account to confirm the database connection works.
7. Log in as each seeded demo account (resident, collector, administrator) once to
   confirm every role's dashboard loads without errors before the live presentation.

## 12. Acceptance Checklist

Before considering the build finished, confirm all of the following work end to end:

- A new resident can register, log in, and see their dashboard
- A resident can submit a pickup request and see it listed with status pending
- An administrator can assign a collector to that pending request from their own zone
- The collector can log in and see the request, then mark it completed
- The resident can then pay for the completed request through the simulated payment
  flow and see it marked paid
- A resident can submit a complaint, and an administrator can resolve it with a
  response that the resident can then see
- The administrator can add and edit a zone, a schedule, a vehicle, and a collector
- The reports page shows sensible counts that change when the underlying data changes
- Every page shows a consistent navbar and footer, and none show a raw PHP error
- All seeded demo logins from the README work exactly as documented
