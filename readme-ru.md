# Мои клиенты (My Clients)

**PHP-продукт для управления клиентами, каталога услуг, счетов Invoice, налогов (НДС / VAT / MVA / PVM), онлайн-оплаты (Stripe и PayPal) и кабинета клиента.**

| | |
|---|---|
| **Лендинг** | https://bilohash.com/myclient/?lang=ru |
| **Демо** | https://bilohash.com/myclient/cms/portal.php |
| **Версия** | **1.0.0** |
| **Автор** | [BILOHASH](https://bilohash.com/) |

### Другие языки
- [English (основной README)](README.md)
- [Українська](readme-ua.md)
- [Русский (этот файл)](readme-ru.md)
- [Norsk](readme-no.md)
- [Lietuvių](readme-lt.md)

---

## Скриншоты

| Кабинет клиента | Клиенты (админ) | Счёт |
|:---:|:---:|:---:|
| ![Кабинет](docs/screenshots/client_cabinet.webp) | ![Клиенты](docs/screenshots/admin_client_list.webp) | ![Invoice](docs/screenshots/admin_invoice.webp) |

Полный набор: [`screenshot/`](screenshot/) · [галерея](https://bilohash.com/myclient/?lang=ru#shots)

---

## Возможности

### Админка
- **Клиенты** — CRUD, компания, сайт/домен, язык, статус, заметки, пароль кабинета
- **Мои услуги** — каталог, иконки, цены на **1 / 3 / 12 месяцев**, мультиязычные названия
- **Invoice** — строки, налог, статусы pending / paid / cancelled, email, PDF
- **Реквизиты продавца** — для PDF / квитанций
- **Налог** — процент (НДС / VAT / MVA / PVM)
- **Оплата** — Stripe Checkout и PayPal REST (ключи в настройках)

### Кабинет клиента
- Отдельный вход
- Активные услуги
- Список счетов → оплата онлайн → PDF
- Профиль / пароль

### Лендинг
- SEO: UK, RU, EN, NO, SV, PL
- Schema.org, Open Graph, lightbox скриншотов
- FAQ, модули, ссылки на демо

---

## Демо-доступ

> Публичное **портфолио-демо**. Не реальный биллинг и не реальные платежи.

| Роль | Логин | Пароль |
|------|--------|--------|
| Админ | `demo` | `demo` |
| Клиент | `anna.berg@demo.myclient` | `demo` |

Портал: https://bilohash.com/myclient/cms/portal.php

---

## Требования

- PHP **8.0+**
- `json`, `mbstring`, запись в `cms/data/`
- **База данных не нужна** (JSON-файлы)

---

## Быстрая установка

1. Загрузите папку `cms/` (или весь репозиторий) на хостинг.
2. Сделайте `cms/data/` доступной для записи и закрытой от листинга.
3. Откройте мастер install (если включён) или смените пароль демо-админа.
4. **Админ → Настройки**: компания, налог, Stripe / PayPal.
5. При необходимости: `config.local.example.php` → `config.local.php`.

---

## Структура

```text
myclient/
├── index.php           # SEO-лендинг
├── screenshot/         # Скриншоты UI
├── docs/screenshots/   # Картинки для README
├── CHANGELOG.md
├── VERSION
└── cms/                # Приложение (админ + кабинет)
```

---

## Changelog и релизы

- [CHANGELOG.md](CHANGELOG.md)
- https://bilohash.com/myclient/changelog.php?lang=ru
- https://github.com/Ruslan-Bilohash/myclient/releases

---

## Отказ от ответственности

Репозиторий содержит **публичное портфолио-демо**. Данные вымышлены. Не используйте демо-платежи как боевой биллинг, пока не настроите свои ключи и реквизиты юрлица.
