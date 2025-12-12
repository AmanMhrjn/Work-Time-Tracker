# WorkTracker (PHP + MySQL)

## Setup
1) Put this folder into your server root:
   - XAMPP: C:/xampp/htdocs/worktracker
   - WAMP:  C:/wamp64/www/worktracker
   - Linux: /var/www/html/worktracker

2) Create the database:
   - Open phpMyAdmin
   - Run: sql/schema.sql

3) Configure database credentials:
   - Edit: includes/db.php
   - Set DB_USER / DB_PASS if needed

4) Open:
   http://localhost/worktracker/

## Notes
- Overnight shifts are supported (end time earlier than start time).
- Weekly view uses week start from Settings (Monday or Sunday).
