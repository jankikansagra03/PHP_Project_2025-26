<?php
// ══════════════════════════════════════════════════════
//  mailer.php — PHP_Project_2025-26
//  Sends HTML emails using PHPMailer + SMTP
//  Config is read from .env via env() helper.
//  Usage:  include 'mailer.php'; sendEmail($to, $subject, $body);
// ══════════════════════════════════════════════════════

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';
require_once 'PHPMailer/Exception.php';

if (!function_exists('env')) {
    require_once __DIR__ . '/env.php';
}

/**
 * Send an HTML email via SMTP (configured in .env).
 *
 * @param  string      $to      Recipient email address
 * @param  string      $subject Email subject line
 * @param  string      $body    HTML body content
 * @param  string|null $file    Optional file attachment path
 * @return true|string          Returns true on success, error string on failure
 */
function sendEmail(string $to, string $subject, string $body, ?string $file = null)
{
    $mail = new PHPMailer(true);

    try {
        // ── SMTP ──────────────────────────────────────────────
        $mail->isSMTP();
        $mail->SMTPAuth   = true;
        $mail->Host       = env('MAIL_HOST',       'smtp.gmail.com');
        $mail->Port       = (int)env('MAIL_PORT',  465);
        $mail->SMTPSecure = env('MAIL_ENCRYPTION', 'ssl');
        $mail->Username   = env('MAIL_USERNAME',   '');
        $mail->Password   = env('MAIL_PASSWORD',   '');

        // Disable SSL peer verification in local/dev env
        if (env('APP_ENV', 'local') === 'local') {
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ];
        }

        // ── Sender & Recipient ────────────────────────────────
        $mail->setFrom(
            env('MAIL_FROM_ADDRESS', env('MAIL_USERNAME', '')),
            env('MAIL_FROM_NAME',    'JK Store')
        );
        $mail->addReplyTo(
            env('MAIL_FROM_ADDRESS', env('MAIL_USERNAME', '')),
            env('MAIL_FROM_NAME', 'JK Store') . ' Support'
        );
        $mail->addAddress($to);

        // ── Content ───────────────────────────────────────────
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        // ── Optional Attachment ───────────────────────────────
        if ($file && file_exists($file)) {
            $mail->addAttachment($file);
        }

        $mail->send();
        return true;

    } catch (Exception $e) {
        return 'Email failed: ' . $mail->ErrorInfo;
    }
}
