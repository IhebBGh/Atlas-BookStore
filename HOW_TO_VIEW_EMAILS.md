# 📧 How to View Email Notifications

## ✅ Email Configuration Fixed!

The mailer is now configured to save emails to files in development mode.

## 🎯 Quick Access Methods

### Method 1: Web Interface (Easiest) ⭐

1. **Log in as Admin**
2. **Click "Admin" dropdown** in the navbar
3. **Click "View Emails"**
4. **Or visit directly:** `http://localhost/projec/admin/emails`

You'll see:
- ✅ All sent emails
- ✅ Email subject, recipient, sender
- ✅ Full HTML email content
- ✅ Raw email view (toggle button)
- ✅ Most recent emails first

### Method 2: File Browser

1. **Navigate to:** `var/mail/` folder in your project
2. **Open any `.txt` file** to view the email
3. Files are automatically created when emails are sent

## 📬 When Emails Are Created

Emails are automatically saved when:

1. **Order Confirmation:**
   - User completes checkout
   - Order is created
   - Email saved to `var/mail/`

2. **Status Update:**
   - Admin updates order status
   - Email sent to customer
   - Email saved to `var/mail/`

## 🧪 Testing Email Notifications

### Test Order Confirmation:
1. Add products to cart
2. Go to checkout (`/checkout`)
3. Fill in shipping address
4. Select payment method
5. Click "Place Order"
6. **Email is automatically saved!**
7. Go to Admin → View Emails to see it

### Test Status Update:
1. Go to Admin → Orders
2. Click on any order
3. Change the status (e.g., from "pending" to "processing")
4. Click "Update Status"
5. **Email is automatically saved!**
6. Go to Admin → View Emails to see it

## 📁 Email File Location

- **Directory:** `var/mail/`
- **Format:** `.txt` files
- **Naming:** Auto-generated with timestamps
- **Content:** Full email including headers and HTML body

## 🔍 Email Viewer Features

The web interface (`/admin/emails`) provides:
- 📋 List of all emails (last 50)
- 👁️ View full email content
- 🔍 Show/Hide raw email
- 🔄 Refresh button
- 📅 Email dates and metadata
- 🎨 Clean, readable interface

## ⚙️ Configuration

**Current Setup:**
- **Development:** Emails saved to `var/mail/` (file transport)
- **Production:** Can be configured to use real SMTP

**File Location:** `var/mail/` (automatically created)

## 🚀 Quick Test

Try this now:
1. Place a test order
2. Go to `/admin/emails`
3. See your order confirmation email!

---

**Note:** In development, emails are NOT actually sent. They're saved as files so you can view and test them without needing a real email server.

