<?php
/**
 * Mailer — wrapper SMTP do wysyłki e-maili
 * Panel Pracowniczy Firma KOT
 *
 * Używa natywnego rozszerzenia PHP streams (nie wymaga zewnętrznych bibliotek).
 * Obsługuje STARTTLS (port 587) i SSL (port 465).
 */

class Mailer {
    private string $host;
    private int    $port;
    private string $username;
    private string $password;
    private string $encryption; // tls | ssl
    private string $fromEmail;
    private string $fromName;

    public function __construct() {
        $this->host       = MAIL_HOST;
        $this->port       = MAIL_PORT;
        $this->username   = MAIL_USERNAME;
        $this->password   = MAIL_PASSWORD;
        $this->encryption = MAIL_ENCRYPTION;
        $this->fromEmail  = MAIL_FROM;
        $this->fromName   = MAIL_FROM_NAME;
    }

    /**
     * Wyślij e-mail.
     *
     * @param string $to      Adres odbiorcy
     * @param string $subject Temat
     * @param string $body    Treść HTML
     * @return bool
     */
    public function send(string $to, string $subject, string $body): bool {
        // Jeśli brak konfiguracji SMTP — spróbuj przez mail()
        if (empty($this->username) || empty($this->password)) {
            return $this->sendViaMail($to, $subject, $body);
        }

        return $this->sendViaSmtp($to, $subject, $body);
    }

    private function sendViaMail(string $to, string $subject, string $body): bool {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . $this->encodeName($this->fromName) . " <" . $this->fromEmail . ">\r\n";

        return mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
    }

    private function sendViaSmtp(string $to, string $subject, string $body): bool {
        try {
            $ssl = ($this->encryption === 'ssl');
            $host = ($ssl ? 'ssl://' : '') . $this->host;

            $socket = fsockopen($host, $this->port, $errno, $errstr, 15);
            if (!$socket) {
                error_log("Mailer: połączenie SMTP nieudane: $errstr ($errno)");
                return false;
            }

            stream_set_timeout($socket, 15);

            $this->expect($socket, '220');

            // EHLO
            $this->send_cmd($socket, 'EHLO ' . gethostname());
            $ehlo = $this->read($socket);

            // STARTTLS jeśli nie SSL
            if (!$ssl && $this->encryption === 'tls') {
                $this->send_cmd($socket, 'STARTTLS');
                $this->expect_raw($socket, '220');
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $this->send_cmd($socket, 'EHLO ' . gethostname());
                $this->read($socket);
            }

            // AUTH LOGIN
            $this->send_cmd($socket, 'AUTH LOGIN');
            $this->expect($socket, '334');
            $this->send_cmd($socket, base64_encode($this->username));
            $this->expect($socket, '334');
            $this->send_cmd($socket, base64_encode($this->password));
            $this->expect($socket, '235');

            // FROM / TO
            $this->send_cmd($socket, 'MAIL FROM:<' . $this->fromEmail . '>');
            $this->expect($socket, '250');
            $this->send_cmd($socket, 'RCPT TO:<' . $to . '>');
            $this->expect($socket, '250');

            // DATA
            $this->send_cmd($socket, 'DATA');
            $this->expect($socket, '354');

            $date    = date('r');
            $msgId   = '<' . uniqid('kot', true) . '@' . gethostname() . '>';
            $encoded = '=?UTF-8?B?' . base64_encode($subject) . '?=';
            $from    = $this->encodeName($this->fromName) . ' <' . $this->fromEmail . '>';

            $message  = "Date: $date\r\n";
            $message .= "From: $from\r\n";
            $message .= "To: $to\r\n";
            $message .= "Message-ID: $msgId\r\n";
            $message .= "Subject: $encoded\r\n";
            $message .= "MIME-Version: 1.0\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n";
            $message .= "\r\n";
            $message .= chunk_split(base64_encode($body));
            $message .= "\r\n.";

            fwrite($socket, $message . "\r\n");
            $this->expect($socket, '250');

            $this->send_cmd($socket, 'QUIT');
            fclose($socket);
            return true;

        } catch (Exception $e) {
            error_log('Mailer SMTP error: ' . $e->getMessage());
            return false;
        }
    }

    private function send_cmd($socket, string $cmd): void {
        fwrite($socket, $cmd . "\r\n");
    }

    private function read($socket): string {
        $response = '';
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            if ($line[3] === ' ') break; // koniec odpowiedzi wieloliniowej
        }
        return $response;
    }

    private function expect($socket, string $code): void {
        $response = $this->read($socket);
        if (substr($response, 0, 3) !== $code) {
            throw new RuntimeException("SMTP oczekiwano $code, otrzymano: $response");
        }
    }

    private function expect_raw($socket, string $code): void {
        $this->expect($socket, $code);
    }

    private function encodeName(string $name): string {
        return '=?UTF-8?B?' . base64_encode($name) . '?=';
    }
}
