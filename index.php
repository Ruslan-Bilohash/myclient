<?php
/**
 * My Clients / Мої клієнти / Мои услуги — SEO product landing
 * bilohash.com/myclient/
 */
declare(strict_types=1);

$base = '/myclient';
$cms = $base . '/cms';
$admin = $cms . '/admin/login.php';
$portal = $cms . '/portal.php';
$clientLogin = $cms . '/login.php';
$install = $cms . '/install.php';
$site_url = 'https://bilohash.com' . $base;
$cms_url = 'https://bilohash.com' . $cms;
$year = (int) date('Y');
// Same VERSION file as the CMS script (cms/VERSION)
$verFile = __DIR__ . '/cms/VERSION';
$version = is_readable($verFile) ? trim((string) file_get_contents($verFile)) : '1.0.0';
if ($version === '') {
    $version = '1.0.0';
}

$langsMeta = [
    'uk' => ['html' => 'uk', 'hreflang' => 'uk', 'og' => 'uk_UA', 'label' => 'UA', 'name' => 'Українська', 'flag' => '🇺🇦'],
    'ru' => ['html' => 'ru', 'hreflang' => 'ru', 'og' => 'ru_RU', 'label' => 'RU', 'name' => 'Русский', 'flag' => '🇷🇺'],
    'en' => ['html' => 'en', 'hreflang' => 'en', 'og' => 'en_US', 'label' => 'EN', 'name' => 'English', 'flag' => '🇬🇧'],
    'no' => ['html' => 'nb', 'hreflang' => 'nb-NO', 'og' => 'nb_NO', 'label' => 'NO', 'name' => 'Norsk', 'flag' => '🇳🇴'],
    'sv' => ['html' => 'sv', 'hreflang' => 'sv-SE', 'og' => 'sv_SE', 'label' => 'SV', 'name' => 'Svenska', 'flag' => '🇸🇪'],
    'pl' => ['html' => 'pl', 'hreflang' => 'pl-PL', 'og' => 'pl_PL', 'label' => 'PL', 'name' => 'Polski', 'flag' => '🇵🇱'],
];

$lang = 'uk';
if (!empty($_GET['lang']) && isset($langsMeta[$_GET['lang']])) {
    $lang = (string) $_GET['lang'];
}

/**
 * Full SEO packs per language — max keywords: clients, My Clients, services, Invoice, tax, payment.
 */
$T = [
    'uk' => [
        'title' => 'Мої клієнти — управління клієнтами, послуги, Invoice, ПДВ/VAT/MVA, оплата | v' . $version,
        'desc' => 'Скрипт «Мої клієнти»: управління клієнтами, мої послуги (каталог і тарифи), рахунки Invoice, податки ПДВ/VAT/MVA/PVM, онлайн-оплата Stripe/PayPal, PDF, кабінет клієнта. Live demo bilohash.com/myclient/cms — demo/demo. BILOHASH.',
        'keywords' => 'мої клієнти, мої послуги, управління клієнтами, кабінет клієнта, invoice, рахунок, ПДВ, VAT, MVA, PVM, оплата, Stripe, PayPal, PDF invoice, My Clients, bilohash',
        'badge' => 'Мої клієнти · Послуги · Invoice · ПДВ/VAT',
        'h1' => 'Мої клієнти — скрипт управління клієнтами та послугами',
        'sub' => 'Адмін веде клієнтів і послуги, виставляє Invoice з податком. Клієнт у кабінеті бачить свої послуги, рахунки, оплачує онлайн і качає PDF.',
        'og_title' => 'Мої клієнти — Invoice, послуги, ПДВ, оплата',
        'og_desc' => 'PHP-скрипт: управління клієнтами, мої послуги, Invoice, ПДВ/VAT/MVA, Stripe & PayPal. Демо bilohash.com/myclient/',
        'cta_demo' => 'Відкрити демо',
        'cta_admin' => 'Адмін: клієнти',
        'cta_client' => 'Кабінет клієнта',
        'cta_contact' => 'Замовити',
        'nav_clients' => 'Клієнти',
        'nav_services' => 'Послуги',
        'nav_sites' => 'Кабінет',
        'nav_invoice' => 'Invoice',
        'nav_tax' => 'Податки',
        'nav_pay' => 'Оплата',
        'nav_shots' => 'Скріни',
        'nav_demo' => 'Демо',
        'nav_cat_product' => 'Продукт',
        'nav_cat_billing' => 'Рахунки',
        'nav_cat_more' => 'Ще',
        'nav_menu' => 'Меню',
        'intro_h' => 'Мої клієнти + послуги + Invoice',
        'intro' => 'Один PHP-скрипт «Мої клієнти»: адмін керує клієнтами й каталогом послуг, виставляє Invoice з ПДВ/VAT/MVA. Клієнт заходить у кабінет, бачить активні послуги, рахунки, платить Stripe/PayPal і завантажує PDF. Демо вигадане.',
        'features_h' => 'Що входить у скрипт',
        'clients_h' => 'Управління клієнтами',
        'clients_p' => 'База клієнтів: імʼя, email, компанія, сайт, домен, мова, статус, нотатки, пароль до кабінету клієнта.',
        'services_h' => 'Мої послуги — каталог і тарифи',
        'services_p' => 'Послуги з цінами на 1/3/12 міс, іконки, багатомовні назви. Клієнт у кабінеті бачить свої активні послуги.',
        'sites_h' => 'Кабінет клієнта',
        'sites_p' => 'Окремий вхід клієнта: огляд послуг, список Invoice, оплата, PDF, профіль і пароль.',
        'invoice_h' => 'Invoice — рахунки',
        'invoice_p' => 'Рядки послуг, subtotal, податок, total у NOK. Статуси pending / paid / cancelled. PDF-квитанція.',
        'tax_h' => 'Податки: ПДВ, VAT, MVA, PVM, PDV',
        'tax_p' => 'Увімкніть податок і % у налаштуваннях — додається до нових Invoice (ПДВ / VAT / MVA / PVM).',
        'pay_h' => 'Оплата онлайн',
        'pay_p' => 'Stripe Checkout і PayPal. Ключі в Адмін → Налаштування. Реквізити банку для ручної оплати.',
        'modules_h' => 'Модулі адмінки',
        'client_mod_h' => 'Модулі кабінету клієнта',
        'shots_h' => 'Скріншоти — натисніть, щоб збільшити',
        'shots_hint' => '← → перемикання · Esc або ✕ закрити',
        'demo_h' => 'Live demo',
        'long_h' => 'Повний опис',
        'use_h' => 'Для кого',
        'stack_h' => 'Технології',
        'faq_h' => 'FAQ',
        'seo_h' => 'SEO',
        'creds_admin' => 'Адмін',
        'creds_client' => 'Клієнт · кабінет',
        'user' => 'Логін',
        'pass' => 'Пароль',
        'footer' => 'Мої клієнти · Послуги · Invoice · ПДВ/VAT · BILOHASH',
        'footer_demo' => 'Це публічне портфоліо-демо. Не реальний білінг і не реальні платежі. Дані вигадані.',
        'footer_col_product' => 'Продукт',
        'footer_col_demo' => 'Демо',
        'footer_col_legal' => 'Політики',
        'footer_col_eco' => 'Екосистема',
        'footer_privacy' => 'Політика конфіденційності',
        'footer_cookies' => 'Cookies',
        'footer_contact' => 'Контакти',
        'footer_about' => 'Про автора',
        'footer_news' => 'Новини',
        'footer_join' => 'Приєднатися',
        'footer_changelog' => 'Changelog',
        'footer_install' => 'Встановлення',
        'footer_home' => 'bilohash.com',
        'footer_rights' => 'Усі права застережено.',
        'nav_changelog' => 'Changelog',
        'changelog_h' => 'Що нового',
        'changelog_lead' => 'Версія на сайті = версія скрипта (файл VERSION).',
        'demo_admin_d' => 'Клієнти, послуги, Invoice, податок, Stripe/PayPal.',
        'demo_client_d' => 'Кабінет: послуги, рахунки, оплата, PDF.',
        'lang_label' => 'Мова',
        'lb_close' => 'Закрити',
        'lb_prev' => 'Попередній',
        'lb_next' => 'Наступний',
        'shot_labels' => [
            'admin_login' => 'Вхід адміна',
            'admin_client_list' => 'Список клієнтів',
            'admin_client_add' => 'Додати клієнта',
            'admin_edit_clients' => 'Редагувати клієнта',
            'admin_service_pricing_list_and_edit' => 'Мої послуги — тарифи',
            'admin_service_add' => 'Нова послуга',
            'admin_invoise_list_paid_and_pending' => 'Invoice paid / pending',
            'admin_invoice' => 'Деталі Invoice',
            'admin_invoice_status' => 'Статус Invoice',
            'admin_details_company' => 'Реквізити продавця',
            'admin_exchange_and_tax' => 'Курс і ПДВ/VAT/MVA',
            'login_client' => 'Вхід у кабінет',
            'client_cabinet' => 'Кабінет клієнта',
            'client_invoice' => 'Рахунок клієнта',
            'client_pdf_invoice' => 'PDF Invoice',
            'client_setting' => 'Налаштування кабінету',
        ],
    ],
    'ru' => [
        'title' => 'Мои клиенты — управление клиентами, услуги, Invoice, НДС/VAT/MVA, оплата | v' . $version,
        'desc' => 'Скрипт «Мои клиенты»: управление клиентами, мои услуги (каталог и тарифы), счета Invoice, налоги НДС/VAT/MVA/PVM, оплата Stripe/PayPal, PDF, кабинет клиента. Demo bilohash.com/myclient/cms — demo/demo.',
        'keywords' => 'мои клиенты, мои услуги, управление клиентами, кабинет клиента, invoice, счёт, НДС, ПДВ, VAT, MVA, PVM, оплата, Stripe, PayPal, PDF invoice, My Clients, bilohash',
        'badge' => 'Мои клиенты · Услуги · Invoice · НДС/VAT',
        'h1' => 'Мои клиенты — скрипт управления клиентами и услугами',
        'sub' => 'Админ ведёт клиентов и услуги, выставляет Invoice с налогом. Клиент в кабинете видит услуги, счета, платит онлайн и качает PDF.',
        'og_title' => 'Мои клиенты — Invoice, услуги, НДС, оплата',
        'og_desc' => 'PHP-скрипт: управление клиентами, мои услуги, Invoice, НДС/VAT/MVA, Stripe & PayPal. Демо bilohash.com/myclient/',
        'cta_demo' => 'Открыть демо',
        'cta_admin' => 'Админ: клиенты',
        'cta_client' => 'Кабинет клиента',
        'cta_contact' => 'Заказать',
        'nav_clients' => 'Клиенты',
        'nav_services' => 'Услуги',
        'nav_sites' => 'Кабинет',
        'nav_invoice' => 'Invoice',
        'nav_tax' => 'Налоги',
        'nav_pay' => 'Оплата',
        'nav_shots' => 'Скрины',
        'nav_demo' => 'Демо',
        'nav_cat_product' => 'Продукт',
        'nav_cat_billing' => 'Счета',
        'nav_cat_more' => 'Ещё',
        'nav_menu' => 'Меню',
        'intro_h' => 'Мои клиенты + услуги + Invoice',
        'intro' => 'Один PHP-скрипт «Мои клиенты»: админ управляет клиентами и каталогом услуг, выставляет Invoice с НДС/VAT/MVA. Клиент входит в кабинет, видит услуги, счета, платит и скачивает PDF. Демо вымышленное.',
        'features_h' => 'Что входит в скрипт',
        'clients_h' => 'Управление клиентами',
        'clients_p' => 'База клиентов: имя, email, компания, сайт, домен, язык, статус, заметки, пароль кабинета клиента.',
        'services_h' => 'Мои услуги — каталог и тарифы',
        'services_p' => 'Услуги с ценами 1/3/12 мес, иконки, мультиязычные названия. В кабинете клиент видит активные услуги.',
        'sites_h' => 'Кабинет клиента',
        'sites_p' => 'Отдельный вход: услуги, список Invoice, оплата, PDF, профиль.',
        'invoice_h' => 'Invoice — счета',
        'invoice_p' => 'Строки услуг, subtotal, налог, total в NOK. Статусы pending / paid / cancelled. PDF-квитанция.',
        'tax_h' => 'Налоги: НДС, VAT, MVA, PVM, ПДВ, PDV',
        'tax_p' => 'Включите налог и % в настройках — добавляется к новым Invoice.',
        'pay_h' => 'Оплата онлайн',
        'pay_p' => 'Stripe Checkout и PayPal. Ключи в Админ → Настройки. Банк для ручной оплаты.',
        'modules_h' => 'Модули админки',
        'client_mod_h' => 'Модули кабинета клиента',
        'shots_h' => 'Скриншоты — нажмите, чтобы увеличить',
        'shots_hint' => '← → переключение · Esc или ✕ закрыть',
        'demo_h' => 'Live demo',
        'long_h' => 'Полное описание',
        'use_h' => 'Для кого',
        'stack_h' => 'Технологии',
        'faq_h' => 'FAQ',
        'seo_h' => 'SEO',
        'creds_admin' => 'Админ',
        'creds_client' => 'Клиент · кабинет',
        'user' => 'Логин',
        'pass' => 'Пароль',
        'footer' => 'Мои клиенты · Услуги · Invoice · НДС/VAT · BILOHASH',
        'footer_demo' => 'Это публичное портфолио-демо. Не реальный биллинг и не реальные платежи. Данные вымышлены.',
        'footer_col_product' => 'Продукт',
        'footer_col_demo' => 'Демо',
        'footer_col_legal' => 'Политики',
        'footer_col_eco' => 'Экосистема',
        'footer_privacy' => 'Политика конфиденциальности',
        'footer_cookies' => 'Cookies',
        'footer_contact' => 'Контакты',
        'footer_about' => 'Об авторе',
        'footer_news' => 'Новости',
        'footer_join' => 'Присоединиться',
        'footer_changelog' => 'Changelog',
        'footer_install' => 'Установка',
        'footer_home' => 'bilohash.com',
        'footer_rights' => 'Все права защищены.',
        'nav_changelog' => 'Changelog',
        'changelog_h' => 'Что нового',
        'changelog_lead' => 'Версия на сайте = версия скрипта (файл VERSION).',
        'demo_admin_d' => 'Клиенты, услуги, Invoice, налог, Stripe/PayPal.',
        'demo_client_d' => 'Кабинет: услуги, счета, оплата, PDF.',
        'lang_label' => 'Язык',
        'lb_close' => 'Закрыть',
        'lb_prev' => 'Предыдущий',
        'lb_next' => 'Следующий',
        'shot_labels' => [
            'admin_login' => 'Вход админа',
            'admin_client_list' => 'Список клиентов',
            'admin_client_add' => 'Добавить клиента',
            'admin_edit_clients' => 'Редактировать клиента',
            'admin_service_pricing_list_and_edit' => 'Мои услуги — тарифы',
            'admin_service_add' => 'Новая услуга',
            'admin_invoise_list_paid_and_pending' => 'Invoice paid / pending',
            'admin_invoice' => 'Детали Invoice',
            'admin_invoice_status' => 'Статус Invoice',
            'admin_details_company' => 'Реквизиты продавца',
            'admin_exchange_and_tax' => 'Курс и НДС/VAT/MVA',
            'login_client' => 'Вход в кабинет',
            'client_cabinet' => 'Кабинет клиента',
            'client_invoice' => 'Счёт клиента',
            'client_pdf_invoice' => 'PDF Invoice',
            'client_setting' => 'Настройки кабинета',
        ],
    ],
    'en' => [
        'title' => 'My Clients — client management, services, Invoice, VAT/MVA tax, payment | v' . $version,
        'desc' => 'My Clients PHP script: client management, my services catalog, Invoice billing, VAT/MVA/PVM tax, Stripe/PayPal payment, PDF, client cabinet. Live demo bilohash.com/myclient/cms — demo/demo. BILOHASH.',
        'keywords' => 'My Clients, my services, client management script, Invoice, VAT, MVA, PVM, online payment, Stripe, PayPal, PDF invoice, client portal, bilohash',
        'badge' => 'My Clients · Services · Invoice · VAT/MVA',
        'h1' => 'My Clients — client management & services script',
        'sub' => 'Admins manage clients and services, issue Invoices with tax. Clients open the cabinet, see services, invoices, pay online and download PDF.',
        'og_title' => 'My Clients — Invoice, services, VAT, payment',
        'og_desc' => 'PHP: client management, services, Invoice, VAT/MVA, Stripe & PayPal. Demo bilohash.com/myclient/',
        'cta_demo' => 'Open demo',
        'cta_admin' => 'Admin: clients',
        'cta_client' => 'Client cabinet',
        'cta_contact' => 'Order',
        'nav_clients' => 'Clients',
        'nav_services' => 'Services',
        'nav_sites' => 'Cabinet',
        'nav_invoice' => 'Invoice',
        'nav_tax' => 'Tax',
        'nav_pay' => 'Payment',
        'nav_shots' => 'Screens',
        'nav_demo' => 'Demo',
        'nav_cat_product' => 'Product',
        'nav_cat_billing' => 'Billing',
        'nav_cat_more' => 'More',
        'nav_menu' => 'Menu',
        'intro_h' => 'My Clients + services + Invoice',
        'intro' => 'One PHP script My Clients: admins manage clients and the services catalog, issue Invoices with VAT/MVA-style tax. Clients log into the cabinet, see services and invoices, pay with Stripe/PayPal and download PDF. Demo data is fictional.',
        'features_h' => 'What the script includes',
        'clients_h' => 'Client management',
        'clients_p' => 'Client base: name, email, company, site, domain, language, status, notes, password for the client cabinet.',
        'services_h' => 'My services — catalog & pricing',
        'services_p' => 'Services with 1/3/12-month prices, icons, multilingual names. Clients see active services in the cabinet.',
        'sites_h' => 'Client cabinet',
        'sites_p' => 'Separate login: services, Invoice list, payment, PDF, profile.',
        'invoice_h' => 'Invoice — bills',
        'invoice_p' => 'Service lines, subtotal, tax, total in NOK. Statuses pending / paid / cancelled. PDF receipt.',
        'tax_h' => 'Tax: VAT, MVA, PVM, PDV',
        'tax_p' => 'Enable tax and set % in settings — applied to new Invoices.',
        'pay_h' => 'Online payment',
        'pay_p' => 'Stripe Checkout and PayPal. API keys in Admin → Settings. Bank details for manual pay.',
        'modules_h' => 'Admin modules',
        'client_mod_h' => 'Client cabinet modules',
        'shots_h' => 'Screenshots — click to enlarge',
        'shots_hint' => '← → navigate · Esc or ✕ close',
        'demo_h' => 'Live demo',
        'long_h' => 'Full description',
        'use_h' => 'Who it is for',
        'stack_h' => 'Tech stack',
        'faq_h' => 'FAQ',
        'seo_h' => 'SEO',
        'creds_admin' => 'Admin',
        'creds_client' => 'Client · cabinet',
        'user' => 'Login',
        'pass' => 'Password',
        'footer' => 'My Clients · Services · Invoice · VAT/MVA · BILOHASH',
        'footer_demo' => 'This is a public portfolio demo. Not real billing and not real payments. Sample data only.',
        'footer_col_product' => 'Product',
        'footer_col_demo' => 'Demo',
        'footer_col_legal' => 'Policies',
        'footer_col_eco' => 'Ecosystem',
        'footer_privacy' => 'Privacy policy',
        'footer_cookies' => 'Cookies',
        'footer_contact' => 'Contact',
        'footer_about' => 'About',
        'footer_news' => 'News',
        'footer_join' => 'Join ecosystem',
        'footer_changelog' => 'Changelog',
        'footer_install' => 'Install',
        'footer_home' => 'bilohash.com',
        'footer_rights' => 'All rights reserved.',
        'nav_changelog' => 'Changelog',
        'changelog_h' => 'What\'s new',
        'changelog_lead' => 'Site version matches the script version (VERSION file).',
        'demo_admin_d' => 'Clients, services, Invoice, tax, Stripe/PayPal.',
        'demo_client_d' => 'Cabinet: services, invoices, pay, PDF.',
        'lang_label' => 'Language',
        'lb_close' => 'Close',
        'lb_prev' => 'Previous',
        'lb_next' => 'Next',
        'shot_labels' => [
            'admin_login' => 'Admin login',
            'admin_client_list' => 'Client list',
            'admin_client_add' => 'Add client',
            'admin_edit_clients' => 'Edit client',
            'admin_service_pricing_list_and_edit' => 'My services — pricing',
            'admin_service_add' => 'New service',
            'admin_invoise_list_paid_and_pending' => 'Invoice paid / pending',
            'admin_invoice' => 'Invoice detail',
            'admin_invoice_status' => 'Invoice status',
            'admin_details_company' => 'Seller company details',
            'admin_exchange_and_tax' => 'FX & VAT/MVA tax',
            'login_client' => 'Client cabinet login',
            'client_cabinet' => 'Client cabinet',
            'client_invoice' => 'Client invoice',
            'client_pdf_invoice' => 'PDF Invoice',
            'client_setting' => 'Cabinet settings',
        ],
    ],
    'no' => [
        'title' => 'Mine kunder — klienter, tjenester, Invoice, MVA/VAT, betaling | v' . $version,
        'desc' => 'Skriptet Mine kunder: klientadministrasjon, mine tjenester, Invoice, MVA/VAT/PVM, Stripe/PayPal, PDF, kundekabinett. Demo bilohash.com/myclient/cms — demo/demo.',
        'keywords' => 'mine kunder, mine tjenester, klientadministrasjon, invoice, faktura, MVA, VAT, PVM, betaling, Stripe, PayPal, PDF, My Clients, bilohash',
        'badge' => 'Mine kunder · Tjenester · Invoice · MVA',
        'h1' => 'Mine kunder — klientadministrasjon og tjenester',
        'sub' => 'Admin fører kunder og tjenester, utsteder Invoice med MVA. Kunden i kabinettet ser tjenester, fakturaer, betaler online og laster ned PDF.',
        'og_title' => 'Mine kunder — Invoice, tjenester, MVA, betaling',
        'og_desc' => 'PHP: klienter, tjenester, Invoice, MVA/VAT, Stripe & PayPal. Demo bilohash.com/myclient/',
        'cta_demo' => 'Åpne demo',
        'cta_admin' => 'Admin: kunder',
        'cta_client' => 'Kundekabinett',
        'cta_contact' => 'Bestill',
        'nav_clients' => 'Kunder',
        'nav_services' => 'Tjenester',
        'nav_sites' => 'Mine kunder',
        'nav_invoice' => 'Invoice',
        'nav_tax' => 'MVA',
        'nav_pay' => 'Betaling',
        'nav_shots' => 'Skjermbilder',
        'nav_demo' => 'Demo',
        'nav_cat_product' => 'Produkt',
        'nav_cat_billing' => 'Fakturering',
        'nav_cat_more' => 'Mer',
        'nav_menu' => 'Meny',
        'intro_h' => 'Mine kunder + tjenester',
        'intro' => 'Ett PHP-skript: admin administrerer kunder og tjenester, utsteder Invoice med MVA. Kunden logger inn i «Mine kunder», ser tjenester og fakturaer, betaler og laster ned PDF. Kun demo.',
        'features_h' => 'Hva skriptet inneholder',
        'clients_h' => 'Klientadministrasjon',
        'clients_p' => 'Kundebase: navn, e-post, firma, nettsted, domene, språk, status, notater, passord til Mine kunder.',
        'services_h' => 'Mine tjenester — katalog og priser',
        'services_p' => 'Tjenester med 1/3/12 mnd-priser, ikoner, flerspråklige navn. Kunden ser aktive tjenester i kabinettet.',
        'sites_h' => 'Kabinett «Mine kunder»',
        'sites_p' => 'Egen innlogging: tjenester, Invoice-liste, betaling, PDF, profil.',
        'invoice_h' => 'Invoice — faktura',
        'invoice_p' => 'Linjer, subtotal, avgift, total i NOK. Status pending / paid / cancelled. PDF.',
        'tax_h' => 'Avgift: MVA, VAT, PVM, PDV',
        'tax_p' => 'Slå på avgift og sett % — legges til nye Invoice.',
        'pay_h' => 'Nettbetaling',
        'pay_p' => 'Stripe og PayPal. Nøkler under Admin → Innstillinger.',
        'modules_h' => 'Adminmoduler',
        'client_mod_h' => 'Moduler «Mine kunder»',
        'shots_h' => 'Skjermbilder — klikk for å forstørre',
        'shots_hint' => '← → bytt · Esc eller ✕ lukk',
        'demo_h' => 'Live demo',
        'long_h' => 'Full beskrivelse',
        'use_h' => 'For hvem',
        'stack_h' => 'Teknologi',
        'faq_h' => 'FAQ',
        'seo_h' => 'SEO',
        'creds_admin' => 'Admin',
        'creds_client' => 'Kunde · Mine kunder',
        'user' => 'Bruker',
        'pass' => 'Passord',
        'footer' => 'Mine kunder · tjenester · Mine kunder · Invoice · MVA · BILOHASH',
        'footer_demo' => 'Dette er en offentlig portefølje-demo. Ikke ekte fakturering eller betalinger. Kun fiktive data.',
        'footer_col_product' => 'Produkt',
        'footer_col_demo' => 'Demo',
        'footer_col_legal' => 'Retningslinjer',
        'footer_col_eco' => 'Økosystem',
        'footer_privacy' => 'Personvern',
        'footer_cookies' => 'Cookies',
        'footer_contact' => 'Kontakt',
        'footer_about' => 'Om utvikleren',
        'footer_news' => 'Nyheter',
        'footer_join' => 'Bli med',
        'footer_changelog' => 'Changelog',
        'footer_install' => 'Installasjon',
        'footer_home' => 'bilohash.com',
        'footer_rights' => 'Alle rettigheter forbeholdt.',
        'nav_changelog' => 'Changelog',
        'changelog_h' => 'Hva er nytt',
        'changelog_lead' => 'Versjon på nettsiden = skriptversjon (VERSION-fil).',
        'demo_admin_d' => 'Kunder, tjenester, Invoice, MVA, Stripe/PayPal.',
        'demo_client_d' => 'Mine kunder: tjenester, faktura, betaling, PDF.',
        'lang_label' => 'Språk',
        'lb_close' => 'Lukk',
        'lb_prev' => 'Forrige',
        'lb_next' => 'Neste',
        'shot_labels' => [
            'admin_login' => 'Admin-innlogging',
            'admin_client_list' => 'Kundeliste',
            'admin_client_add' => 'Legg til kunde',
            'admin_edit_clients' => 'Rediger kunde',
            'admin_service_pricing_list_and_edit' => 'Mine tjenester — priser',
            'admin_service_add' => 'Ny tjeneste',
            'admin_invoise_list_paid_and_pending' => 'Invoice betalt/ubehandlet',
            'admin_invoice' => 'Invoice-detalj',
            'admin_invoice_status' => 'Invoice-status',
            'admin_details_company' => 'Selgerfirma',
            'admin_exchange_and_tax' => 'Kurs og MVA',
            'login_client' => 'Innlogging Mine kunder',
            'client_cabinet' => 'Kabinett Mine kunder',
            'client_invoice' => 'Kundefaktura',
            'client_pdf_invoice' => 'PDF Invoice',
            'client_setting' => 'Kabinettinnstillinger',
        ],
    ],
];

// SV / PL — full packs derived from EN with localized SEO titles
$T['sv'] = $T['en'];
$T['sv']['title'] = 'Mina kunder · My Clients — klienter, tjänster, Invoice, moms/VAT/MVA, betalning | v' . $version;
$T['sv']['desc'] = 'PHP-skript: klienthantering, mina tjänster, Invoice, moms (VAT/MVA/PVM), Stripe/PayPal, PDF. Kabinett «My Clients». Demo bilohash.com/myclient/cms.';
$T['sv']['keywords'] = 'mina webbplatser, mina tjänster, klienthantering, invoice, moms, VAT, MVA, PVM, betalning, Stripe, PayPal, PDF, My Clients, bilohash';
$T['sv']['h1'] = 'Mina kunder och tjänster — klienthantering';
$T['sv']['badge'] = 'Mina kunder · Tjänster · Invoice · Moms';
$T['sv']['og_title'] = 'Mina kunder / My Clients — Invoice, tjänster, moms, betalning';
$T['sv']['og_desc'] = $T['sv']['desc'];
$T['sv']['cta_client'] = 'Mina kunder';
$T['sv']['nav_sites'] = 'Mina kunder';
$T['sv']['nav_services'] = 'Tjänster';
$T['sv']['nav_cat_product'] = 'Produkt';
$T['sv']['nav_cat_billing'] = 'Fakturering';
$T['sv']['nav_cat_more'] = 'Mer';
$T['sv']['nav_menu'] = 'Meny';
$T['sv']['sites_h'] = 'Kabinett «Mina kunder»';
$T['sv']['services_h'] = 'Mina tjänster — katalog och priser';
$T['sv']['shots_h'] = 'Skärmdumpar — klicka för att förstora';
$T['sv']['shots_hint'] = '← → byt · Esc eller ✕ stäng';
$T['sv']['lb_close'] = 'Stäng';
$T['sv']['lb_prev'] = 'Föregående';
$T['sv']['lb_next'] = 'Nästa';
$T['sv']['lang_label'] = 'Språk';
$T['sv']['footer_demo'] = 'Offentlig portföljdemo — inte riktig fakturering eller betalningar.';
$T['sv']['footer_privacy'] = 'Integritetspolicy';
$T['sv']['footer_cookies'] = 'Cookies';
$T['sv']['footer_contact'] = 'Kontakt';
$T['sv']['footer_changelog'] = 'Changelog';
$T['sv']['changelog_h'] = 'Vad är nytt';
$T['sv']['changelog_lead'] = 'Webbplatsversionen matchar skriptversionen (VERSION).';

$T['pl'] = $T['en'];
$T['pl']['title'] = 'Moi klienci · My Clients — klienci, usługi, Invoice, VAT/PVM/MVA, płatność | v' . $version;
$T['pl']['desc'] = 'Skrypt PHP: zarządzanie klientami, moje usługi, Invoice, podatki VAT/PVM/MVA, Stripe/PayPal, PDF. Kabinet «My Clients». Demo bilohash.com/myclient/cms.';
$T['pl']['keywords'] = 'moje strony, moje usługi, zarządzanie klientami, invoice, VAT, PVM, MVA, płatność, Stripe, PayPal, PDF, My Clients, bilohash';
$T['pl']['h1'] = 'Moi klienci i usługi — zarządzanie klientami';
$T['pl']['badge'] = 'Moi klienci · Usługi · Invoice · VAT';
$T['pl']['og_title'] = 'Moi klienci / My Clients — Invoice, usługi, VAT, płatność';
$T['pl']['og_desc'] = $T['pl']['desc'];
$T['pl']['cta_client'] = 'Moi klienci';
$T['pl']['nav_sites'] = 'Moi klienci';
$T['pl']['nav_services'] = 'Usługi';
$T['pl']['nav_cat_product'] = 'Produkt';
$T['pl']['nav_cat_billing'] = 'Rozliczenia';
$T['pl']['nav_cat_more'] = 'Więcej';
$T['pl']['nav_menu'] = 'Menu';
$T['pl']['sites_h'] = 'Kabinet «Moi klienci»';
$T['pl']['services_h'] = 'Moje usługi — katalog i cennik';
$T['pl']['shots_h'] = 'Zrzuty — kliknij, aby powiększyć';
$T['pl']['shots_hint'] = '← → przełącz · Esc lub ✕ zamknij';
$T['pl']['lb_close'] = 'Zamknij';
$T['pl']['lb_prev'] = 'Poprzedni';
$T['pl']['lb_next'] = 'Następny';
$T['pl']['lang_label'] = 'Język';
$T['pl']['footer_demo'] = 'Publiczne demo portfolio — nie prawdziwe rozliczenia ani płatności.';
$T['pl']['footer_privacy'] = 'Polityka prywatności';
$T['pl']['footer_cookies'] = 'Cookies';
$T['pl']['footer_contact'] = 'Kontakt';
$T['pl']['footer_changelog'] = 'Changelog';
$T['pl']['changelog_h'] = 'Co nowego';
$T['pl']['changelog_lead'] = 'Wersja na stronie = wersja skryptu (plik VERSION).';

$L = $T[$lang] ?? $T['uk'];
$meta = $langsMeta[$lang];

// Other BILOHASH CMS scripts for footer (product landing URLs)
$ecoScripts = [
    ['slug' => 'hosting', 'url' => 'https://bilohash.com/hosting/', 'label' => ['en' => 'Hosting CMS', 'no' => 'Hosting CMS', 'uk' => 'Hosting CMS', 'ru' => 'Hosting CMS', 'sv' => 'Hosting CMS', 'pl' => 'Hosting CMS']],
    ['slug' => 'shop', 'url' => 'https://bilohash.com/shop/', 'label' => ['en' => 'Shop CMS', 'no' => 'Shop CMS', 'uk' => 'Shop CMS', 'ru' => 'Shop CMS', 'sv' => 'Shop CMS', 'pl' => 'Shop CMS']],
    ['slug' => 'booking', 'url' => 'https://bilohash.com/booking/', 'label' => ['en' => 'Booking CMS', 'no' => 'Booking CMS', 'uk' => 'Booking CMS', 'ru' => 'Booking CMS', 'sv' => 'Booking CMS', 'pl' => 'Booking CMS']],
    ['slug' => 'faktura', 'url' => 'https://bilohash.com/faktura/', 'label' => ['en' => 'Faktura', 'no' => 'Faktura', 'uk' => 'Faktura', 'ru' => 'Faktura', 'sv' => 'Faktura', 'pl' => 'Faktura']],
    ['slug' => 'lending', 'url' => 'https://bilohash.com/lending/', 'label' => ['en' => 'Business Landing', 'no' => 'Business Landing', 'uk' => 'Business Landing', 'ru' => 'Business Landing', 'sv' => 'Business Landing', 'pl' => 'Business Landing']],
    ['slug' => 'auction', 'url' => 'https://bilohash.com/auction/', 'label' => ['en' => 'Auction', 'no' => 'Auction', 'uk' => 'Аукціон', 'ru' => 'Аукцион', 'sv' => 'Auction', 'pl' => 'Aukcja']],
    ['slug' => 'tavle', 'url' => 'https://bilohash.com/tavle/', 'label' => ['en' => 'Bilen CMS', 'no' => 'Bilen CMS', 'uk' => 'Bilen CMS', 'ru' => 'Bilen CMS', 'sv' => 'Bilen CMS', 'pl' => 'Bilen CMS']],
    ['slug' => 'driving-school', 'url' => 'https://bilohash.com/driving-school/', 'label' => ['en' => 'Driving School', 'no' => 'Kjøreskole', 'uk' => 'Автошкола', 'ru' => 'Автошкола', 'sv' => 'Körskola', 'pl' => 'Szkoła jazdy']],
    ['slug' => 'freelance', 'url' => 'https://bilohash.com/freelance/', 'label' => ['en' => 'Freelance', 'no' => 'Freelance', 'uk' => 'Фріланс', 'ru' => 'Фриланс', 'sv' => 'Frilans', 'pl' => 'Freelance']],
    ['slug' => 'pizza', 'url' => 'https://bilohash.com/pizza/', 'label' => ['en' => 'Pizza CMS', 'no' => 'Pizza CMS', 'uk' => 'Pizza CMS', 'ru' => 'Pizza CMS', 'sv' => 'Pizza CMS', 'pl' => 'Pizza CMS']],
    ['slug' => 'gamehub', 'url' => 'https://bilohash.com/gamehub/', 'label' => ['en' => 'GameHub', 'no' => 'GameHub', 'uk' => 'GameHub', 'ru' => 'GameHub', 'sv' => 'GameHub', 'pl' => 'GameHub']],
    ['slug' => 'today', 'url' => 'https://bilohash.com/today/', 'label' => ['en' => 'Today CMS', 'no' => 'Today CMS', 'uk' => 'Today CMS', 'ru' => 'Today CMS', 'sv' => 'Today CMS', 'pl' => 'Today CMS']],
    ['slug' => '3d', 'url' => 'https://bilohash.com/3d/', 'label' => ['en' => '3D Shop', 'no' => '3D Shop', 'uk' => '3D Shop', 'ru' => '3D Shop', 'sv' => '3D Shop', 'pl' => '3D Shop']],
    ['slug' => 'ai', 'url' => 'https://bilohash.com/ai/', 'label' => ['en' => 'AI Platform', 'no' => 'AI Platform', 'uk' => 'AI Platform', 'ru' => 'AI Platform', 'sv' => 'AI Platform', 'pl' => 'AI Platform']],
    ['slug' => 'wordpress', 'url' => 'https://bilohash.com/wordpress/', 'label' => ['en' => 'WordPress', 'no' => 'WordPress', 'uk' => 'WordPress', 'ru' => 'WordPress', 'sv' => 'WordPress', 'pl' => 'WordPress']],
    ['slug' => 'landing_builder', 'url' => 'https://bilohash.com/landing_builder/', 'label' => ['en' => 'Landing Builder', 'no' => 'Landing Builder', 'uk' => 'Landing Builder', 'ru' => 'Landing Builder', 'sv' => 'Landing Builder', 'pl' => 'Landing Builder']],
    ['slug' => 'news', 'url' => 'https://bilohash.com/news.php', 'label' => ['en' => 'News hub', 'no' => 'Nyhetshub', 'uk' => 'Новини', 'ru' => 'Новости', 'sv' => 'Nyheter', 'pl' => 'Aktualności']],
];
// Localized short names for footer heading
$footerScriptsTitle = match ($lang) {
    'uk' => 'Інші скрипти екосистеми BILOHASH',
    'ru' => 'Другие скрипты экосистемы BILOHASH',
    'no' => 'Andre skript i BILOHASH-økosystemet',
    'sv' => 'Andra skript i BILOHASH-ekosystemet',
    'pl' => 'Inne skrypty ekosystemu BILOHASH',
    default => 'Other BILOHASH ecosystem scripts',
};

$features = [
    ['icon' => 'fa-users-gear', 'id' => 'clients', 'title' => $L['clients_h'], 'text' => $L['clients_p']],
    ['icon' => 'fa-puzzle-piece', 'id' => 'services', 'title' => $L['services_h'], 'text' => $L['services_p']],
    ['icon' => 'fa-globe', 'id' => 'sites', 'title' => $L['sites_h'], 'text' => $L['sites_p']],
    ['icon' => 'fa-file-invoice', 'id' => 'invoice', 'title' => $L['invoice_h'], 'text' => $L['invoice_p']],
    ['icon' => 'fa-percent', 'id' => 'tax', 'title' => $L['tax_h'], 'text' => $L['tax_p']],
    ['icon' => 'fa-credit-card', 'id' => 'pay', 'title' => $L['pay_h'], 'text' => $L['pay_p']],
];

$adminModules = match ($lang) {
    'uk' => ['Клієнти', 'Мої послуги / тарифи', 'Invoice', 'Податок %', 'Stripe/PayPal', 'Реквізити', 'Курс'],
    'ru' => ['Клиенты', 'Мои услуги / тарифы', 'Invoice', 'Налог %', 'Stripe/PayPal', 'Реквизиты', 'Курс'],
    'no' => ['Kunder', 'Tjenester / priser', 'Invoice', 'MVA %', 'Stripe/PayPal', 'Firma', 'Kurs'],
    default => ['Clients', 'My services / pricing', 'Invoice', 'Tax %', 'Stripe/PayPal', 'Company', 'FX'],
};

$clientModules = match ($lang) {
    'uk' => ['Мої клієнти / послуги', 'Invoice', 'Оплата', 'PDF', 'Профіль'],
    'ru' => ['Мои клиенты / услуги', 'Invoice', 'Оплата', 'PDF', 'Профиль'],
    'no' => ['Mine kunder / tjenester', 'Invoice', 'Betaling', 'PDF', 'Profil'],
    default => ['My Clients / services', 'Invoice', 'Payment', 'PDF', 'Profile'],
};

$useCases = match ($lang) {
    'uk' => ['Фріланс', 'Агентства', 'Хостинг', 'МСБ', 'Портфоліо'],
    'ru' => ['Фриланс', 'Агентства', 'Хостинг', 'МСБ', 'Портфолио'],
    'no' => ['Frilans', 'Byrå', 'Hosting', 'SMB', 'Portefølje'],
    default => ['Freelance', 'Agencies', 'Hosting', 'SME', 'Portfolio'],
};

$faq = [
    [
        'q' => match ($lang) {
            'uk' => 'Що таке «Мої клієнти»?',
            'ru' => 'Что такое «Мои клиенты»?',
            'no' => 'Hva er «Mine kunder»?',
            default => 'What is “My Clients”?',
        },
        'a' => match ($lang) {
            'uk' => 'Кабінет клієнта в скрипті My Clients: активні послуги (мої сайти / мої послуги), рахунки Invoice, оплата і PDF.',
            'ru' => 'Кабинет клиента в скрипте My Clients: активные услуги (мои сайты / мои услуги), счета Invoice, оплата и PDF.',
            'no' => 'Kundekabinettet i My Clients: aktive tjenester, Invoice, betaling og PDF.',
            default => 'The client cabinet in My Clients: active services (my clients / my services), invoices, payment and PDF.',
        },
    ],
    [
        'q' => match ($lang) {
            'uk' => 'Що таке «мої послуги»?',
            'ru' => 'Что такое «мои услуги»?',
            'no' => 'Hva er «mine tjenester»?',
            default => 'What are “my services”?',
        },
        'a' => match ($lang) {
            'uk' => 'Каталог послуг і тарифів в адмінці; клієнт бачить підключені послуги у «Мої клієнти».',
            'ru' => 'Каталог услуг и тарифов в админке; клиент видит подключённые услуги в «Мои клиенты».',
            'no' => 'Tjenestekatalog i admin; kunden ser aktive tjenester i Mine kunder.',
            default => 'Services catalog and pricing in admin; clients see active services in My Clients.',
        },
    ],
    [
        'q' => match ($lang) {
            'uk' => 'Як працює Invoice і ПДВ/VAT/MVA?',
            'ru' => 'Как работает Invoice и НДС/VAT/MVA?',
            'no' => 'Hvordan fungerer Invoice og MVA?',
            default => 'How do Invoice and VAT/MVA work?',
        },
        'a' => match ($lang) {
            'uk' => 'Адмін створює Invoice; % податку з налаштувань додається до subtotal. ПДВ/VAT/MVA/PVM — регіональні назви однієї ставки.',
            'ru' => 'Админ создаёт Invoice; % налога из настроек добавляется к subtotal. НДС/VAT/MVA/PVM — региональные названия одной ставки.',
            'no' => 'Admin lager Invoice; avgifts-% fra innstillinger legges til subtotal. MVA/VAT/PVM er regionale navn.',
            default => 'Admin creates Invoice; tax % from settings is added to subtotal. VAT/MVA/PVM are regional labels for one rate model.',
        },
    ],
    [
        'q' => match ($lang) {
            'uk' => 'Де демо?',
            'ru' => 'Где демо?',
            'no' => 'Hvor er demo?',
            default => 'Where is the demo?',
        },
        'a' => 'https://bilohash.com/myclient/cms/portal.php — admin demo/demo · client anna.berg@demo.myclient / demo',
    ],
];

$shotDir = __DIR__ . '/screenshot';
$shotUrlBase = $base . '/screenshot';
$shotOrder = [
    'client_cabinet', 'login_client', 'client_invoice', 'client_pdf_invoice', 'client_setting',
    'admin_client_list', 'admin_client_add', 'admin_edit_clients',
    'admin_service_pricing_list_and_edit', 'admin_service_add',
    'admin_invoise_list_paid_and_pending', 'admin_invoice', 'admin_invoice_status',
    'admin_exchange_and_tax', 'admin_details_company', 'admin_login',
];
$shots = [];
foreach ($shotOrder as $id) {
    $webp = $shotDir . '/' . $id . '.webp';
    $jpg = $shotDir . '/' . $id . '.jpg';
    if (is_file($webp)) {
        $file = $id . '.webp';
    } elseif (is_file($jpg)) {
        $file = $id . '.jpg';
    } else {
        continue;
    }
    $thumb = $id . '-480.webp';
    if (!is_file($shotDir . '/' . $thumb)) {
        $thumb = $file;
    }
    $shots[] = [
        'id' => $id,
        'file' => $file,
        'thumb' => $thumb,
        'label' => $L['shot_labels'][$id] ?? $id,
        'url' => $site_url . '/screenshot/' . rawurlencode($file),
    ];
}

$ogImage = $shots[0]['url'] ?? ($site_url . '/screenshot/client_cabinet.webp');
$lang_qs = static fn(string $code): string => $base . '/?lang=' . $code;
$canonical = $site_url . '/?lang=' . $lang;

$jsonLd = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Organization',
            '@id' => 'https://bilohash.com/#organization',
            'name' => 'BILOHASH',
            'url' => 'https://bilohash.com/',
            'logo' => 'https://bilohash.com/favicon.ico',
        ],
        [
            '@type' => 'WebSite',
            '@id' => $site_url . '/#website',
            'name' => 'My Clients — Мої клієнти / Мои услуги',
            'alternateName' => ['Мої клієнти', 'Мои клиенты', 'My Clients', 'Mine kunder', 'Мої клієнти', 'Мои клиенты'],
            'url' => $site_url . '/',
            'publisher' => ['@id' => 'https://bilohash.com/#organization'],
            'inLanguage' => array_values(array_unique(array_column($langsMeta, 'html'))),
            'potentialAction' => [
                '@type' => 'ViewAction',
                'target' => $cms_url . '/portal.php',
                'name' => 'Open demo',
            ],
        ],
        [
            '@type' => 'WebPage',
            '@id' => $canonical . '#webpage',
            'url' => $canonical,
            'name' => $L['title'],
            'description' => $L['desc'],
            'isPartOf' => ['@id' => $site_url . '/#website'],
            'primaryImageOfPage' => ['@type' => 'ImageObject', 'url' => $ogImage, 'width' => 1200, 'height' => 630],
            'inLanguage' => $meta['html'],
            'dateModified' => date('Y-m-d'),
            'about' => [
                ['@type' => 'Thing', 'name' => 'My Clients / Мої клієнти / Мои клиенты'],
                ['@type' => 'Thing', 'name' => 'My services / Мої послуги / Мои услуги'],
                ['@type' => 'Thing', 'name' => 'Client management'],
                ['@type' => 'Thing', 'name' => 'Invoice'],
                ['@type' => 'Thing', 'name' => 'VAT MVA PVM tax'],
                ['@type' => 'Thing', 'name' => 'Online payment'],
            ],
        ],
        [
            '@type' => 'SoftwareApplication',
            '@id' => $site_url . '/#software',
            'name' => 'My Clients',
            'alternateName' => ['Мої клієнти', 'Мои клиенты', 'Мої клієнти', 'Мои клиенты', 'My Clients', 'Mine kunder'],
            'applicationCategory' => 'BusinessApplication',
            'applicationSubCategory' => 'Client management, My Clients cabinet, services catalog, Invoice, VAT/MVA tax, payment',
            'operatingSystem' => 'Web / PHP 8+',
            'softwareVersion' => $version,
            'url' => $site_url . '/',
            'installUrl' => $cms_url . '/',
            'screenshot' => array_map(static fn($s) => $s['url'], array_slice($shots, 0, 12)),
            'featureList' => [
                'Client management', 'My Clients cabinet', 'My services catalog',
                'Invoice', 'VAT', 'MVA', 'PVM', 'ПДВ', 'PDV',
                'Stripe payment', 'PayPal payment', 'PDF Invoice',
            ],
            'keywords' => $L['keywords'],
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'NOK',
                'availability' => 'https://schema.org/InStock',
                'description' => 'Portfolio live demo',
            ],
            'author' => ['@id' => 'https://bilohash.com/#organization'],
            'publisher' => ['@id' => 'https://bilohash.com/#organization'],
        ],
        [
            '@type' => 'ImageGallery',
            '@id' => $site_url . '/#gallery',
            'name' => $L['shots_h'],
            'image' => array_map(static fn($s) => [
                '@type' => 'ImageObject',
                'contentUrl' => $s['url'],
                'name' => $s['label'],
                'description' => $s['label'] . ' — My Clients / Мої клієнти / Invoice',
            ], $shots),
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'BILOHASH', 'item' => 'https://bilohash.com/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'My Clients / Мої клієнти', 'item' => $site_url . '/'],
            ],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(static fn($f) => [
                '@type' => 'Question',
                'name' => $f['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
            ], $faq),
        ],
        [
            '@type' => 'ItemList',
            'name' => 'My Clients core topics',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Мої клієнти / My Clients'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Мои услуги / My services'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Client management'],
                ['@type' => 'ListItem', 'position' => 4, 'name' => 'Invoice'],
                ['@type' => 'ListItem', 'position' => 5, 'name' => 'VAT MVA PVM ПДВ'],
                ['@type' => 'ListItem', 'position' => 6, 'name' => 'Online payment'],
            ],
        ],
    ],
];

// JSON for lightbox
$lbJson = array_map(static fn($s) => [
    'src' => $site_url . '/screenshot/' . $s['file'],
    'alt' => $s['label'],
    'cap' => $s['label'],
], $shots);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($meta['html']) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= htmlspecialchars($L['title']) ?></title>
<meta name="description" content="<?= htmlspecialchars($L['desc']) ?>">
<meta name="keywords" content="<?= htmlspecialchars($L['keywords']) ?>">
<meta name="author" content="BILOHASH">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<meta name="theme-color" content="#0d9488">
<meta name="application-name" content="My Clients">
<link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
<?php foreach ($langsMeta as $c => $lm): ?>
<link rel="alternate" hreflang="<?= htmlspecialchars($lm['hreflang']) ?>" href="<?= htmlspecialchars($site_url . '/?lang=' . $c) ?>">
<?php endforeach; ?>
<link rel="alternate" hreflang="x-default" href="<?= htmlspecialchars($site_url . '/?lang=uk') ?>">
<meta property="og:type" content="website">
<meta property="og:locale" content="<?= htmlspecialchars($meta['og']) ?>">
<?php foreach ($langsMeta as $c => $lm): if ($c === $lang) continue; ?>
<meta property="og:locale:alternate" content="<?= htmlspecialchars($lm['og']) ?>">
<?php endforeach; ?>
<meta property="og:title" content="<?= htmlspecialchars($L['og_title'] ?? $L['title']) ?>">
<meta property="og:description" content="<?= htmlspecialchars($L['og_desc'] ?? $L['desc']) ?>">
<meta property="og:url" content="<?= htmlspecialchars($canonical) ?>">
<meta property="og:site_name" content="My Clients · Мої клієнти · Мои услуги · Invoice">
<meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">
<meta property="og:image:secure_url" content="<?= htmlspecialchars($ogImage) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="<?= htmlspecialchars($L['og_title'] ?? $L['h1']) ?>">
<meta property="og:image:type" content="image/webp">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($L['og_title'] ?? $L['h1']) ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($L['og_desc'] ?? $L['desc']) ?>">
<meta name="twitter:image" content="<?= htmlspecialchars($ogImage) ?>">
<meta name="twitter:image:alt" content="<?= htmlspecialchars($L['h1']) ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin>
<link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/site.css?v=<?= rawurlencode($version) ?>">
<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<style>
.mk-shot-grid{display:grid;gap:16px;grid-template-columns:1fr}
@media(min-width:640px){.mk-shot-grid{grid-template-columns:1fr 1fr}}
@media(min-width:960px){.mk-shot-grid{grid-template-columns:1fr 1fr 1fr}}
.mk-shot-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;box-shadow:0 8px 24px rgba(15,23,42,.06);cursor:zoom-in;transition:transform .15s,box-shadow .15s}
.mk-shot-card:hover{transform:translateY(-2px);box-shadow:0 12px 28px rgba(15,23,42,.1)}
.mk-shot-card button{display:block;width:100%;padding:0;border:0;background:none;cursor:zoom-in;text-align:left;font:inherit;color:inherit}
.mk-shot-card img{width:100%;height:180px;object-fit:cover;object-position:top;display:block;background:#f1f5f9}
.mk-shot-card figcaption{padding:10px 12px;font-size:.85rem;font-weight:600;color:#334155}
.mk-mod-grid{display:flex;flex-wrap:wrap;gap:10px;justify-content:center}
.mk-mod-grid span{background:#fff;border:1px solid #e2e8f0;border-radius:999px;padding:8px 14px;font-size:.9rem;font-weight:600}
.mk-cred{display:grid;gap:12px;grid-template-columns:1fr}
@media(min-width:720px){.mk-cred{grid-template-columns:1fr 1fr}}
.mk-cred-card{background:#f0fdfa;border:1px solid #99f6e4;border-radius:12px;padding:16px}
.mk-cred-card code{font-size:.95rem;background:#fff;padding:2px 8px;border-radius:6px}
.mk-tax-tags{display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin:16px 0 0}
.mk-tax-tags span{background:#0f766e;color:#fff;font-weight:700;font-size:.78rem;padding:6px 12px;border-radius:999px}
.ds-sticky-cta{position:fixed;bottom:16px;right:16px;z-index:50;display:flex;gap:8px}
.ds-sticky-cta a{box-shadow:0 8px 24px rgba(13,148,136,.35)}
/* Compact category nav — no wrap of many links */
.shs-header-inner{flex-wrap:nowrap!important;gap:10px}
.shs-nav{display:flex!important;flex-wrap:nowrap;align-items:center;gap:4px;min-width:0}
.shs-nav-cats{display:flex;flex-wrap:nowrap;align-items:center;gap:4px}
.shs-nav-mobile-only{display:none}
.shs-nav-drop{position:relative}
.shs-nav-btn{
  display:inline-flex;align-items:center;gap:6px;
  border:0;background:transparent;padding:8px 11px;border-radius:8px;
  cursor:pointer;font:inherit;font-weight:600;font-size:.9rem;color:#64748b;white-space:nowrap;
}
.shs-nav-btn:hover,.shs-nav-btn[aria-expanded="true"]{color:#0f766e;background:#f0fdfa}
.shs-nav-btn .shs-nav-caret{font-size:.65rem;opacity:.7;line-height:1}
.shs-nav-menu{
  position:absolute;top:calc(100% + 6px);left:0;min-width:200px;z-index:130;
  list-style:none;margin:0;padding:6px;background:#fff;border:1px solid #e2e8f0;
  border-radius:12px;box-shadow:0 12px 32px rgba(15,23,42,.12);
}
.shs-nav-menu[hidden]{display:none!important}
.shs-nav-menu a{
  display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;
  color:#1e293b;font-weight:600;text-decoration:none;white-space:nowrap;
}
.shs-nav-menu a:hover{background:#ccfbf1;color:#0f766e}
.shs-nav-menu a i{width:1.1em;text-align:center;color:#0f766e;opacity:.85}
.shs-nav-menu--end{left:auto;right:0}
@media(max-width:900px){
  .shs-nav-cats{display:none}
  .shs-nav-mobile-only{display:block}
}
@media(max-width:640px){
  .shs-header .mk-ver-pill,.shs-header .shs-btn-outline{display:none}
}
.mk-long h3{margin:24px 0 10px;color:#0f766e}
.mk-faq details{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:12px 16px;margin-bottom:10px}
.mk-faq summary{font-weight:700;cursor:pointer}
.shs-lang-dropdown{position:relative;z-index:120}
.shs-lang-btn{display:inline-flex;align-items:center;gap:8px;border:1px solid #e2e8f0;background:#fff;padding:8px 12px;border-radius:10px;cursor:pointer;font-weight:700;font-size:.9rem;color:#1e293b;font-family:inherit}
.shs-lang-btn:hover,.shs-lang-btn[aria-expanded="true"]{border-color:#99f6e4;background:#f0fdfa}
.shs-lang-menu{position:absolute;top:calc(100% + 6px);right:0;min-width:190px;list-style:none;margin:0;padding:6px;background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 12px 32px rgba(15,23,42,.12);z-index:130}
.shs-lang-menu[hidden]{display:none!important}
.shs-lang-menu a{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;color:#1e293b;font-weight:600;text-decoration:none}
.shs-lang-menu a:hover,.shs-lang-menu a.active{background:#ccfbf1;color:#0f766e}
.shs-lang-menu a .code{margin-left:auto;font-size:.75rem;opacity:.65}
.mk-ver-pill{font-size:.75rem;font-weight:700;color:#0f766e;background:#ccfbf1;padding:4px 10px;border-radius:999px}
.mk-shots-hint{text-align:center;color:#64748b;font-size:.9rem;margin:-8px 0 20px}
.mk-demo-banner{background:linear-gradient(90deg,#fef3c7,#ffedd5);border-bottom:1px solid #fcd34d;color:#92400e;font-size:.9rem;padding:10px 0;text-align:center}
.mk-demo-banner a{color:#b45309;font-weight:700}
.mk-site-footer{background:#0f172a;color:#94a3b8;padding:48px 0 28px;margin-top:0;font-size:.9rem}
.mk-site-footer a{color:#99f6e4;text-decoration:none;display:block;margin:6px 0;font-weight:500}
.mk-site-footer a:hover{color:#fff}
.mk-footer-demo{background:rgba(251,191,36,.12);border:1px solid rgba(251,191,36,.35);color:#fde68a;border-radius:12px;padding:12px 16px;margin-bottom:28px;line-height:1.45;font-size:.9rem}
.mk-footer-grid{display:grid;gap:28px;grid-template-columns:1.4fr 1fr 1fr 1fr 1fr}
@media(max-width:900px){.mk-footer-grid{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.mk-footer-grid{grid-template-columns:1fr}}
.mk-footer-brand{font-size:1.25rem;font-weight:800;color:#fff;margin-bottom:10px}
.mk-footer-brand em{color:#2dd4bf;font-style:normal}
.mk-footer-tag{color:#64748b;line-height:1.5;margin:0;max-width:260px}
.mk-site-footer h4{margin:0 0 10px;color:#e2e8f0;font-size:.8rem;text-transform:uppercase;letter-spacing:.08em}
.mk-footer-bottom{margin-top:32px;padding-top:18px;border-top:1px solid #1e293b;display:flex;flex-wrap:wrap;gap:10px;justify-content:space-between;font-size:.82rem;color:#64748b}
.mk-footer-scripts{margin-top:28px;padding-top:22px;border-top:1px solid #1e293b}
.mk-footer-scripts h4{margin:0 0 14px;color:#e2e8f0;font-size:.8rem;text-transform:uppercase;letter-spacing:.08em}
.mk-footer-pills{display:flex;flex-wrap:wrap;gap:8px}
.mk-footer-pills a{
  display:inline-flex!important;align-items:center;
  margin:0!important;padding:7px 12px;border-radius:999px;
  background:rgba(45,212,191,.08);border:1px solid rgba(45,212,191,.22);
  color:#99f6e4!important;font-size:.8rem;font-weight:600;text-decoration:none;
  transition:background .15s,border-color .15s,color .15s;
}
.mk-footer-pills a:hover{background:rgba(45,212,191,.18);border-color:rgba(45,212,191,.45);color:#fff!important}
/* Lightbox */
.mk-lb{position:fixed;inset:0;z-index:9999;background:rgba(8,12,20,.92);display:flex;align-items:center;justify-content:center;padding:16px;opacity:0;visibility:hidden;transition:opacity .2s,visibility .2s}
.mk-lb.is-open{opacity:1;visibility:visible}
.mk-lb-inner{position:relative;max-width:min(1100px,96vw);max-height:92vh;display:flex;flex-direction:column;align-items:center;gap:10px}
.mk-lb-img-wrap{position:relative;max-height:78vh;overflow:auto;border-radius:12px;background:#0f172a;box-shadow:0 20px 60px rgba(0,0,0,.45)}
.mk-lb-img{display:block;max-width:min(1100px,96vw);max-height:78vh;width:auto;height:auto;margin:0 auto}
.mk-lb-cap{color:#e2e8f0;font-size:.95rem;font-weight:600;text-align:center}
.mk-lb-count{color:#94a3b8;font-size:.8rem}
.mk-lb-btn{position:absolute;border:0;background:rgba(255,255,255,.12);color:#fff;width:48px;height:48px;border-radius:999px;cursor:pointer;font-size:1.25rem;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(6px);transition:background .15s}
.mk-lb-btn:hover{background:rgba(255,255,255,.22)}
.mk-lb-close{top:12px;right:12px;z-index:3}
.mk-lb-prev{left:8px;top:50%;transform:translateY(-50%);z-index:3}
.mk-lb-next{right:8px;top:50%;transform:translateY(-50%);z-index:3}
@media(max-width:640px){
  .mk-lb-btn{width:42px;height:42px}
  .mk-lb-prev{left:4px}
  .mk-lb-next{right:4px}
}
body.mk-lb-lock{overflow:hidden}
</style>
</head>
<body>
<header class="shs-header">
  <div class="shs-header-inner">
    <a class="shs-logo" href="<?= htmlspecialchars($lang_qs($lang)) ?>">
      <span class="shs-logo-icon"><i class="fas fa-users"></i></span>
      <?php
      $logoHtml = match ($lang) {
          'uk' => 'Мої <em>клієнти</em>',
          'ru' => 'Мои <em>клиенты</em>',
          'no' => 'Mine <em>kunder</em>',
          'sv' => 'Mina <em>kunder</em>',
          'pl' => 'Moi <em>klienci</em>',
          default => 'My <em>Clients</em>',
      };
      echo $logoHtml;
      ?>
    </a>
    <?php
    $navChangelogHref = $base . '/changelog.php?lang=' . rawurlencode($lang);
    $navProductItems = [
        ['href' => '#clients', 'icon' => 'fa-users', 'label' => $L['nav_clients']],
        ['href' => '#services', 'icon' => 'fa-tags', 'label' => $L['nav_services']],
        ['href' => '#sites', 'icon' => 'fa-globe', 'label' => $L['nav_sites']],
    ];
    $navBillingItems = [
        ['href' => '#invoice', 'icon' => 'fa-file-invoice', 'label' => $L['nav_invoice']],
        ['href' => '#tax', 'icon' => 'fa-percent', 'label' => $L['nav_tax']],
        ['href' => '#pay', 'icon' => 'fa-credit-card', 'label' => $L['nav_pay']],
    ];
    $navMoreItems = [
        ['href' => '#shots', 'icon' => 'fa-images', 'label' => $L['nav_shots']],
        ['href' => '#demo', 'icon' => 'fa-play', 'label' => $L['nav_demo']],
        ['href' => $navChangelogHref, 'icon' => 'fa-clock-rotate-left', 'label' => $L['nav_changelog'] ?? 'Changelog'],
    ];
    $navAllItems = array_merge($navProductItems, $navBillingItems, $navMoreItems);
    $renderNavMenu = static function (array $items, string $extraClass = '') {
        $cls = 'shs-nav-menu' . ($extraClass !== '' ? ' ' . $extraClass : '');
        echo '<ul class="' . htmlspecialchars($cls) . '" data-nav-menu hidden role="menu">';
        foreach ($items as $item) {
            echo '<li role="none"><a role="menuitem" href="' . htmlspecialchars((string) $item['href']) . '">';
            if (!empty($item['icon'])) {
                echo '<i class="fas ' . htmlspecialchars((string) $item['icon']) . '" aria-hidden="true"></i>';
            }
            echo '<span>' . htmlspecialchars((string) $item['label']) . '</span></a></li>';
        }
        echo '</ul>';
    };
    ?>
    <nav class="shs-nav" aria-label="Main">
      <div class="shs-nav-cats">
        <div class="shs-nav-drop" data-nav-drop>
          <button type="button" class="shs-nav-btn" data-nav-btn aria-haspopup="menu" aria-expanded="false">
            <?= htmlspecialchars($L['nav_cat_product'] ?? 'Product') ?>
            <span class="shs-nav-caret" aria-hidden="true">▾</span>
          </button>
          <?php $renderNavMenu($navProductItems); ?>
        </div>
        <div class="shs-nav-drop" data-nav-drop>
          <button type="button" class="shs-nav-btn" data-nav-btn aria-haspopup="menu" aria-expanded="false">
            <?= htmlspecialchars($L['nav_cat_billing'] ?? 'Billing') ?>
            <span class="shs-nav-caret" aria-hidden="true">▾</span>
          </button>
          <?php $renderNavMenu($navBillingItems); ?>
        </div>
        <div class="shs-nav-drop" data-nav-drop>
          <button type="button" class="shs-nav-btn" data-nav-btn aria-haspopup="menu" aria-expanded="false">
            <?= htmlspecialchars($L['nav_cat_more'] ?? 'More') ?>
            <span class="shs-nav-caret" aria-hidden="true">▾</span>
          </button>
          <?php $renderNavMenu($navMoreItems); ?>
        </div>
      </div>
      <div class="shs-nav-drop shs-nav-mobile-only" data-nav-drop>
        <button type="button" class="shs-nav-btn" data-nav-btn aria-haspopup="menu" aria-expanded="false" aria-label="<?= htmlspecialchars($L['nav_menu'] ?? 'Menu') ?>">
          <i class="fas fa-bars" aria-hidden="true"></i>
          <?= htmlspecialchars($L['nav_menu'] ?? 'Menu') ?>
          <span class="shs-nav-caret" aria-hidden="true">▾</span>
        </button>
        <?php $renderNavMenu($navAllItems, 'shs-nav-menu--end'); ?>
      </div>
    </nav>
    <div class="shs-header-tools">
      <span class="mk-ver-pill" title="CMS VERSION">v<?= htmlspecialchars($version) ?></span>
      <div class="shs-lang-dropdown" data-lang-drop>
        <button type="button" class="shs-lang-btn" data-lang-btn aria-haspopup="listbox" aria-expanded="false" aria-label="<?= htmlspecialchars($L['lang_label']) ?>">
          <span><?= htmlspecialchars($meta['flag']) ?></span>
          <span><?= htmlspecialchars($meta['label']) ?></span>
          <span aria-hidden="true">▾</span>
        </button>
        <ul class="shs-lang-menu" data-lang-menu hidden role="listbox">
          <?php foreach ($langsMeta as $code => $lm): ?>
          <li>
            <a href="<?= htmlspecialchars($lang_qs($code)) ?>" class="<?= $code === $lang ? 'active' : '' ?>" role="option" hreflang="<?= htmlspecialchars($lm['hreflang']) ?>" lang="<?= htmlspecialchars($lm['html']) ?>">
              <span><?= htmlspecialchars($lm['flag']) ?></span>
              <span><?= htmlspecialchars($lm['name']) ?></span>
              <span class="code"><?= htmlspecialchars($lm['label']) ?></span>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <a class="shs-btn-outline" href="<?= htmlspecialchars($admin) ?>"><i class="fas fa-user-shield"></i> Admin</a>
      <a class="shs-btn-primary" href="<?= htmlspecialchars($portal) ?>"><i class="fas fa-rocket"></i> Demo</a>
    </div>
  </div>
</header>

<div class="mk-demo-banner" role="status">
  <div class="shs-container">
    <strong>DEMO</strong> — <?= htmlspecialchars($L['footer_demo'] ?? '') ?>
    · <a href="<?= htmlspecialchars($portal) ?>"><?= htmlspecialchars($L['cta_demo']) ?></a>
    · v<?= htmlspecialchars($version) ?>
  </div>
</div>

<section class="shs-hero">
  <div class="shs-container shs-hero-inner">
    <div class="shs-hero-content">
      <span class="shs-badge"><?= htmlspecialchars($L['badge']) ?> · v<?= htmlspecialchars($version) ?></span>
      <h1><?= htmlspecialchars($L['h1']) ?></h1>
      <p class="shs-hero-sub"><?= htmlspecialchars($L['sub']) ?></p>
      <div class="mk-tax-tags">
        <span>Мої клієнти</span><span>Мои услуги</span><span>My Clients</span>
        <span>Invoice</span><span>VAT</span><span>MVA</span><span>PVM</span><span>ПДВ</span>
      </div>
      <div class="shs-hero-cta" style="margin-top:22px">
        <a class="shs-btn-primary shs-btn-lg" href="<?= htmlspecialchars($portal) ?>"><i class="fas fa-door-open"></i> <?= htmlspecialchars($L['cta_demo']) ?></a>
        <a class="shs-btn-outline shs-btn-lg" href="<?= htmlspecialchars($admin) ?>"><i class="fas fa-users"></i> <?= htmlspecialchars($L['cta_admin']) ?></a>
        <a class="shs-btn-outline shs-btn-lg" href="<?= htmlspecialchars($clientLogin) ?>"><i class="fas fa-globe"></i> <?= htmlspecialchars($L['cta_client']) ?></a>
      </div>
    </div>
    <div class="shs-hero-preview">
      <?php if ($shots): $hero = $shots[0]; ?>
      <button type="button" class="shs-mockup" style="display:block;width:100%;padding:0;border:0;background:none;cursor:zoom-in" data-lb-open="0" aria-label="<?= htmlspecialchars($hero['label']) ?>">
        <div class="shs-mockup-header"><span></span><span></span><span></span><span class="shs-mockup-title">My Clients · cabinet</span></div>
        <img src="<?= htmlspecialchars($shotUrlBase . '/' . rawurlencode($hero['file'])) ?>" alt="<?= htmlspecialchars($hero['label']) ?>" width="640" height="400" style="width:100%;height:auto;display:block" loading="eager">
      </button>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="shs-section" id="intro">
  <div class="shs-container">
    <h2 class="shs-section-title"><?= htmlspecialchars($L['intro_h']) ?></h2>
    <p class="shs-lead" style="margin:0 auto;text-align:center;max-width:900px"><?= htmlspecialchars($L['intro']) ?></p>
    <p class="shs-use-label" style="text-align:center"><?= htmlspecialchars($L['use_h']) ?></p>
    <div class="shs-usecases" style="justify-content:center">
      <?php foreach ($useCases as $u): ?><span class="shs-usecase"><?= htmlspecialchars($u) ?></span><?php endforeach; ?>
    </div>
  </div>
</section>

<section class="shs-section" id="features" style="background:#f0fdfa">
  <div class="shs-container">
    <h2 class="shs-section-title"><?= htmlspecialchars($L['features_h']) ?></h2>
    <div class="shs-features-grid" style="grid-template-columns:repeat(auto-fill,minmax(280px,1fr))">
      <?php foreach ($features as $f): ?>
      <article class="shs-feature-card" id="<?= htmlspecialchars($f['id']) ?>">
        <div class="shs-feature-icon"><i class="fas <?= htmlspecialchars($f['icon']) ?>"></i></div>
        <h3><?= htmlspecialchars($f['title']) ?></h3>
        <p><?= htmlspecialchars($f['text']) ?></p>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="shs-section">
  <div class="shs-container">
    <h2 class="shs-section-title"><?= htmlspecialchars($L['modules_h']) ?></h2>
    <div class="mk-mod-grid" style="margin-bottom:28px">
      <?php foreach ($adminModules as $m): ?><span><i class="fas fa-check" style="color:#0d9488;margin-right:6px"></i><?= htmlspecialchars($m) ?></span><?php endforeach; ?>
    </div>
    <h2 class="shs-section-title"><?= htmlspecialchars($L['client_mod_h']) ?></h2>
    <div class="mk-mod-grid">
      <?php foreach ($clientModules as $m): ?><span><i class="fas fa-check" style="color:#0d9488;margin-right:6px"></i><?= htmlspecialchars($m) ?></span><?php endforeach; ?>
    </div>
  </div>
</section>

<section class="shs-section" id="shots">
  <div class="shs-container">
    <h2 class="shs-section-title"><?= htmlspecialchars($L['shots_h']) ?></h2>
    <p class="mk-shots-hint"><?= htmlspecialchars($L['shots_hint']) ?></p>
    <div class="mk-shot-grid">
      <?php foreach ($shots as $i => $s):
        $full = $shotUrlBase . '/' . rawurlencode($s['file']);
        $th = $shotUrlBase . '/' . rawurlencode($s['thumb']);
      ?>
      <figure class="mk-shot-card">
        <button type="button" data-lb-open="<?= (int) $i ?>" aria-label="<?= htmlspecialchars($s['label'] . ' — ' . $L['shots_h']) ?>">
          <img src="<?= htmlspecialchars($th) ?>"
               srcset="<?= htmlspecialchars($th) ?> 480w, <?= htmlspecialchars($full) ?> 1200w"
               sizes="(max-width:640px) 100vw, 360px"
               alt="<?= htmlspecialchars($s['label'] . ' — Мої клієнти / My Clients / Invoice') ?>"
               width="640" height="400" loading="lazy">
          <figcaption><?= htmlspecialchars($s['label']) ?></figcaption>
        </button>
      </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="shs-section" id="demo" style="background:#f8fafc">
  <div class="shs-container">
    <h2 class="shs-section-title"><?= htmlspecialchars($L['demo_h']) ?> · v<?= htmlspecialchars($version) ?></h2>
    <div class="mk-cred">
      <div class="mk-cred-card">
        <h3 style="margin:0 0 8px"><i class="fas fa-user-shield"></i> <?= htmlspecialchars($L['creds_admin']) ?></h3>
        <p style="margin:0 0 10px;color:#64748b"><?= htmlspecialchars($L['demo_admin_d']) ?></p>
        <p><?= htmlspecialchars($L['user']) ?>: <code>demo</code> / <code>admin</code><br>
           <?= htmlspecialchars($L['pass']) ?>: <code>demo</code> / <code>admin</code></p>
        <a class="shs-btn-primary" href="<?= htmlspecialchars($admin) ?>"><?= htmlspecialchars($L['cta_admin']) ?></a>
      </div>
      <div class="mk-cred-card">
        <h3 style="margin:0 0 8px"><i class="fas fa-globe"></i> <?= htmlspecialchars($L['creds_client']) ?></h3>
        <p style="margin:0 0 10px;color:#64748b"><?= htmlspecialchars($L['demo_client_d']) ?></p>
        <p><?= htmlspecialchars($L['user']) ?>: <code>anna.berg@demo.myclient</code><br>
           <?= htmlspecialchars($L['pass']) ?>: <code>demo</code></p>
        <a class="shs-btn-primary" href="<?= htmlspecialchars($clientLogin) ?>"><?= htmlspecialchars($L['cta_client']) ?></a>
      </div>
    </div>
    <p style="text-align:center;margin-top:20px">
      <a class="shs-btn-outline shs-btn-lg" href="<?= htmlspecialchars($portal) ?>"><i class="fas fa-rocket"></i> <?= htmlspecialchars($L['cta_demo']) ?></a>
    </p>
  </div>
</section>

<section class="shs-section" id="specs">
  <div class="shs-container mk-long" style="max-width:820px">
    <h2 class="shs-section-title"><?= htmlspecialchars($L['long_h']) ?></h2>
    <?php foreach ($features as $f): ?>
    <h3><?= htmlspecialchars($f['title']) ?></h3>
    <p><?= htmlspecialchars($f['text']) ?></p>
    <?php endforeach; ?>
    <h3><?= htmlspecialchars($L['stack_h']) ?></h3>
    <ul>
      <li>PHP 8+ · JSON storage · v<?= htmlspecialchars($version) ?></li>
      <li>My Clients cabinet · My services catalog · Client management</li>
      <li>Invoice · VAT / MVA / PVM / ПДВ tax %</li>
      <li>Stripe Checkout · PayPal REST · PDF Invoice</li>
      <li>Landing <?= htmlspecialchars($site_url) ?>/ · App <?= htmlspecialchars($cms_url) ?>/</li>
    </ul>
  </div>
</section>

<section class="shs-section" id="seo" style="background:#f0fdfa">
  <div class="shs-container" style="max-width:800px">
    <h2 class="shs-section-title"><?= htmlspecialchars($L['seo_h']) ?></h2>
    <ul style="line-height:1.85">
      <li>Schema: SoftwareApplication, WebPage, FAQPage, ImageGallery, BreadcrumbList, ItemList, Organization</li>
      <li>Open Graph + Twitter Card (image WebP, locale + alternates)</li>
      <li>Keywords: Мої клієнти · Мои услуги · My Clients · Invoice · VAT · MVA · PVM · ПДВ · оплата</li>
      <li>hreflang UA/RU/EN/NO/SV/PL · language dropdown</li>
      <li><a href="<?= htmlspecialchars($base) ?>/sitemap.php">sitemap</a> · <a href="<?= htmlspecialchars($base) ?>/robots.txt">robots</a> · <a href="<?= htmlspecialchars($base) ?>/llms.txt">llms.txt</a></li>
    </ul>
  </div>
</section>

<section class="shs-section" id="changelog" style="background:#f8fafc">
  <div class="shs-container" style="max-width:720px;text-align:center">
    <h2 class="shs-section-title"><?= htmlspecialchars($L['changelog_h'] ?? 'Changelog') ?></h2>
    <p class="shs-lead" style="margin:0 auto 16px"><?= htmlspecialchars($L['changelog_lead'] ?? '') ?> <strong>v<?= htmlspecialchars($version) ?></strong></p>
    <a class="shs-btn-outline" href="<?= htmlspecialchars($base . '/changelog.php?lang=' . $lang) ?>"><i class="fas fa-list"></i> <?= htmlspecialchars($L['footer_changelog'] ?? 'Changelog') ?> v<?= htmlspecialchars($version) ?></a>
  </div>
</section>

<section class="shs-section" id="faq">
  <div class="shs-container mk-faq" style="max-width:720px">
    <h2 class="shs-section-title"><?= htmlspecialchars($L['faq_h']) ?></h2>
    <?php foreach ($faq as $f): ?>
    <details>
      <summary><?= htmlspecialchars($f['q']) ?></summary>
      <p style="margin:10px 0 0;color:#475569"><?= htmlspecialchars($f['a']) ?></p>
    </details>
    <?php endforeach; ?>
  </div>
</section>

<footer class="mk-site-footer">
  <div class="shs-container">
    <div class="mk-footer-demo" role="note">
      <strong>DEMO</strong> — <?= htmlspecialchars($L['footer_demo'] ?? '') ?>
    </div>
    <div class="mk-footer-grid">
      <div>
        <div class="mk-footer-brand">My <em>Clients</em> <span class="mk-ver-pill">v<?= htmlspecialchars($version) ?></span></div>
        <p class="mk-footer-tag"><?= htmlspecialchars($L['footer']) ?></p>
      </div>
      <div>
        <h4><?= htmlspecialchars($L['footer_col_product'] ?? 'Product') ?></h4>
        <a href="<?= htmlspecialchars($base . '/?lang=' . $lang) ?>">Landing</a>
        <a href="<?= htmlspecialchars($base . '/changelog.php?lang=' . $lang) ?>"><?= htmlspecialchars($L['footer_changelog'] ?? 'Changelog') ?></a>
        <a href="<?= htmlspecialchars($install) ?>"><?= htmlspecialchars($L['footer_install'] ?? 'Install') ?></a>
        <a href="<?= htmlspecialchars($base) ?>/sitemap.php">Sitemap</a>
        <a href="<?= htmlspecialchars($base) ?>/llms.txt">llms.txt</a>
      </div>
      <div>
        <h4><?= htmlspecialchars($L['footer_col_demo'] ?? 'Demo') ?></h4>
        <a href="<?= htmlspecialchars($portal) ?>"><?= htmlspecialchars($L['cta_demo']) ?></a>
        <a href="<?= htmlspecialchars($admin) ?>"><?= htmlspecialchars($L['cta_admin']) ?></a>
        <a href="<?= htmlspecialchars($clientLogin) ?>"><?= htmlspecialchars($L['cta_client']) ?></a>
      </div>
      <div>
        <h4><?= htmlspecialchars($L['footer_col_legal'] ?? 'Policies') ?></h4>
        <a href="https://bilohash.com/website/privacy-policy.php"><?= htmlspecialchars($L['footer_privacy'] ?? 'Privacy') ?></a>
        <a href="https://bilohash.com/website/cookies.php"><?= htmlspecialchars($L['footer_cookies'] ?? 'Cookies') ?></a>
        <a href="https://bilohash.com/contact.php"><?= htmlspecialchars($L['footer_contact'] ?? 'Contact') ?></a>
        <a href="https://bilohash.com/about.php"><?= htmlspecialchars($L['footer_about'] ?? 'About') ?></a>
      </div>
      <div>
        <h4><?= htmlspecialchars($L['footer_col_eco'] ?? 'Ecosystem') ?></h4>
        <a href="https://bilohash.com/"><?= htmlspecialchars($L['footer_home'] ?? 'bilohash.com') ?></a>
        <a href="https://bilohash.com/news.php"><?= htmlspecialchars($L['footer_news'] ?? 'News') ?></a>
        <a href="https://bilohash.com/ecosystem/join.php"><?= htmlspecialchars($L['footer_join'] ?? 'Join') ?></a>
        <a href="https://bilohash.com/ecosystem/install.php">Install guide</a>
      </div>
    </div>

    <div class="mk-footer-scripts">
      <h4><?= htmlspecialchars($footerScriptsTitle) ?></h4>
      <div class="mk-footer-pills" role="navigation" aria-label="<?= htmlspecialchars($footerScriptsTitle) ?>">
        <?php foreach ($ecoScripts as $es):
            $esLabel = $es['label'][$lang] ?? $es['label']['en'] ?? $es['slug'];
        ?>
        <a href="<?= htmlspecialchars($es['url']) ?>" rel="noopener"><?= htmlspecialchars($esLabel) ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="mk-footer-bottom">
      <span>© <?= $year ?> BILOHASH · My Clients v<?= htmlspecialchars($version) ?></span>
      <span><?= htmlspecialchars($L['footer_rights'] ?? '') ?></span>
    </div>
  </div>
</footer>

<div class="ds-sticky-cta">
  <a class="shs-btn-primary" href="<?= htmlspecialchars($portal) ?>"><i class="fas fa-rocket"></i> Demo</a>
</div>

<!-- Lightbox -->
<div class="mk-lb" id="mkLb" role="dialog" aria-modal="true" aria-label="<?= htmlspecialchars($L['shots_h']) ?>" hidden>
  <button type="button" class="mk-lb-btn mk-lb-close" data-lb-close aria-label="<?= htmlspecialchars($L['lb_close']) ?>">✕</button>
  <button type="button" class="mk-lb-btn mk-lb-prev" data-lb-prev aria-label="<?= htmlspecialchars($L['lb_prev']) ?>">‹</button>
  <button type="button" class="mk-lb-btn mk-lb-next" data-lb-next aria-label="<?= htmlspecialchars($L['lb_next']) ?>">›</button>
  <div class="mk-lb-inner">
    <div class="mk-lb-img-wrap">
      <img class="mk-lb-img" id="mkLbImg" src="" alt="">
    </div>
    <div class="mk-lb-cap" id="mkLbCap"></div>
    <div class="mk-lb-count" id="mkLbCount"></div>
  </div>
</div>

<script>
window.MK_SHOTS = <?= json_encode($lbJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
(function () {
  /* Shared dropdowns: language + nav categories */
  function closeAllDrops(except) {
    document.querySelectorAll('[data-nav-drop], [data-lang-drop]').forEach(function (el) {
      if (except && el === except) return;
      var m = el.querySelector('[data-nav-menu], [data-lang-menu]');
      var b = el.querySelector('[data-nav-btn], [data-lang-btn]');
      if (m) m.hidden = true;
      if (b) b.setAttribute('aria-expanded', 'false');
    });
  }
  document.querySelectorAll('[data-nav-drop], [data-lang-drop]').forEach(function (drop) {
    var btn = drop.querySelector('[data-nav-btn], [data-lang-btn]');
    var menu = drop.querySelector('[data-nav-menu], [data-lang-menu]');
    if (!btn || !menu) return;
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      var open = menu.hidden;
      closeAllDrops(drop);
      menu.hidden = !open;
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    menu.querySelectorAll('a[href^="#"]').forEach(function (a) {
      a.addEventListener('click', function () { closeAllDrops(); });
    });
  });
  document.addEventListener('click', function () { closeAllDrops(); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAllDrops();
  });

  /* Lightbox */
  var shots = window.MK_SHOTS || [];
  var lb = document.getElementById('mkLb');
  var img = document.getElementById('mkLbImg');
  var cap = document.getElementById('mkLbCap');
  var count = document.getElementById('mkLbCount');
  var idx = 0;
  if (!lb || !img || !shots.length) return;

  function show(i) {
    idx = (i + shots.length) % shots.length;
    var s = shots[idx];
    img.src = s.src;
    img.alt = s.alt || '';
    if (cap) cap.textContent = s.cap || s.alt || '';
    if (count) count.textContent = (idx + 1) + ' / ' + shots.length;
  }
  function open(i) {
    show(i);
    lb.hidden = false;
    lb.classList.add('is-open');
    document.body.classList.add('mk-lb-lock');
  }
  function close() {
    lb.classList.remove('is-open');
    document.body.classList.remove('mk-lb-lock');
    setTimeout(function () { lb.hidden = true; img.removeAttribute('src'); }, 180);
  }
  function prev() { show(idx - 1); }
  function next() { show(idx + 1); }

  document.querySelectorAll('[data-lb-open]').forEach(function (el) {
    el.addEventListener('click', function () {
      open(parseInt(el.getAttribute('data-lb-open') || '0', 10) || 0);
    });
  });
  lb.querySelectorAll('[data-lb-close]').forEach(function (b) { b.addEventListener('click', close); });
  var p = lb.querySelector('[data-lb-prev]');
  var n = lb.querySelector('[data-lb-next]');
  if (p) p.addEventListener('click', function (e) { e.stopPropagation(); prev(); });
  if (n) n.addEventListener('click', function (e) { e.stopPropagation(); next(); });
  lb.addEventListener('click', function (e) {
    if (e.target === lb) close();
  });
  document.addEventListener('keydown', function (e) {
    if (!lb.classList.contains('is-open')) return;
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowLeft') prev();
    if (e.key === 'ArrowRight') next();
  });
})();
</script>
</body>
</html>
