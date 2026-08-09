<?php

class Mailer
{
    // Envía un correo. Si no hay servidor SMTP configurado, guarda el correo
    // en logs/emails/ para poder recuperar el enlace en desarrollo.
    public static function send(string $para, string $asunto, string $html): bool
    {
        $host = getenv('MAIL_HOST');
        $from = getenv('MAIL_FROM') ?: 'no-reply@bodyshop.local';

        if ($host) {
            return self::enviarSmtp($para, $asunto, $html, $from);
        }

        return self::guardarLocal($para, $asunto, $html);
    }

    private static function guardarLocal(string $para, string $asunto, string $html): bool
    {
        $dir = BASE_PATH . '/logs/emails';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $archivo = $dir . '/' . date('Y-m-d_H-i-s') . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $para) . '.html';
        $contenido = "<h2>Para: " . Security::escape($para) . "</h2><h3>Asunto: " . Security::escape($asunto) . "</h3><hr>" . $html;

        return file_put_contents($archivo, $contenido) !== false;
    }

    private static function enviarSmtp(string $para, string $asunto, string $html, string $from): bool
    {
        $host = getenv('MAIL_HOST');
        $port = (int)(getenv('MAIL_PORT') ?: 587);
        $user = getenv('MAIL_USER');
        $pass = getenv('MAIL_PASS');

        $header = "MIME-Version: 1.0\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "From: $from\r\n"
            . "To: $para\r\n"
            . "Subject: =?UTF-8?B?" . base64_encode($asunto) . "?=\r\n";

        $socket = @fsockopen($host, $port, $errno, $errstr, 10);
        if (!$socket) {
            return false;
        }

        $leer = fn() => fgets($socket, 512);

        $leer(); // banner
        fwrite($socket, "EHLO bodyshop\r\n");
        while (strpos($leer(), '250 ') === false) { /* espera respuesta final */ }

        if ($user) {
            fwrite($socket, "AUTH LOGIN\r\n");
            $leer();
            fwrite($socket, base64_encode($user) . "\r\n");
            $leer();
            fwrite($socket, base64_encode($pass) . "\r\n");
            $leer();
        }

        fwrite($socket, "MAIL FROM:<$from>\r\n");
        $leer();
        fwrite($socket, "RCPT TO:<$para>\r\n");
        $leer();
        fwrite($socket, "DATA\r\n");
        $leer();
        fwrite($socket, $header . "\r\n" . $html . "\r\n.\r\n");
        $leer();
        fwrite($socket, "QUIT\r\n");

        fclose($socket);
        return true;
    }
}
