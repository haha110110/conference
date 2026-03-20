# GEMINI.md - Conference Manager Plugin

This project is a specialized **WordPress plugin** designed for mobile-first conference registration, multi-channel payments, and real-time on-site logistics management.

## 🚀 Project Overview

-   **Primary Goal:** Enable high-efficiency bulk registration and on-site check-in for conferences.
-   **Core Technologies:** PHP (WordPress), JavaScript, Vanilla CSS, REST API, WeChat Pay SDK.
-   **Architecture:**
    -   **Orders:** Managed via a Custom Post Type (`conf_order`).
    -   **Attendees:** Managed via a custom database table (`conf_attendees`) linked to orders.
    -   **Staff Portals:** Standalone, lightweight PHP pages in the `/staff` directory.
    -   **Payments:** Integrated with WeChat Pay (JSAPI/H5), Bank Transfer, and Pay-on-site.

## 🧩 Core Development Principles

-   **Strict Decoupling (极致解耦):**
    -   **UI vs. Logic:** Keep presentation templates (`/templates`) clean of complex business logic.
    -   **Database Abstraction:** All SQL operations must reside in the `Conf_DB` class. Never write raw SQL in templates or registration logic.
    -   **Payment Abstraction:** Payment gateways should be modular. The registration flow should not depend on the internal implementation of a specific payment method.
-   **Boundary Awareness (边界意识):**
    -   **Plugin Scope:** Only modify files within the plugin directory. Never touch WordPress core or other plugins.
    -   **Namespace Protection:** All CSS classes, JS global variables, and PHP functions/classes must be prefixed with `conf_` or `Conf_` to prevent collisions.
-   **UI Consistency (视觉一致性):**
    -   **Design System:** Adhere to the Tailwind-based configuration found in the modern UI prototypes.
    -   **Component Reuse:** Reuse existing patterns for headers, sidebars, and form inputs across all new admin and frontend pages.
-   **Security First:**
    -   **Input/Output:** Sanitize all inputs (`sanitize_text_field`, etc.) and escape all outputs (`esc_html`, `esc_attr`).
    -   **Verification:** Always use nonces and permission checks (`current_user_can`) for administrative actions and API calls.

## 📂 Key Directory Structure

-   `/includes`: Core PHP classes (Logic, DB, API, Payments).
-   `/templates`: UI templates for frontend and backend.
-   `/staff`: Standalone portals for on-site operations.
-   `/assets`: Frontend CSS and JS (Namespace-protected).
-   `/languages`: i18n translation files.
-   `/ui-prototypes`: (New) Modern HTML/Tailwind design references.

## ✍️ Development Conventions

-   **Coding Style:** Follow **WordPress Coding Standards**. Use `dash_case` for filenames and `PascalCase` with prefixes for classes (e.g., `class-conf-manager.php`).
-   **i18n:** Always use `__( 'Text', 'conf-manager' )`.
-   **Database:** Use `$wpdb` with the `conf_` prefix for custom tables.
-   **Hooks:** Orchestrate all initializations via the main `Conf_Manager` class.

## 📝 TODO / Future Improvements
- [ ] Implement automated unit tests for registration and refund logic.
- [ ] Harmonize existing admin pages with the new Tailwind-based UI design.
- [ ] Add support for more payment gateways (e.g., Alipay, Stripe).
- [ ] Enhance the QR scanner robustness.
