# WidePrep website — notes for Claude

PHP 8 + MySQL site (no framework, no build step) on Hostinger Premium shared hosting.
Every push to `main` is published to https://wideprep.in by Hostinger Git auto-deploy.
The site owner is not a developer: explain changes in plain language and check pages at
phone and desktop width before pushing.

## How it's organised
- Pages: `index.php`, `course.php` (/courses/{slug}), `contact.php`, `thank-you.php`, legal pages
  (`terms`, `privacy`, `refund-policy`, `delivery-policy`), `admin/` (owner-only dashboard).
  Clean URLs come from `.htaccess`.
- `inc/` (blocked from the web): `bootstrap.php` (settings, DB, helpers), `courses.php`
  (catalogue + prices in paise — prices are server-side only), `orders.php` (Razorpay + fulfilment),
  `mailer.php` (PHPMailer, vendored in `inc/lib`), `layout.php` (header/footer).
- `api/`: `create-order`, `verify-payment` (HMAC signature check), `razorpay-webhook`, `enquiry`.
- DB tables `orders` and `enquiries` are created automatically on first use (`ensure_schema`).

## Secrets — never commit them (the repo is public)
- Production settings live in `wideprep.env` ONE FOLDER ABOVE `public_html` on Hostinger
  (`/home/u836859130/domains/wideprep.in/wideprep.env`). Upload/replace it with the Hostinger file
  upload (TUS) to path `wideprep.env` (not under public_html). Keys: RAZORPAY_KEY_ID/SECRET,
  RAZORPAY_WEBHOOK_SECRET, DB_*, SMTP_*, MAIL_FROM/NOTIFY, ADMIN_PASSWORD_HASH, ACCESS_LINK_<SLUG>.
- Local development: `.env` in the project root (git-ignored); `DB_DSN=sqlite:...` and
  `MAIL_DISABLED=1` are handy for testing with `php -S`.
- Course joining links are secret (ACCESS_LINK_*), never put them in `inc/courses.php`.

## Brand (from the WidePrep brand kit, v1 Nov 2026 — guide: assets/brand/wideprep-brand-guide.png)
- Colours: Petrol `#0B4F6C` (main: headings, body text, buttons), Mint `#20BF9B` (accent/highlights),
  Bright mint `#3EE0BA` (replaces mint on dark backgrounds only), White `#FFFFFF`.
  Never put small text in mint on white — too light to read.
- Font: Nunito (Google Fonts). Headings 800 (ExtraBold); body 400 or 600.
- Logo: always use the supplied files, never retype "WidePrep" as text in place of the logo.
  - Light backgrounds: `assets/brand/wideprep-logo-color.svg`
  - Petrol/dark backgrounds: `assets/brand/wideprep-logo-dark.svg` (or `-white.svg`)
  - Small sizes (< 120px wide): WP icon `assets/brand/wideprep-icon-color.svg` / `-dark.svg`
  - Clear space around the logo = height of the "W". Don't stretch, rotate, recolour, add shadows/outlines,
    or place on busy photos.
- Favicons and link preview live in the site root: favicon.ico, favicon.svg, apple-touch-icon-180x180.png,
  android-chrome-192x192.png / -512x512.png, og-link-preview-1200x630.png.
- The full kit (print PDFs, social profile and cover images) is not in this repo; ask the owner for it if needed.
