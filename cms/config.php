<?php
declare(strict_types=1);

/**
 * Мои клиенты / My Clients — standalone CRM.
 * No external libraries, no third-party APIs — only Stripe + PayPal for payments.
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');

define('MK_ROOT', __DIR__);
define('MK_DATA_DIR', MK_ROOT . '/data');
define('MK_SITE_NAME', 'My Clients');
/** Single source of truth: VERSION file next to this config (shared with landing). */
if (!defined('MK_VERSION')) {
    $__mkVerFile = MK_ROOT . '/VERSION';
    $__mkVer = is_readable($__mkVerFile) ? trim((string) file_get_contents($__mkVerFile)) : '';
    define('MK_VERSION', $__mkVer !== '' ? $__mkVer : '1.0.0');
}
define('MK_CURRENCY', 'NOK');
define('MK_TIMEZONE', 'Europe/Oslo');

date_default_timezone_set(MK_TIMEZONE);

if (!is_dir(MK_DATA_DIR)) {
    mkdir(MK_DATA_DIR, 0755, true);
}

// Secrets (Stripe/PayPal keys, mail sender) live outside version control.
$mkLocalConfig = MK_ROOT . '/data/config.local.php';
if (is_file($mkLocalConfig)) {
    require_once $mkLocalConfig;
}

if (!defined('MK_STRIPE_SECRET_KEY')) {
    define('MK_STRIPE_SECRET_KEY', '');
}
if (!defined('MK_STRIPE_PUBLISHABLE_KEY')) {
    define('MK_STRIPE_PUBLISHABLE_KEY', '');
}
if (!defined('MK_STRIPE_WEBHOOK_SECRET')) {
    define('MK_STRIPE_WEBHOOK_SECRET', '');
}
if (!defined('MK_PAYPAL_CLIENT_ID')) {
    define('MK_PAYPAL_CLIENT_ID', '');
}
if (!defined('MK_PAYPAL_CLIENT_SECRET')) {
    define('MK_PAYPAL_CLIENT_SECRET', '');
}
if (!defined('MK_PAYPAL_ENV')) {
    define('MK_PAYPAL_ENV', 'live'); // 'live' or 'sandbox'
}
if (!defined('MK_MAIL_FROM')) {
    define('MK_MAIL_FROM', 'no-reply@bilohash.com');
}
if (!defined('MK_MAIL_FROM_NAME')) {
    define('MK_MAIL_FROM_NAME', 'My Clients');
}
if (!defined('MK_DEMO_MODE')) {
    define('MK_DEMO_MODE', false);
}
if (!defined('MK_BASE_URL')) {
    // Auto-detect when hosted under /myclient/cms or similar subdirectory.
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
    // If running from /admin/*, go up one level for public base.
    if (str_ends_with($dir, '/admin')) {
        $dir = substr($dir, 0, -6);
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $auto = $scheme . '://' . $host . ($dir === '' || $dir === '/' ? '' : $dir);
    define('MK_BASE_URL', $auto !== '' ? $auto : 'https://bilohash.com/myclient/cms');
}

require_once MK_ROOT . '/includes/helpers.php';
require_once MK_ROOT . '/includes/storage.php';
require_once MK_ROOT . '/includes/i18n.php';
require_once MK_ROOT . '/includes/csrf.php';
require_once MK_ROOT . '/includes/auth.php';
require_once MK_ROOT . '/includes/settings.php';
require_once MK_ROOT . '/includes/services.php';
require_once MK_ROOT . '/includes/clients.php';
require_once MK_ROOT . '/includes/invoices.php';
require_once MK_ROOT . '/includes/mail.php';
require_once MK_ROOT . '/includes/qr.php';
require_once MK_ROOT . '/includes/pdf.php';
require_once MK_ROOT . '/includes/stripe.php';
require_once MK_ROOT . '/includes/paypal.php';
require_once MK_ROOT . '/includes/layout.php';
require_once MK_ROOT . '/includes/init.php';
