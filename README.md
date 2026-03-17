# Conference Manager for WordPress

A specialized, mobile-first WordPress plugin designed for high-efficiency conference registration, multi-channel payments, and real-time on-site logistics management.

## 🚀 Key Features

### 1. Registration & Bulk Entry
- **Bulk Registration:** One user can register and pay for multiple attendees in a single transaction.
- **Detailed Attendee Profiles:** Collects Name, Phone, Company, and Job Title for every individual attendee.
- **Automated Verification:** Generates a unique 6-digit verification code and a QR code for every attendee.

### 2. Multi-Channel Payment System
- **WeChat Pay:** Fully integrated support for **JSAPI** (inside WeChat) and **H5** (standard mobile browsers).
- **Bank Transfer:** Supports receipt image uploads and manual admin approval in the backend.
- **Pay-on-Site:** Allows attendees to register online and pay cash/POS at the venue.

### 3. Staff Logistics Portals
- **Staff Search Portal:** A mobile-friendly search engine to verify attendees by Phone, Name, or Code.
- **QR Scanner:** Integrated browser-based camera scanning for rapid entry.
- **On-site Payment Log:** Staff can collect pending payments on-site and the system logs the staff ID responsible.
- **Live Material Desk:** A real-time dashboard that updates instantly when an attendee checks in, helping staff distribute conference materials (badges, bags, etc.).

### 4. Advanced Multi-language Support
- **Automatic Detection:** Detects the user's browser language (English/Chinese) and switches the UI automatically.
- **Manual Switch:** Users can manually toggle between English and Simplified Chinese via a frontend switcher.
- **Admin Default:** Administrators can set a default plugin language in the settings.

### 5. Management & Refunds
- **Automated Refunds:** Partial refund support for non-attendees via the WeChat Pay Refund API.
- **Email Notifications:** Customizable templates for "Order Received" and "Payment Confirmed," compatible with **WP Mail SMTP**.

---

## 🛠 Installation Guide

### Prerequisites
- WordPress 5.0 or higher.
- PHP 7.4 or higher.
- **WP Mail SMTP** plugin (Recommended for reliable email delivery).
- A valid **WeChat Merchant Account** (for WeChat Pay features).

### Steps
1. **Upload the Plugin:**
   - Compress the `conference-manager` folder into a `.zip` file.
   - Go to your WordPress Admin -> **Plugins** -> **Add New** -> **Upload Plugin**.
   - Activate the plugin.

2. **Install Dependencies:**
   ```bash
   # Navigate to plugin directory
   cd /path/to/wp-content/plugins/conference
   
   # Install Composer dependencies
   composer install
   ```

3. **Configure WeChat Pay Certificates:**
   - Download your merchant certificates from WeChat Merchant Platform:
     - `apiclient_cert.pem`
     - `apiclient_key.pem`
   - Upload these files to the plugin directory: `/includes/certs/`
   - Configure the certificate paths in WordPress admin settings

4. **Register Staff Members:**
   - Create new WordPress users for your venue staff.
   - Assign them the **"Conference Staff"** role created by the plugin.

5. **Configure Settings:**
   - Navigate to the new **Conference** menu in your sidebar -> **Settings**.
   - Input your **WeChat Pay credentials**:
     - `conf_wechat_appid` - WeChat AppID
     - `conf_wechat_mchid` - Merchant ID
     - `conf_wechat_key` - API Key
     - `conf_wechat_cert_path` - Certificate file path (e.g., `certs/apiclient_cert.pem`)
     - `conf_wechat_key_path` - Key file path (e.g., `certs/apiclient_key.pem`)
   - Set your **Ticket Name** and **Price**.
   - Enter your **Bank Account Details** for manual transfers.
   - Select your **Default Language**.

6. **Deploy the Registration Form:**
   - Create a new Page in WordPress.
   - Insert the shortcode: `[conf_registration]`.

7. **Access Staff Portals:**
   - **Check-in Portal:** `yourdomain.com/wp-content/plugins/conference-manager/staff/index.php`
   - **Material Desk:** `yourdomain.com/wp-content/plugins/conference-manager/staff/material-desk.php`
   *(Staff must be logged in to access these URLs)*

---

## ⚠️ Important Notes

### WeChat Pay Configuration
1. **HTTPS Required:** Your site must use HTTPS for WeChat Pay to work.
2. **Callback URL:** The payment callback URL is: `/wp-json/conf-manager/v1/wechat-callback`
3. **Merchant Verification:** Ensure your WeChat Merchant Account has enabled the appropriate payment methods:
   - **Native Pay** (QR Code) - for PC
   - **H5 Pay** - for mobile browsers
4. **IP Whitelist:** Add your server IP to the WeChat Merchant Platform allowed list.
5. **Certificate Security:** Keep your certificate files secure. Do not commit them to version control.

### Server Requirements
- PHP `curl` extension
- PHP `mbstring` extension
- PHP `openssl` extension
- Server must have outbound access to `api.mch.weixin.qq.com`

### Payment Flow
- **PC (Native):** User scans QR code with WeChat app → Payment → Auto-redirect
- **Mobile (H5):** User clicks pay → Redirects to WeChat → Payment → Returns to site

---

## 📂 Project Structure
- `/assets`: Frontend JavaScript and CSS.
- `/includes`: Core PHP classes for DB, Admin, Registration, and API logic.
- `/languages`: Translation files (.po/.pot).
- `/staff`: Standalone lightweight portals for venue operations.
- `/templates`: HTML templates for forms and emails.
