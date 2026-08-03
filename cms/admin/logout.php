<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
mk_boot('admin');
mk_admin_logout();
mk_redirect(mk_base_url('admin/login.php'));
