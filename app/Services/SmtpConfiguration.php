<?php
declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class SmtpConfiguration
{
    public static function path(): string
    {
        return getenv('CLICOMPUTER_SMTP_CONFIG') ?: dirname(ROOT_PATH) . '/clicomputer-private/smtp.json';
    }

    public static function load(string $defaultUsername): array
    {
        $path = self::path();
        $config = [];
        if (is_file($path)) {
            $resolved = realpath($path);
            $public = realpath(ROOT_PATH) . DIRECTORY_SEPARATOR;
            if (!$resolved || strncmp($resolved, $public, strlen($public)) === 0 || !is_readable($path)) {
                throw new RuntimeException('smtp_config_location');
            }
            $config = json_decode(file_get_contents($path), true, 16, JSON_THROW_ON_ERROR);
            if (!is_array($config)) throw new RuntimeException('smtp_config_format');
        }
        $config += ['username' => $defaultUsername, 'password' => '', 'port' => 587];
        foreach (['username', 'password', 'port'] as $name) {
            $value = getenv('CLICOMPUTER_SMTP_' . strtoupper($name));
            if ($value !== false) $config[$name] = $value;
        }
        return self::validate($config);
    }

    public static function validate(array $config): array
    {
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';
        $port = $config['port'] ?? 587;
        if (!is_string($username) || preg_match('/[\r\n]/', $username) || !filter_var($username, FILTER_VALIDATE_EMAIL)
            || !is_string($password) || $password === '' || !in_array($port, [465, 587, '465', '587'], true)) {
            throw new RuntimeException('smtp_config_incomplete');
        }
        // El destino de las credenciales siempre es Titan; no se acepta un host del formulario.
        return ['host' => 'smtp.titan.email', 'username' => $username, 'password' => $password,
            'port' => (int) $port, 'encryption' => (int) $port === 465 ? 'ssl' : 'tls'];
    }
}
