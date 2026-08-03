# Changelog

All notable changes to **My Clients** are documented in this file.

The version source of truth is `VERSION` (mirrored in `cms/VERSION`).  
Landing page and `changelog.php` read the same value.

Format inspired by [Keep a Changelog](https://keepachangelog.com/).  
Versioning follows [Semantic Versioning](https://semver.org/).

---

## [1.0.0] — 2026-08-03

First public open-source release on GitHub: [Ruslan-Bilohash/myclient](https://github.com/Ruslan-Bilohash/myclient).

### Added

#### Product landing (`/myclient/`)
- Multi-language SEO landing: **UK, RU, EN, NO, SV, PL**
- Feature sections: clients, services, client cabinet, Invoice, tax, payments
- Screenshot gallery with lightbox (WebP + thumbnails)
- Schema.org: SoftwareApplication, FAQPage, ImageGallery, BreadcrumbList
- Open Graph / Twitter cards
- Public changelog page (`changelog.php`)
- `llms.txt` context for AI tools
- Sitemap + robots

#### Admin (CMS)
- Client management (CRUD): name, email, company, site, domain, language, status, notes, cabinet password
- **My services** catalog: icons, multi-lang names, prices for 1 / 3 / 12 months
- **Invoice** create / list / view / edit lines / mark paid / cancel / resend email
- Company seller details for PDF / receipts
- Manual FX rates (display approx.)
- Tax toggle + percent (VAT / MVA / PVM / ПДВ model)
- Stripe Checkout + PayPal REST API keys in settings
- Dashboard overview

#### Client cabinet
- Separate client login
- Active services overview
- Invoice list, pay online, PDF download
- Profile / password settings

#### Technical
- PHP 8+, JSON file storage (no heavy framework / no MySQL required)
- CSRF protection on forms
- `data/` protected with `.htaccess`
- PDF invoices with embedded DejaVu fonts (Unicode)
- Clean install wizard (`install.php` / `install.php.off` on demo)
- Demo portal one-click admin/client entry
- Demo data included for portfolio use

### Security
- No live payment secrets in public demo config
- Empty Stripe / PayPal keys by default — set in Admin → Settings
- Data directory not web-listable

### Demo
- Live: https://bilohash.com/myclient/
- Portal: https://bilohash.com/myclient/cms/portal.php
- Admin: `demo` / `demo`
- Client: `anna.berg@demo.myclient` / `demo`
- Portfolio demo only — not real billing or real payments

### Documentation
- `README.md` (English, primary)
- `readme-ua.md`, `readme-ru.md`, `readme-no.md`, `readme-lt.md`
- Full screenshot set in `screenshot/`
- Curated images in `docs/screenshots/`

---

## Links

| Resource | URL |
|----------|-----|
| Landing | https://bilohash.com/myclient/ |
| Demo portal | https://bilohash.com/myclient/cms/portal.php |
| Install | https://bilohash.com/myclient/cms/install.php |
| Changelog page | https://bilohash.com/myclient/changelog.php |
| Donate / Support | https://bilohash.com/donate.php |
| BILOHASH | https://bilohash.com/ |

[1.0.0]: https://github.com/Ruslan-Bilohash/myclient/releases/tag/v1.0.0
