<?php

namespace Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailerException;

class Mailer
{
    private PHPMailer $mailer;

    public function __construct()
    {
        $mailer = new PHPMailer(true);

        $mailer->isSMTP();
        $mailer->Host       = (string) ($_ENV['MAIL_HOST']     ?? 'localhost');
        $mailer->Port       = (int)    ($_ENV['MAIL_PORT']     ?? 587);
        $mailer->SMTPSecure = (string) ($_ENV['MAIL_ENCRYPTION'] ?? PHPMailer::ENCRYPTION_STARTTLS);
        $mailer->CharSet    = PHPMailer::CHARSET_UTF8;

        $username = (string) ($_ENV['MAIL_USERNAME'] ?? '');
        $password = (string) ($_ENV['MAIL_PASSWORD'] ?? '');

        if ($username !== '' && $password !== '') {
            $mailer->SMTPAuth = true;
            $mailer->Username = $username;
            $mailer->Password = $password;
        }

        $fromAddress = (string) ($_ENV['MAIL_FROM_ADDRESS'] ?? $username);
        $fromName    = (string) ($_ENV['MAIL_FROM_NAME']    ?? 'Web Revolution');

        if ($fromAddress !== '') {
            $mailer->setFrom($fromAddress, $fromName);
        }

        $this->mailer = $mailer;
    }

    /**
     * Envia un correo HTML (con fallback en texto plano).
     *
     * @throws MailerException
     */
    public function send(string $to, string $subject, string $htmlBody, string $altBody = ''): void
    {
        $this->mailer->clearAddresses();
        $this->mailer->addAddress($to);
        $this->mailer->isHTML(true);
        $this->mailer->Subject = $subject;
        $this->mailer->Body    = $htmlBody;
        $this->mailer->AltBody = $altBody !== '' ? $altBody : strip_tags($htmlBody);

        $this->mailer->send();
    }
}
