# Мої клієнти (My Clients)

**PHP-продукт для управління клієнтами, каталогу послуг, рахунків Invoice, податків (ПДВ / VAT / MVA / PVM), онлайн-оплати (Stripe і PayPal) та кабінету клієнта.**

| | |
|---|---|
| **Лендинг** | https://bilohash.com/myclient/?lang=uk |
| **Демо** | https://bilohash.com/myclient/cms/portal.php |
| **Версія** | **1.0.0** |
| **Автор** | [BILOHASH](https://bilohash.com/) |

### Інші мови
- [English (основний README)](README.md)
- [Українська (цей файл)](readme-ua.md)
- [Русский](readme-ru.md)
- [Norsk](readme-no.md)
- [Lietuvių](readme-lt.md)

---

## Скріншоти

| Кабінет клієнта | Клієнти (адмін) | Рахунок |
|:---:|:---:|:---:|
| ![Кабінет](docs/screenshots/client_cabinet.webp) | ![Клієнти](docs/screenshots/admin_client_list.webp) | ![Invoice](docs/screenshots/admin_invoice.webp) |

Повний набір: [`screenshot/`](screenshot/) · [галерея на сайті](https://bilohash.com/myclient/?lang=uk#shots)

---

## Можливості

### Адмінка
- **Клієнти** — CRUD, компанія, сайт/домен, мова, статус, нотатки, пароль кабінету
- **Мої послуги** — каталог, іконки, ціни на **1 / 3 / 12 місяців**, багатомовні назви
- **Invoice** — рядки, податок, статуси pending / paid / cancelled, email, PDF
- **Реквізити продавця** — для PDF / квитанцій
- **Податок** — увімкнення % (ПДВ / VAT / MVA / PVM)
- **Оплата** — Stripe Checkout і PayPal REST (ключі в налаштуваннях)

### Кабінет клієнта
- Окремий вхід
- Активні послуги
- Список рахунків → оплата онлайн → PDF
- Профіль / пароль

### Лендинг
- SEO: UK, RU, EN, NO, SV, PL
- Schema.org, Open Graph, lightbox скрінів
- FAQ, модулі, демо-посилання

---

## Демо-доступ

> Публічне **портфоліо-демо**. Не реальний білінг і не реальні платежі.

| Роль | Логін | Пароль |
|------|--------|--------|
| Адмін | `demo` | `demo` |
| Клієнт | `anna.berg@demo.myclient` | `demo` |

Портал: https://bilohash.com/myclient/cms/portal.php

---

## Вимоги

- PHP **8.0+**
- `json`, `mbstring`, запис у `cms/data/`
- База даних **не потрібна** (JSON-файли)

---

## Швидке встановлення

1. Завантажте папку `cms/` (або весь репозиторій) на хостинг.
2. Зробіть `cms/data/` доступним для запису та закритим від лістингу.
3. Відкрийте майстер install (якщо увімкнено) або змініть пароль демо-адміна.
4. **Адмін → Налаштування**: компанія, податок, Stripe / PayPal.
5. За потреби: `config.local.example.php` → `config.local.php`.

---

## Структура

```text
myclient/
├── index.php           # SEO-лендинг
├── screenshot/         # Скріншоти UI
├── docs/screenshots/   # Зображення для README
├── CHANGELOG.md
├── VERSION
└── cms/                # Додаток (адмін + кабінет)
```

---

## Changelog і релізи

- [CHANGELOG.md](CHANGELOG.md)
- https://bilohash.com/myclient/changelog.php?lang=uk
- https://github.com/Ruslan-Bilohash/myclient/releases

---

## Відмова від відповідальності

Репозиторій містить **публічне портфоліо-демо**. Дані вигадані. Не використовуйте демо-платежі як бойовий білінг, доки не налаштуєте власні ключі та реквізити юрособи.
