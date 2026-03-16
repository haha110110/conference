# Design Specification: WordPress Conference Management Plugin (Mobile-First)

## 1. Overview
A specialized WordPress plugin for managing conference registrations, multi-channel payments (WeChat Pay, Bank Transfer, Pay-on-site), and real-time on-site check-in logistics. 

The plugin supports **Bulk Registration** (one user registering multiple attendees), **Staff-led Check-in** (via QR or manual search), and an **Automated Refund Workflow** for non-attendees.

## 2. Target Users
- **Main Registrant (User):** Creates an account, adds multiple attendees, pays for the group, and manages refunds.
- **Attendees:** The individuals actually attending the conference (may or may not be the same as the Registrant).
- **Staff (Check-in):** Use mobile/desktop portals to verify attendees via QR code, 6-digit code, phone, name, or company.
- **Staff (Material Desk):** View real-time check-in feed to distribute materials.
- **Administrators:** Configure tickets, WeChat Pay keys, approve manual payments, and process refund requests.

## 3. Core Functional Workflows

### 3.1. Bulk Registration & Payment
1.  **Account Creation:** Registrant creates a WP account.
2.  **Add Attendees:** Registrant can add multiple attendees (Name, Company, Job Title, Phone) to a single order.
3.  **Payment Selection:** One payment for the entire group order.
    *   **WeChat Pay:** Automatic status update to "Paid".
    *   **Bank Transfer:** Admin approval required.
    *   **Pay-on-site:** Status set to "Unpaid (On-site)".
4.  **Proof of Entry:**
    *   **QR Code:** Generated for **each attendee** in a paid order.
    *   **6-digit Code:** One unique code per **attendee** for on-site payment verification.

### 3.2. Staff Portals (Check-in & Verification)
-   **Mobile Scanner:** For rapid QR code scanning at the door.
-   **Desktop/Mobile Search Portal:** 
    *   Search by **Phone, 6-digit Code, Name, or Company**.
    *   **Payment Handling:** If status is "Unpaid (On-site)", staff confirms payment and the system logs which staff member processed the payment.
    *   **Check-in Action:** Updates attendee status to "Checked In" and triggers the Material Desk feed.

### 3.3. Refund Workflow (WeChat Pay only)
-   **Eligibility:** Only attendees who have **not** been checked in/confirmed on-site are eligible for a refund.
-   **Request:** Registrant selects specific attendees from their order to refund.
-   **Approval:** Admin reviews the request in the WP backend.
-   **Execution:** Upon approval, the plugin calls the WeChat Pay **Refund API** to automatically return the partial amount for the selected attendees to the original payment method.

### 3.4. Live Material Desk (Logistics Board)
-   **Real-time Feed:** New check-ins (from QR or Search) appear at the top.
-   **Action:** Mark "Materials Distributed" to complete the attendee's journey.

### 3.5. Management & Multi-language
-   **Email Integration:** "Order Received" and "Payment Confirmed" templates (via WP Mail SMTP).
-   **Multi-language:** Full UI and email support for **Chinese (Simplified)** and **English** by default.
-   **Admin Settings:** Configure WeChat Pay (AppID, MchID, API Keys) and Bank details.

## 4. Technical Architecture

### 4.1. Data Model
-   **`conf_orders` (CPT):** Represents the group transaction. Stores payment status, total amount, and Registrant ID.
-   **`conf_attendees` (Custom Table):** Linked to an `Order ID`. Stores individual info, unique QR/6-digit codes, Check-in status, Material status, and Refund status.
-   **`conf_transactions` (Custom Table):** Logs payment and refund details, including which staff member processed on-site payments.

### 4.2. API & Interfaces
-   **REST API:** Real-time communication for the Staff Search Portal and Material Desk.
-   **WeChat SDK:** Handles Pay and Refund API calls.
-   **QR Engine:** Generates unique codes for each `conf_attendee` record.

## 5. Security & Roles
-   **Staff Role:** Restricted access to Check-in Search and Material Desk (no full admin access).
-   **Refund Safety:** Double-checks "Checked In" status before allowing any refund request or execution.
-   **Transaction Logging:** Every status change (Payment/Check-in/Refund) is logged with a timestamp and User/Staff ID.
