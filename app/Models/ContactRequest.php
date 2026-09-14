<?php
declare(strict_types=1);

namespace App\Models;

final class ContactRequest
{
    public const SERVICES = ['Desarrollo de software', 'Desarrollo web', 'Soporte técnico',
        'Redes e infraestructura', 'Cámaras de seguridad', 'Consultoría TI'];
    public const LIMITS = ['name' => 80, 'email' => 120, 'phone' => 25, 'service' => 80, 'message' => 1000];

    public static function validate(array $input): array
    {
        $values = $errors = [];
        foreach (self::LIMITS as $key => $limit) {
            $value = isset($input[$key]) && is_string($input[$key]) ? trim($input[$key]) : '';
            if (preg_match('//u', $value) !== 1) {
                $value = '';
                $errors[$key] = 'Usa texto válido para este campo.';
            }
            $values[$key] = $value;
            // UTF-16 code units match the browser maxlength without requiring mbstring.
            $length = preg_match_all('/[\s\S]/u', $value) + preg_match_all('/[\x{10000}-\x{10FFFF}]/u', $value);
            if ($length > $limit) {
                $errors[$key] = 'El texto excede el límite de ' . $limit . ' caracteres.';
            }
        }
        foreach (['name', 'service', 'message'] as $key) {
            if ($values[$key] === '') {
                $errors[$key] = 'Completa este campo.';
            }
        }
        if ($values['email'] !== '' && !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Escribe un correo electrónico válido.';
        }
        if ($values['service'] !== '' && !in_array($values['service'], self::SERVICES, true)) {
            $errors['service'] = 'Selecciona uno de nuestros servicios.';
        }
        return [$values, $errors];
    }

    public static function message(array $values): string
    {
        $lines = ['Hola, Clicomputer. Me gustaría solicitar una cotización.',
            'Nombre: ' . $values['name'], 'Servicio: ' . $values['service']];
        if ($values['email'] !== '') {
            $lines[] = 'Email: ' . $values['email'];
        }
        if ($values['phone'] !== '') {
            $lines[] = 'Teléfono: ' . $values['phone'];
        }
        return implode("\n", array_merge($lines, ['', $values['message']]));
    }
}
