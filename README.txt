ONLINE CLOTHING STORE - LE5 SECURE WEB SYSTEM

SETUP (XAMPP)
1. Copy this folder into C:\xampp\htdocs\online-clothing-store\
2. Start Apache and MySQL in XAMPP.
3. Open phpMyAdmin and import database.sql.
4. Confirm includes/config.php uses the correct MySQL username/password.
5. Visit http://localhost/online-clothing-store/index.php

SECURITY FEATURES INCLUDED
- PHP sessions for authentication and cart state
- Session regeneration after login/registration
- HttpOnly + SameSite session cookies, Secure automatically when HTTPS is used
- Non-sensitive preferred-category cookie
- Server-side validation for registration, login, filters, sizes and quantities
- PDO prepared statements
- password_hash() and password_verify()
- CSRF tokens for state-changing forms
- Server-side cart price recalculation
- Checkout transaction and stock checks
- Login-only checkout/order history
- Admin-only dashboard/inventory pages

ADMIN ACCOUNT
Register normally, then run this in phpMyAdmin:
UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
