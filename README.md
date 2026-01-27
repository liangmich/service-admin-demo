# Service Admin Management System (PHP + MySQL)

A lightweight web-based admin dashboard for managing **customers**, **service providers**, **services**, and **orders/bookings**.  
Built with **PHP**, **MySQL**, and a simple UI layer.

---

## Features

- **Authentication & Session-based Access**
  - Login / logout
  - Protected admin pages

- **User Management**
  - View users
  - Add / edit / delete users
  - Role-based access (e.g., Admin / Super Admin)

- **Customer Management**
  - Customer list
  - Customer detail page

- **Provider Management**
  - Provider list
  - Provider review page (depending on schema)

- **Service Management**
  - List services
  - Add service
  - Edit service

- **Orders / Bookings**
  - Orders list
  - Order detail page
  - Supports status/history tracking (depending on DB schema)

---

## Tech Stack

- **Backend:** PHP
- **Database:** MySQL
- **Frontend/UI:** HTML + CSS (see `assets/app.css`)
- **Project type:** Classic PHP (multi-page) application

---

## Project Structure

```txt
service-admin-demo/
  assets/
    app.css
  lib/
    auth.php
    db.php
    helpers.php
  partials/
    header.php
    footer.php
    admin_nav.php
  sql/
    schema.sql
  index.php
  login.php
  logout.php
  dashboard.php

  users.php
  user_add.php
  user_edit.php
  user_delete.php

  customers.php
  customer_view.php

  providers.php
  provider_review.php

  services.php
  service_add.php
  service_edit.php

  orders.php
  order_view.php

  config.php
  gen_hash.php
  README.md

#Requirements
PHP 7.4+ (recommended PHP 8.x)

MySQL 5.7+ / MariaDB

Apache (XAMPP/WAMP/MAMP is OK)

#Quick Start (Local Setup)
1) Put the project in your web root
Example (XAMPP on Windows):

C:\xampp\htdocs\service-admin-demo
2) Create database & import schema
Create a database (example): service_admin

#Import the schema:

-- Use phpMyAdmin or MySQL CLI to import:
service-admin-demo/sql/schema.sql
3) Configure database connection
Edit:

service-admin-demo/config.php
Update DB host/user/password/database name to match your environment.

4) Run the project
Open in browser:

http://localhost/service-admin-demo/
Login
Default Admin Account
Use the following demo credentials:

Email: admin@example.com

Password: Admin123!

#How to log in
Open:

http://localhost/service-admin-demo/login.php
Enter the credentials above

After successful login, you will be redirected to:

/dashboard.php
If login fails (reset admin password)
If your database does not match the password above, reset it:

#Generate a new password hash using:

http://localhost/service-admin-demo/gen_hash.php
Update the admin user in MySQL (adjust column names if needed):

#UPDATE users
SET password_hash = '<PASTE_NEW_HASH_HERE>'
WHERE email = 'admin@example.com';
Log in again using the plaintext password you hashed.

#Pages / Routes
/index.php — entry

/login.php — login form

/logout.php — logout

/dashboard.php — admin dashboard

Users
/users.php

/user_add.php

/user_edit.php

/user_delete.php

Customers
/customers.php

/customer_view.php

Providers
/providers.php

/provider_review.php

Services
/services.php

/service_add.php

/service_edit.php

Orders
/orders.php

/order_view.php

#Roles & Permissions
Role-based access is enforced via lib/auth.php.

Typical setup:

Admin: manage operational data (customers, services, orders)

Super Admin: includes user management (add/edit/delete users)

Final behavior depends on your implementation and database schema.

#Common Troubleshooting
Blank page / 500 error

Enable PHP error display temporarily (php.ini → display_errors=On)

Check Apache error logs

Database connection failed

Re-check values in config.php

Confirm MySQL service is running

Confirm DB name matches the imported schema

Login redirects back to login page

Session/cookie issue or auth guard logic

Verify PHP sessions are enabled

Check lib/auth.php for session key names
