# WordPress Conference Management Plugin Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a robust WordPress plugin for managing conference registrations with bulk entry, WeChat Pay, real-time staff check-in, and automated refunds.

**Architecture:** A decoupled WordPress plugin using custom database tables and the REST API for performance. It features a "Mobile-First" staff portal independent of the site theme.

**Tech Stack:** PHP (WordPress), MySQL, JavaScript (Vanilla for the staff portal), WeChat Pay SDK (Official).

---

## Chunk 1: Foundation & Database

### Task 1: Plugin Scaffold & Staff Role
**Files:**
- Create: `conference-manager.php`
- Create: `includes/class-conf-manager.php`
- Create: `languages/conf-manager-en_US.pot`

- [ ] **Step 1: Create the main plugin file and register 'Staff' role**
Include `add_role( 'conference_staff', 'Conference Staff' )` on activation.
- [ ] **Step 2: Initialize the main class with i18n support**
- [ ] **Step 3: Commit foundation**
Run: `git add . && git commit -m "chore: initial plugin scaffold and staff role registration"`

### Task 2: Custom Database Tables
**Files:**
- Create: `includes/class-conf-db.php`

- [ ] **Step 1: Create the DB management class**
Define `wp_conf_attendees` with columns: `id`, `order_id`, `name`, `phone`, `company`, `job_title`, `six_digit_code`, `qr_code_url`, `checkin_status`, `material_status`, `refund_status`.
- [ ] **Step 2: Hook activation to create tables**
- [ ] **Step 3: Commit DB layer**
Run: `git add . && git commit -m "feat: add custom database tables for orders and attendees"`

---

## Chunk 2: Admin Settings & Registration

### Task 3: Admin Settings UI (WeChat & Tickets)
**Files:**
- Create: `includes/class-conf-admin.php`
- Create: `templates/admin-settings.php`

- [ ] **Step 1: Build Settings page for WeChat Pay Credentials** (AppID, MchID, API Key, Certs).
- [ ] **Step 2: Build Ticket Configuration** (Ticket Name, Price).
- [ ] **Step 3: Build Bank Transfer details** (Account Name, Number, Bank).
- [ ] **Step 4: Commit Admin Settings**
Run: `git add . && git commit -m "feat: add admin settings for WeChat Pay and ticket configuration"`

### Task 4: Bulk Registration Form (Multi-Payment)
**Files:**
- Create: `includes/class-conf-registration.php`
- Create: `templates/registration-form.php`

- [ ] **Step 1: Implement the multi-attendee form**
Each attendee row MUST collect: **Name, Phone, Company, Job Title** (mirrors the registrant's registration fields).
- [ ] **Step 2: Add payment selection** (WeChat Pay, Bank Transfer, Pay-on-site).
- [ ] **Step 3: Handle form submission and code generation** (6-digit unique code per attendee).
- [ ] **Step 4: Include QR Code library** (e.g., PHPQRCode) and generate QR for paid attendees.
- [ ] **Step 5: Commit registration**
Run: `git add . && git commit -m "feat: implement bulk registration with three payment options and detailed attendee fields"`

---

## Chunk 3: Payments & Approval

### Task 5: WeChat Pay & Manual Approval
**Files:**
- Create: `includes/class-conf-wechat-pay.php`
- Modify: `includes/class-conf-admin.php`

- [ ] **Step 1: Implement WeChat Pay JSAPI/H5 flows and callback listener.**
- [ ] **Step 2: Build Bank Receipt upload and Admin Approval UI.**
- [ ] **Step 3: Commit payment flows**
Run: `git add . && git commit -m "feat: implement WeChat Pay integration and bank transfer approval"`

---

## Chunk 4: Staff Portals

### Task 6: Staff Check-in (Scanner & Search)
**Files:**
- Create: `staff/index.php`
- Create: `includes/class-conf-rest-api.php`

- [ ] **Step 1: Create REST API endpoints for check-in and search.**
- [ ] **Step 2: Build the Search Portal** (Search by Phone, Name, 6-digit Code, or Company).
- [ ] **Step 3: Implement QR Scanner** (Using browser camera).
- [ ] **Step 4: Handle "Pay-on-site" confirmation** (Log the staff member who collects the payment).
- [ ] **Step 5: Commit check-in portal**
Run: `git add . && git commit -m "feat: build mobile-friendly staff search and scanner portal"`

### Task 7: Live Material Desk
**Files:**
- Create: `staff/material-desk.php`

- [ ] **Step 1: Build the real-time feed for newest check-ins.**
- [ ] **Step 2: Add "Confirm Material Distribution" tracking.**
- [ ] **Step 3: Commit material desk**
Run: `git add . && git commit -m "feat: add real-time material distribution tracking"`

---

## Chunk 5: Final Logistics

### Task 8: Refunds & Email Templates
**Files:**
- Modify: `includes/class-conf-wechat-pay.php`
- Create: `templates/emails/`

- [ ] **Step 1: Implement WeChat Pay Refund API logic for non-attendees.**
- [ ] **Step 2: Build custom email templates** ("Order Received", "Payment Confirmed").
- [ ] **Step 3: Final Verification & Commit**
Run: `git add . && git commit -m "feat: add refund logic and customizable email templates"`
