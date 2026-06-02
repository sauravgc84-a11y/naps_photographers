# 📸 NAPS PHOTOGRAPHERS — Smart Appointment Booking System
## Complete Setup & Installation Guide

---

## 🎯 PROJECT OVERVIEW

A full-stack photography studio booking system featuring:
- Premium dark/gold themed public website
- Customer registration & login
- Online booking with time-slot conflict prevention
- Simulated card payment processing
- Customer dashboard (view bookings, pay, cancel, notifications)
- Full admin dashboard (manage bookings, services, gallery, users, payments, reviews)

---

## ⚙️ REQUIREMENTS

- **XAMPP** (v7.4+ or v8.x recommended)
- **PHP** 7.4 or higher
- **MySQL** 5.7 or higher
- A modern web browser (Chrome, Firefox, Edge)

---

## 🚀 INSTALLATION STEPS

### Step 1 — Copy Files
1. Extract the `naps_photographers` folder
2. Copy it into your XAMPP `htdocs` directory:
   ```
   C:\xampp\htdocs\naps_photographers\
   ```
   *(On Mac: `/Applications/XAMPP/htdocs/naps_photographers/`)*

### Step 2 — Start XAMPP
1. Open **XAMPP Control Panel**
2. Click **Start** next to **Apache**
3. Click **Start** next to **MySQL**

### Step 3 — Import Database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click **"New"** in the left sidebar
3. Create a database named `naps_photographers` (or let the SQL do it)
4. Click on `naps_photographers` database
5. Click the **Import** tab
6. Click **Choose File** and select `naps_photographers/database.sql`
7. Click **Go** to import

### Step 4 — Configure Database (if needed)
Open `db.php` and update credentials if your MySQL setup differs:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // Your MySQL username
define('DB_PASS', '');          // Your MySQL password (blank by default in XAMPP)
define('DB_NAME', 'naps_photographers');
```

### Step 5 — Launch the Application
Open your browser and navigate to:
```
http://localhost/naps_photographers/
```

---

## 🔑 LOGIN CREDENTIALS

| Role     | Email                              | Password   |
|----------|------------------------------------|------------|
| Admin    | admin@napsphotographers.com        | password   |
| Customer | alex@example.com                   | password   |

> **Note:** The password `password` is hashed using bcrypt in the database.
> When creating new accounts via signup, any password you set will be properly hashed.

---

## 📁 PROJECT STRUCTURE

```
naps_photographers/
│
├── index.php                    ← Public homepage
├── db.php                       ← Database connection & helpers
├── database.sql                 ← Database schema + seed data
│
├── assets/
│   ├── css/style.css            ← Global stylesheet (dark gold theme)
│   └── js/script.js             ← Frontend JavaScript
│
├── auth/
│   ├── login.php                ← Login page
│   ├── signup.php               ← Registration page
│   └── logout.php               ← Session destroyer
│
├── customer/
│   └── customer_dashboard.php   ← Customer portal (bookings, payments, notifications)
│
├── admin/
│   ├── dashboard.php            ← Admin overview
│   ├── manage_bookings.php      ← Approve/reject/reschedule bookings
│   ├── manage_users.php         ← View/block/delete customers
│   ├── manage_services.php      ← Add/edit/delete services
│   ├── manage_gallery.php       ← Upload & manage gallery images
│   ├── manage_reviews.php       ← Approve/delete testimonials
│   ├── manage_payments.php      ← View all payment records
│   ├── manage_notifications.php ← Send notifications to users
│   ├── manage_homepage.php      ← Edit homepage content dynamically
│   └── partials/
│       ├── admin_header.php     ← Admin layout header + sidebar
│       └── admin_footer.php     ← Admin layout footer
│
├── booking/
│   ├── book.php                 ← Process booking submissions
│   ├── update_booking.php       ← Customer cancel booking
│   └── submit_review.php        ← Submit review handler
│
└── payment/
    └── payment.php              ← Simulated card payment processor
```

---

## 🌟 KEY FEATURES

### Public Website
- ✅ Sticky navigation with smooth scroll
- ✅ Auto-advancing hero image slider with controls
- ✅ About section with studio story
- ✅ Services section (dynamic from DB) with hover effects
- ✅ Gallery with category filtering + lightbox zoom
- ✅ Packages/pricing cards (with featured badge)
- ✅ Booking form (locked to logged-in users)
- ✅ Testimonials (dynamic from DB)
- ✅ Contact section
- ✅ Footer with links
- ✅ Page loader animation
- ✅ Toast notifications
- ✅ Reveal-on-scroll animations

### Customer Dashboard
- ✅ View all bookings with status
- ✅ Pay Now button (for approved bookings)
- ✅ Simulated card payment form with validation
- ✅ Cancel booking
- ✅ Notification centre
- ✅ Profile view

### Admin Dashboard
- ✅ Overview stats (users, bookings, revenue, pending)
- ✅ Approve / Reject bookings
- ✅ Reschedule bookings with date/time modal
- ✅ Mark bookings as complete
- ✅ Delete bookings
- ✅ Block / unblock / delete customers
- ✅ Add / edit / deactivate services
- ✅ Add (via URL) / edit / delete gallery images
- ✅ Approve / delete reviews
- ✅ View all payment records with revenue stats
- ✅ Send custom notifications (individual or broadcast)
- ✅ Edit homepage content dynamically

### Payment System
- ✅ Card number formatting (groups of 4)
- ✅ Expiry date validation
- ✅ CVV validation
- ✅ Expired card detection
- ✅ 90% success simulation (realistic testing)
- ✅ Transaction reference generation
- ✅ Payment records stored in database
- ✅ Automatic notification on success/failure

---

## 🛡️ SECURITY FEATURES

- Passwords hashed with PHP `password_hash()` (bcrypt)
- Session-based authentication
- Role-based access control (admin vs customer)
- SQL injection prevention via prepared statements & escaping
- XSS prevention via `htmlspecialchars()`
- Duplicate booking slot prevention

---

## 🎨 DESIGN SYSTEM

| Element      | Value                          |
|--------------|--------------------------------|
| Primary Font | Cormorant Garamond (headings)  |
| Body Font    | Montserrat                     |
| Gold Accent  | `#C9A84C`                      |
| Background   | `#0A0A0A` / `#111111`          |
| Text         | `#F8F6F1`                      |
| Theme        | Dark luxury with gold accents  |

---

## ❓ TROUBLESHOOTING

| Problem | Solution |
|---------|----------|
| Blank page | Enable PHP error reporting or check Apache error logs |
| DB connection failed | Ensure MySQL is running; verify db.php credentials |
| Images not loading | Images use Unsplash URLs — requires internet connection |
| Login not working | Re-import database.sql; check password hash |
| Permission errors | On Linux/Mac: `chmod -R 755 naps_photographers/` |

---

## 📞 SUPPORT

For any issues, check:
1. XAMPP error logs: `C:\xampp\apache\logs\error.log`
2. PHP error display: Add `ini_set('display_errors', 1);` to db.php temporarily
3. Browser DevTools Console for JS errors

---

*Naps Photographers Booking System — Built for academic excellence*
