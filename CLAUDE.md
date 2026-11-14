# WidePrep website — notes for Claude

Static site (plain HTML/CSS/JS, no build step). Every push to `main` is published to
https://wideprep.in by Hostinger Git auto-deploy. The site owner is not a developer:
explain changes in plain language and check pages at phone and desktop width before pushing.

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
