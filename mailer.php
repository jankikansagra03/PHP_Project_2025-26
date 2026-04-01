<?php
// ============================================================
// mailer.php — PHP_Project_2025-26
// Sends HTML emails using PHPMailer + Gmail SMTP
// Usage: include 'mailer.php'; sendEmail($to, $subject, $body);
// ============================================================

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require('PHPMailer/PHPMailer.php');
require('PHPMailer/SMTP.php');
require('PHPMailer/Exception.php');

/**
 * Send an HTML email via Gmail SMTP.
 *
 * @param string      $to      Recipient email address
 * @param string      $subject Email subject line
 * @param string      $body    HTML body content
 * @param string|null $file    Optional file attachment path (or null)
 * @return true|string         Returns true on success, error string on failure
 */
function sendEmail($to, $subject, $body, $file = null)
{
    $mail = new PHPMailer(true); // Enable exceptions

    try {
        // ---- SMTP Configuration ----
        $mail->isSMTP();
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = 'ssl';   //tls
        $mail->Host       = 'smtp.gmail.com';
        $mail->Port       = 465; //587
        $mail->Username   = 'kansagrajanki@gmail.com';
        $mail->Password   = '';   // <-- Add your Gmail App Password here

        // Disable SSL peer verification (for local/dev environments)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ]
        ];

        // ---- Sender & Recipient ----
        $mail->setFrom('kansagrajanki@gmail.com', 'JK Store');
        $mail->addReplyTo('kansagrajanki@gmail.com', 'JK Store Support');
        $mail->addAddress($to);

        // ---- Content ----
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body); // Plain-text fallback

        // ---- Optional Attachment ----
        if ($file && file_exists($file)) {
            $mail->addAttachment($file);
        }

        $mail->send();
        return true;
    } catch (Exception $e) {
        return 'Email failed: ' . $mail->ErrorInfo;
    }
}
