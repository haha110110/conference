# GEMINI.md - Conference Manager Plugin

This project is a specialized **WordPress plugin** designed for mobile-first conference registration, multi-channel payments, and real-time on-site logistics management.

## 🚀 Project Overview

-   **Primary Goal:** Enable high-efficiency bulk registration and on-site check-in for conferences.
-   **Core Technologies:** PHP (WordPress), JavaScript, Vanilla CSS, REST API, WeChat Pay SDK.
-   **Architecture:**
    -   **Orders:** Managed via a Custom Post Type (`conf_order`).
    -   **Attendees:** Managed via a custom database table (`conf_attendees`) linked to orders, allowing one order to contain multiple attendees.
    -   **Staff Portals:** Standalone, lightweight PHP pages in the `/staff` directory for rapid on-site operations (Check-in and Material Distribution).
    -   **Payments:** Integrated with WeChat Pay (JSAPI/H5), Bank Transfer (manual approval), and Pay-on-site.
    -   **i18n:** Automatic and manual language switching (English and Simplified Chinese).

## 🛠 Building and Running

### Building
The project includes a shell script to package the plugin for distribution:
```bash
./build.sh
```
This generates a versioned `.zip` file (e.g., `conference-manager-v1.0.0.zip`) excluding development artifacts like `.git` and `docs`.

### Running / Installation
1.  Upload the generated `.zip` file via the WordPress Admin (Plugins -> Add New -> Upload).
2.  Activate the "Conference Manager" plugin.
3.  **Shortcode:** Use `[conf_registration]` on any WordPress page to display the registration form.
4.  **Staff Portals:**
    -   Check-in Portal: `{domain}/wp-content/plugins/conference-manager/staff/index.php`
    -   Material Desk: `{domain}/wp-content/plugins/conference-manager/staff/material-desk.php`
    *(Note: Users must have the `conference_staff` role and be logged in to access these).*

## 📂 Key Directory Structure

-   `/includes`: Core PHP classes (DB, Admin, Registration, REST API, WeChat Pay).
-   `/templates`: UI templates for frontend forms, order details, and emails.
-   `/staff`: Standalone portals for on-site staff operations.
-   `/assets`: Frontend CSS and JavaScript (React-less, mobile-optimized).
-   `/languages`: Translation files (`.po`/`.pot`).
-   `/docs`: Design specifications and implementation plans.

## ✍️ Development Conventions

-   **Coding Style:** Adhere to **WordPress Coding Standards**. Use `dash_case` for file names and `PascalCase` with prefixes for class names (e.g., `class-conf-manager.php` containing `Conf_Manager`).
-   **Internationalization (i18n):** Always use `__( 'Text', 'conf-manager' )` for strings. The text domain is `conf-manager`.
-   **Database:** Use `$wpdb` for all custom table operations. Table names are prefixed with `$wpdb->prefix . 'conf_'`.
-   **Security:**
    -   Always check `ABSPATH` or `WPINC` at the top of PHP files.
    -   Use `check_admin_referer()` and `wp_verify_nonce()` for form submissions and API calls.
    -   Sanitize all inputs (`sanitize_text_field`, etc.) and escape all outputs (`esc_html`, `esc_attr`).
-   **Hooks:** Use the `Conf_Manager` class to orchestrate hooks and initializations.

## 📝 TODO / Future Improvements
- [ ] Implement automated unit tests for the registration and refund logic.
- [ ] Add support for more payment gateways (e.g., Alipay, Stripe).
- [ ] Enhance the QR scanner with a more robust library if browser support is inconsistent.
- [ ] Improve the "Live Material Desk" with WebSockets or better polling optimization.
