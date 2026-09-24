<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\ContactRequest;
use Closure;
use Throwable;

/** Entrega el correo al servicio configurado en PHP por el alojamiento. */
final class ContactMailer
{
    private Closure $transport;

    public function __construct(?Closure $transport = null)
    {
        $this->transport = $transport ?? static fn (string $to, string $subject, string $body, array $headers): bool =>
            function_exists('mail') && @mail($to, $subject, $body, $headers);
    }

    public function send(array $values, array $settings): bool
    {
        $recipient = $settings['email'];
        $sender = $settings['mail_from'] ?: $recipient;
        foreach ([$recipient, $sender, $values['email']] as $address) {
            if (preg_match('/[\r\n]/', $address) || !filter_var($address, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
        }
        // El visitante solo controla Reply-To; nunca el destinatario ni el remitente.
        $headers = [
            'From' => 'Clicomputer <' . $sender . '>',
            'Reply-To' => $values['email'],
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Transfer-Encoding' => 'base64',
        ];
        $body = str_replace(["\r\n", "\r"], "\n", ContactRequest::message($values));
        $body = chunk_split(base64_encode(str_replace("\n", "\r\n", $body)), 76, "\r\n");
        try {
            return ($this->transport)($recipient, 'Nueva solicitud de contacto - Clicomputer', $body, $headers);
        } catch (Throwable) {
            // No registrar el contenido ni los datos personales de la solicitud.
            error_log('Clicomputer: el servicio de correo no pudo aceptar una solicitud.');
            return false;
        }
    }
}
