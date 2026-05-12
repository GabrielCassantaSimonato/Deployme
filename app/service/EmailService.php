<?php

namespace app\service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{

    public static function enviarBoasVindas($email, $nome)
    {

        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['EMAIL_USER'];
            $mail->Password = $_ENV['EMAIL_PASS'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($_ENV['EMAIL_USER'], 'Deployme');
            $mail->addAddress($email, $nome);

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';

            $mail->Subject = 'Bem-vindo à Deployme 🚀';

            ob_start();
            require '../app/view/Email/welcomeEmail.php';

            $template = ob_get_clean();

            $mail->Body = $template;

            $mail->send();

            return true;

        } catch (Exception $e) {

            return false;
        }
    }
}