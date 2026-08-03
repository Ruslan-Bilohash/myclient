<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
mk_boot('client');
mk_client_logout();
mk_redirect(mk_base_url('login.php'));
