<?php
declare(strict_types=1);

function mk_smtp_enabled(): bool
{
    return defined('MK_SMTP_HOST') && MK_SMTP_HOST !== '';
}

/**
 * @param list<array{filename:string,content:string,mime:string}> $attachments
 */
function mk_mail_build_message(string $htmlBody, array $attachments): array
{
    // quoted-printable, not 8bit: the HTML is built as one long unbroken line, and RFC 5321 caps
    // SMTP lines at 1000 octets — quoted-printable inserts soft line breaks so long lines don't
    // get the message rejected ("500 Line too long") by strict SMTP servers.
    $qpBody = quoted_printable_encode($htmlBody);

    if ($attachments === []) {
        return ['Content-Type: text/html; charset=UTF-8' . "\r\n" . 'Content-Transfer-Encoding: quoted-printable', $qpBody];
    }

    $boundary = 'mkbnd_' . bin2hex(random_bytes(12));
    $parts = "--{$boundary}\r\n"
        . "Content-Type: text/html; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: quoted-printable\r\n\r\n"
        . $qpBody . "\r\n";

    foreach ($attachments as $att) {
        $parts .= "--{$boundary}\r\n"
            . 'Content-Type: ' . $att['mime'] . '; name="' . $att['filename'] . "\"\r\n"
            . "Content-Transfer-Encoding: base64\r\n"
            . 'Content-Disposition: attachment; filename="' . $att['filename'] . "\"\r\n\r\n"
            . chunk_split(base64_encode($att['content'])) . "\r\n";
    }
    $parts .= "--{$boundary}--";

    return ['Content-Type: multipart/mixed; boundary="' . $boundary . '"', $parts];
}

/**
 * Minimal SMTP client over a raw socket — no library. Used instead of PHP's mail(), because on
 * shared hosting mail() reports success (handed off locally) even when the message never actually
 * reaches the recipient (no SPF/DKIM alignment for the sending domain). Authenticating as a real
 * mailbox and relaying through the provider's own outbound server is far more likely to deliver.
 * @param list<array{filename:string,content:string,mime:string}> $attachments
 */
function mk_smtp_send(string $to, string $subject, string $htmlBody, array $attachments = []): bool
{
    $host = (string) MK_SMTP_HOST;
    $port = (int) (defined('MK_SMTP_PORT') ? MK_SMTP_PORT : 587);
    $user = (string) (defined('MK_SMTP_USER') ? MK_SMTP_USER : '');
    $pass = (string) (defined('MK_SMTP_PASS') ? MK_SMTP_PASS : '');
    $secure = defined('MK_SMTP_SECURE') ? (string) MK_SMTP_SECURE : 'tls'; // 'ssl' (implicit) or 'tls' (STARTTLS)
    $from = defined('MK_MAIL_FROM') ? MK_MAIL_FROM : $user;
    $fromName = defined('MK_MAIL_FROM_NAME') ? MK_MAIL_FROM_NAME : 'My Clients';

    $transport = ($secure === 'ssl' ? 'ssl://' : '') . $host;
    $fp = @stream_socket_client($transport . ':' . $port, $errno, $errstr, 15);
    if ($fp === false) {
        return false;
    }
    stream_set_timeout($fp, 15);

    $ehloName = (string) (parse_url(defined('MK_BASE_URL') ? MK_BASE_URL : '', PHP_URL_HOST) ?: 'localhost');

    $read = static function () use ($fp): string {
        $data = '';
        while (($line = fgets($fp, 515)) !== false) {
            $data .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }

        return $data;
    };
    $write = static function (string $cmd) use ($fp): void {
        fwrite($fp, $cmd . "\r\n");
    };
    $code = static fn(string $resp): string => substr($resp, 0, 3);

    $read(); // server greeting
    $write('EHLO ' . $ehloName);
    $read();

    if ($secure === 'tls') {
        $write('STARTTLS');
        if ($code($read()) !== '220') {
            fclose($fp);

            return false;
        }
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($fp);

            return false;
        }
        $write('EHLO ' . $ehloName);
        $read();
    }

    if ($user !== '') {
        $write('AUTH LOGIN');
        $read();
        $write(base64_encode($user));
        $read();
        $write(base64_encode($pass));
        if ($code($read()) !== '235') {
            $write('QUIT');
            fclose($fp);

            return false;
        }
    }

    $write('MAIL FROM:<' . $from . '>');
    if ($code($read()) !== '250') {
        fclose($fp);

        return false;
    }
    $write('RCPT TO:<' . $to . '>');
    if ($code($read())[0] !== '2') {
        fclose($fp);

        return false;
    }
    $write('DATA');
    if ($code($read()) !== '354') {
        fclose($fp);

        return false;
    }

    [$contentTypeHeader, $messageBody] = mk_mail_build_message($htmlBody, $attachments);
    $headers = "From: {$fromName} <{$from}>\r\n"
        . "To: <{$to}>\r\n"
        . 'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\r\n"
        . "MIME-Version: 1.0\r\n"
        . $contentTypeHeader . "\r\n\r\n";
    $body = preg_replace('/^\./m', '..', $messageBody); // dot-stuffing per RFC 5321
    $write($headers . $body . "\r\n.");
    $sent = $code($read()) === '250';

    $write('QUIT');
    fclose($fp);

    return $sent;
}

/** @param list<array{filename:string,content:string,mime:string}> $attachments */
function mk_mail_send(string $to, string $subject, string $htmlBody, array $attachments = []): bool
{
    if (mk_smtp_enabled()) {
        $ok = mk_smtp_send($to, $subject, $htmlBody, $attachments);
        if (!$ok) {
            mk_mail_log($to, $subject, $htmlBody);
        }

        return $ok;
    }

    $from = defined('MK_MAIL_FROM') ? MK_MAIL_FROM : 'no-reply@localhost';
    $fromName = defined('MK_MAIL_FROM_NAME') ? MK_MAIL_FROM_NAME : 'My Clients';

    [$contentTypeHeader, $messageBody] = mk_mail_build_message($htmlBody, $attachments);
    $headers = [
        'MIME-Version: 1.0',
        $contentTypeHeader,
        'From: ' . $fromName . ' <' . $from . '>',
        'Reply-To: ' . $from,
        'X-Mailer: MyClients',
    ];

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

    $ok = false;
    if (function_exists('mail')) {
        $ok = @mail($to, $encodedSubject, $messageBody, implode("\r\n", $headers));
    }

    if (!$ok) {
        mk_mail_log($to, $subject, $htmlBody);
    }

    return $ok;
}

function mk_mail_log(string $to, string $subject, string $body): void
{
    $dir = MK_DATA_DIR . '/logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $line = '[' . mk_now() . "] to={$to} subject=" . str_replace("\n", ' ', $subject) . "\n" . $body . "\n---\n";
    @file_put_contents($dir . '/mail.log', $line, FILE_APPEND | LOCK_EX);
}

function mk_mail_template(string $title, string $bodyHtml, array $t, string $lang = 'uk'): string
{
    $year = date('Y');
    $footer = str_replace('{year}', $year, $t['footer_copyright'] ?? '© {year} My Clients');
    $loginUrl = mk_base_url('login.php');
    $loginLabel = $t['client_login_title'] ?? 'Client cabinet';
    $siteName = mk_h($t['site_name_client'] ?? $t['site_name'] ?? MK_SITE_NAME);

    return '<!DOCTYPE html><html lang="' . mk_h($lang) . '"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1"></head>'
        . '<body style="margin:0;padding:0;background:#f3f4fb;font-family:-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#131826;">'
        . '<div style="max-width:560px;margin:0 auto;padding:36px 20px;">'

        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">'
        . '<tr><td style="font-size:15px;font-weight:800;color:#1e2130;">'
        . '<span style="display:inline-block;width:28px;height:28px;border-radius:8px;background:#2563eb;color:#fff;text-align:center;line-height:28px;margin-right:8px;vertical-align:middle;">🗂️</span>'
        . '<span style="vertical-align:middle;">' . $siteName . '</span>'
        . '</td></tr></table>'

        . '<div style="background:#ffffff;border:1px solid #e3e6ec;border-radius:16px;padding:32px;box-shadow:0 8px 30px rgba(31,35,66,.06);">'
        . '<div style="height:4px;border-radius:4px;background:linear-gradient(90deg,#2563eb,#60a5fa);margin:-32px -32px 24px;"></div>'
        . '<h1 style="margin:0 0 18px;font-size:20px;line-height:1.35;color:#131826;">' . mk_h($title) . '</h1>'
        . $bodyHtml
        . '<div style="margin-top:28px;padding-top:20px;border-top:1px solid #eceef4;">'
        . '<p style="margin:0;font-size:13px;color:#767b96;">'
        . '<a href="' . mk_h($loginUrl) . '" style="color:#2563eb;font-weight:600;text-decoration:none;">→ ' . mk_h($loginLabel) . '</a>'
        . '</p></div>'
        . '</div>'

        . '<p style="text-align:center;color:#9498ad;font-size:12px;margin-top:24px;">' . mk_h($footer) . '</p>'
        . '</div></body></html>';
}

function mk_mail_button(string $url, string $label): string
{
    return '<p style="margin:24px 0;"><a href="' . mk_h($url) . '" '
        . 'style="background:#2563eb;color:#ffffff;text-decoration:none;padding:13px 26px;border-radius:10px;'
        . 'font-weight:700;font-size:14px;display:inline-block;">' . mk_h($label) . '</a></p>';
}

/** @return list<array{filename:string,content:string,mime:string}> */
function mk_mail_invoice_pdf_attachment(array $client, array $invoice, string $lang): array
{
    $invoiceForPdf = $invoice;
    $invoiceForPdf['lines'] = mk_invoice_display_lines($invoice, $lang);
    $pdf = mk_pdf_invoice_bytes($invoiceForPdf, $client, mk_load_lang($lang));

    $filename = preg_replace('/[^A-Za-z0-9_-]/', '-', (string) $invoice['number']) . '.pdf';

    return [['filename' => $filename, 'content' => $pdf, 'mime' => 'application/pdf']];
}

function mk_mail_invoice_created(array $client, array $invoice, array $t): bool
{
    $email = (string) ($client['email'] ?? '');
    if ($email === '') {
        return false;
    }
    $lang = (string) ($client['lang'] ?? 'uk');
    $amount = mk_money((float) ($invoice['total'] ?? 0), (string) ($invoice['currency'] ?? MK_CURRENCY));
    $subject = str_replace(['{number}', '{site}'], [(string) $invoice['number'], MK_SITE_NAME], $t['mail_invoice_subject'] ?? 'New invoice {number}');
    $intro = str_replace(['{number}', '{amount}'], [(string) $invoice['number'], $amount], $t['mail_invoice_body_intro'] ?? '');
    $link = mk_base_url('invoice-view.php?id=' . rawurlencode((string) $invoice['id']));

    $body = '<p style="font-size:15px;line-height:1.6;color:#333a4d;">' . mk_h($intro) . '</p>'
        . mk_mail_button($link, $t['btn_view'] ?? 'View');

    $attachments = mk_mail_invoice_pdf_attachment($client, $invoice, $lang);

    return mk_mail_send($email, $subject, mk_mail_template($subject, $body, $t, $lang), $attachments);
}

function mk_mail_invoice_paid(array $client, array $invoice, array $t): bool
{
    $email = (string) ($client['email'] ?? '');
    if ($email === '') {
        return false;
    }
    $lang = (string) ($client['lang'] ?? 'uk');
    $amount = mk_money((float) ($invoice['total'] ?? 0), (string) ($invoice['currency'] ?? MK_CURRENCY));
    $subject = str_replace('{number}', (string) $invoice['number'], $t['mail_invoice_paid_subject'] ?? 'Payment received {number}');
    $body = str_replace(['{number}', '{amount}'], [(string) $invoice['number'], $amount], $t['mail_invoice_paid_body'] ?? '');
    $link = mk_base_url('invoice-view.php?id=' . rawurlencode((string) $invoice['id']));

    $bodyHtml = '<p style="font-size:15px;line-height:1.6;color:#333a4d;">' . mk_h($body) . '</p>'
        . mk_mail_button($link, $t['btn_view'] ?? 'View');

    $attachments = mk_mail_invoice_pdf_attachment($client, $invoice, $lang);

    return mk_mail_send($email, $subject, mk_mail_template($subject, $bodyHtml, $t, $lang), $attachments);
}
