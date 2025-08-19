# GUB Bus Booking (PHP/MySQL)

[![GitHub release](https://img.shields.io/github/v/release/Mohim71/Bus-Scheduling-System)](https://github.com/Mohim71/Bus-Scheduling-System/releases)
[![License](https://img.shields.io/github/license/Mohim71/Bus-Scheduling-System)](https://github.com/Mohim71/Bus-Scheduling-System/blob/main/LICENSE)
[![Made with PHP](https://img.shields.io/badge/Made%20with-PHP-777bb4.svg?logo=php&logoColor=white)](https://www.php.net/)

A lightweight bus-slot booking system for students with a simple manager dashboard. Students can log in, select arrival and departure slots by route and time, and view/rebook. Managers can post scrolling notices, allocate buses to routes/time windows, and adjust bus assignments.

> Source: `XIII-done/htdocs/` (the archive includes a Bitnami/XAMPP dashboard under `htdocs/dashboard/`, which is not part of the app).



# GUB Bus Booking (PHP/MySQL)

A lightweight bus-slot booking system for students with a simple manager dashboard. Students can log in, select arrival and departure slots by route and time, and view/rebook. Managers can post scrolling notices, allocate buses to routes/time windows, and adjust bus assignments.

> Source: `XIII-done/htdocs/` (the archive includes a Bitnami/XAMPP dashboard under `htdocs/dashboard/`, which is not part of the app).

## Features

- Student login and session handling (`users` table)
- Book **arrival** and **departure** bus slots (route + time)
- Rebooking page shows latest bookings and allows changes
- Scrolling notices on the main page (managed by admins)
- Manager dashboard for:
  - Notices: create, activate/deactivate, delete
  - Per-slot student counts (reads `arrival_info`/`departure_info`)
  - Bus allocations upsert per (route, time_slot)
  - Update a bus’s designated route
- Clean HTML/CSS and a small JS controller (`bush.js`)
- Minimal demo form under `kme/` showing DB writes (separate test_db)

## Tech Stack

- PHP 8+ (procedural style with mysqli + prepared statements)
- MySQL/MariaDB
- HTML5/CSS3/Vanilla JS
- Apache (tested with XAMPP/Bitnami layout)

## Directory Overview

```
XIII-done/
└─ htdocs/
   ├─ index.php               # redirects to the Bitnami dashboard (not the app)
   ├─ login.html / login.php  # student login
   ├─ main_interface.php      # main landing with notices & CTA
   ├─ schedule.html           # booking UI (arrival/departure)
   ├─ save_schedule.php       # persists selections
   ├─ rebook.php              # rebooking and latest booking view
   ├─ logout.php
   ├─ login2.html / bus_manager_login.php
   ├─ bus_manager_interface.php
   ├─ allocate_buses.php
   ├─ update_bus_route.php
   ├─ bush.css / bush.js      # UI & client logic
   ├─ bus_manager.css         # manager UI
   ├─ kme/                    # demo page for DB write (test_db)
   └─ dashboard/              # Bitnami docs (unrelated to app)
```

<details>
<summary>All app files under <code>htdocs/</code> (excluding dashboard assets)</summary>

  - htdocs/allocate_buses.php
  - htdocs/applications.html
  - htdocs/bitnami.css
  - htdocs/bus_manager.css
  - htdocs/bus_manager_interface.php
  - htdocs/bus_manager_login.php
  - htdocs/bush.css
  - htdocs/bush.js
  - htdocs/index.php
  - htdocs/kme/i.html
  - htdocs/kme/save_name.php
  - htdocs/login.html
  - htdocs/login.php
  - htdocs/login2.html
  - htdocs/logout.php
  - htdocs/main_interface.php
  - htdocs/rebook.php
  - htdocs/save_schedule.php
  - htdocs/schedule.html
  - htdocs/update_bus_route.php
  - htdocs/working_files.php
</details>

## Getting Started (Local)

1. **Install prerequisites**
   - XAMPP (Apache + MySQL + PHP) or LAMP/WAMP equivalent.
2. **Place files**
   - Extract `XIII-done/htdocs` to your Apache document root (e.g., `C:/xampp/htdocs` or `/var/www/html`).  
     You can also host it as a subfolder.
3. **Create the database**
   - Create a MySQL database named **`ded2`**.
4. **Create tables**
   - Use the *Inferred Minimal Schema* below to create the tables the code expects.
5. **Configure credentials (if needed)**
   - By default the code uses `host=localhost, user=root, password=""` and `dbname=ded2`.
   - Update the connection details in these files if yours differ:
     - `login.php`, `save_schedule.php`, `rebook.php`, `bus_manager_login.php`, `bus_manager_interface.php`, `allocate_buses.php`, `update_bus_route.php`, `main_interface.php`, `kme/save_name.php` (uses `test_db`).
6. **Seed minimal data**
   - Add at least one user in `users` (for student login).
   - Add at least one manager in `bus_manager_details`.
   - Optionally insert some `bus_info` rows (bus_no + designated_route).
7. **Run**
   - Open `http://localhost/` and navigate to the app pages directly, e.g.:
     - `http://localhost/login.html` (student)
     - `http://localhost/login2.html` (manager)

> **Note**: `index.php` in the root currently redirects to Bitnami’s dashboard. You can change it to point to `login.html` for convenience.
