# Mine kunder (My Clients)

**PHP-produkt for kundeadministrasjon, tjenestekatalog, Invoice-fakturering, avgift (MVA / VAT / PVM), nettbetaling (Stripe og PayPal) og kundekabinett.**

| | |
|---|---|
| **Landing** | https://bilohash.com/myclient/?lang=no |
| **Demo** | https://bilohash.com/myclient/cms/portal.php |
| **Versjon** | **1.0.0** |
| **Forfatter** | [BILOHASH](https://bilohash.com/) |
| **Doner / støtte** | https://bilohash.com/donate.php |

### Andre språk
- [English (hoved-README)](README.md)
- [Українська](readme-ua.md)
- [Русский](readme-ru.md)
- [Norsk (denne filen)](readme-no.md)
- [Lietuvių](readme-lt.md)

---

## Skjermbilder

| Kundekabinett | Kunder (admin) | Faktura |
|:---:|:---:|:---:|
| ![Kabinett](docs/screenshots/client_cabinet.webp) | ![Kunder](docs/screenshots/admin_client_list.webp) | ![Invoice](docs/screenshots/admin_invoice.webp) |

Fullt sett: [`screenshot/`](screenshot/) · [galleri](https://bilohash.com/myclient/?lang=no#shots)

---

## Muligheter

### Admin
- **Kunder** — CRUD, firma, nettsted/domene, språk, status, notater, kabinettpassord
- **Mine tjenester** — katalog, ikoner, priser for **1 / 3 / 12 måneder**, flerspråklige navn
- **Invoice** — linjer, avgift, status pending / paid / cancelled, e-post, PDF
- **Selgerdetaljer** — for PDF / kvittering
- **Avgift** — prosent (MVA / VAT / PVM)
- **Betaling** — Stripe Checkout og PayPal REST (nøkler i innstillinger)

### Kundekabinett
- Egen innlogging
- Aktive tjenester
- Fakturaliste → betal online → last ned PDF
- Profil / passord

### Landingsside
- SEO: UK, RU, EN, NO, SV, PL
- Schema.org, Open Graph, screenshot-lightbox
- FAQ, moduler, demolinker

---

## Demoinnlogging

> Offentlig **portefølje-demo**. Ikke ekte fakturering eller betalinger.

| Rolle | Brukernavn | Passord |
|-------|------------|---------|
| Admin | `demo` | `demo` |
| Kunde | `anna.berg@demo.myclient` | `demo` |

Portal: https://bilohash.com/myclient/cms/portal.php

---

## Krav

- PHP **8.0+**
- `json`, `mbstring`, skrivbar `cms/data/`
- **Ingen database** (JSON-filer)

---

## Rask installasjon

1. Last opp `cms/` (eller hele repoet) til hostingen.
2. Gjør `cms/data/` skrivbar og stengt for directory listing.
3. Kjør install-veiviser (hvis aktiv) eller bytt demo-admin-passord.
4. **Admin → Innstillinger**: firma, avgift, Stripe / PayPal.
5. Valgfritt: `config.local.example.php` → `config.local.php`.

---

## Struktur

```text
myclient/
├── index.php           # SEO-landing
├── screenshot/         # UI-skjermbilder
├── docs/screenshots/   # README-bilder
├── CHANGELOG.md
├── VERSION
└── cms/                # App (admin + kabinett)
```

---

## Endringslogg og utgivelser

- [CHANGELOG.md](CHANGELOG.md)
- https://bilohash.com/myclient/changelog.php?lang=no
- https://github.com/Ruslan-Bilohash/myclient/releases

---

## Doner

Støtt forfatteren: https://bilohash.com/donate.php

---

## Ansvarsfraskrivelse

Dette repositoriet inneholder en **offentlig portefølje-demo**. Data er fiktive. Ikke bruk demobetalinger som produksjonsfakturering før du har satt egne nøkler og firmadetaljer.
