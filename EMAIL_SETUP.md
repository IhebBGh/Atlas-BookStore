# 📧 Email Notification Setup Guide

## How to View Email Notifications

### Option 1: File Transport (Recommended for Development) ✅

Emails are automatically saved to files in `var/mail/` directory. You can view them in two ways:

#### Method A: Web Interface
1. Go to Admin Panel → **View Emails** (in admin dropdown)
2. Or visit: `http://localhost/your-project/admin/emails`
3. All sent emails will be displayed in a nice web interface

#### Method B: Direct File Access
1. Navigate to `var/mail/` folder in your project
2. Open any `.txt` file to view the email content
3. Files are named with timestamps

### Option 2: Mailpit (If Using Docker)

If you're using Docker, Mailpit is already configured:
1. Start Docker: `docker-compose up -d`
2. Access Mailpit at: `http://localhost:8025`
3. All emails will appear in the Mailpit interface

### Option 3: Real SMTP (For Production)

For production, configure real SMTP in `.env`:

```env
MAILER_DSN=smtp://username:password@smtp.example.com:587
```

## Current Configuration

**Development Mode:** Emails are saved to `var/mail/` as files
**Production Mode:** Uses MAILER_DSN from `.env` file

## Testing Email Notifications

1. **Order Confirmation Email:**
   - Add products to cart
   - Go to checkout
   - Complete an order
   - Email will be saved to `var/mail/`

2. **Status Update Email:**
   - Go to Admin → Orders
   - Update an order status
   - Email will be sent to customer

## Viewing Emails

### Quick Access:
- **Admin Panel:** Admin dropdown → View Emails
- **Direct URL:** `/admin/emails`
- **File Location:** `var/mail/*.txt`

### Email Format:
Each email file contains:
- To: recipient email
- From: sender email  
- Subject: email subject
- Body: HTML email content

## Troubleshooting

**No emails appearing?**
- Check that `var/mail/` directory exists and is writable
- Make sure you've placed an order or updated order status
- Check Symfony logs: `var/log/dev.log`

**Want to use real email?**
- Update `.env` with your SMTP settings
- Or use services like Mailtrap for testing

## Email Types

1. **Order Confirmation** - Sent when order is placed
2. **Status Update** - Sent when admin updates order status

Both emails include:
- Order details
- Customer information
- Order items
- Total amount
- Status information

