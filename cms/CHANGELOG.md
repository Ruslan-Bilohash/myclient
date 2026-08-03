# My Clients — Changelog

All notable changes to this product are documented here.  
Version source of truth: `VERSION` (read by CMS `config.php` and landing page).

Full project changelog: [../CHANGELOG.md](../CHANGELOG.md)

## [1.0.0] — 2026-08-03

### Added
- Client management (CRUD): company, site, domain, language, status, notes
- **My Clients** client cabinet
- **My services** catalog with 1 / 3 / 12 month pricing
- **Invoice** billing (pending / paid / cancelled) with line items
- Tax settings for invoice totals (VAT / MVA / PVM / ПДВ model)
- Online payment hooks: Stripe Checkout + PayPal REST (keys in Admin → Settings)
- PDF invoice receipts (DejaVu fonts)
- Multi-language UI: UK, NO, EN
- Clean **install** wizard (empty data + first admin setup)
- Demo portal with one-click Admin / Client login
- Product landing with SEO, Schema.org, Open Graph, screenshot lightbox
- Public GitHub repository with multi-language README set

### Security
- No payment secrets in public demo config
- Data directory protected with `.htaccess`
- CSRF on forms

### Demo
- Public portfolio demo only — not a real billing service
- Admin: `demo` / `demo` · Client: `anna.berg@demo.myclient` / `demo`

---

Links: [Landing](https://bilohash.com/myclient/) · [Demo](https://bilohash.com/myclient/cms/portal.php) · [GitHub](https://github.com/Ruslan-Bilohash/myclient)
