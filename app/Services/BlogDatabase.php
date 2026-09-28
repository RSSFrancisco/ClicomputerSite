<?php
declare(strict_types=1);

namespace App\Services;

use PDO;
use RuntimeException;

final class BlogDatabase
{
    private ?PDO $connection = null;

    public static function configPath(): string
    {
        return getenv('CLICOMPUTER_DB_CONFIG') ?: dirname(ROOT_PATH) . '/clicomputer-private/database.json';
    }

    public function configured(): bool
    {
        return is_file(self::configPath()) || getenv('CLICOMPUTER_DB_DATABASE') !== false;
    }

    public function connection(): PDO
    {
        if ($this->connection) return $this->connection;
        $config = [];
        if (is_file(self::configPath())) {
            $path = realpath(self::configPath());
            if (!$path || str_starts_with($path, realpath(ROOT_PATH) . DIRECTORY_SEPARATOR)) {
                throw new RuntimeException('La configuración de MySQL debe estar fuera de la carpeta pública.');
            }
            $contents = @file_get_contents($path);
            if ($contents === false) throw new RuntimeException('No se pudo leer la configuración privada de MySQL.');
            $config = json_decode($contents, true, 16, JSON_THROW_ON_ERROR);
            if (!is_array($config)) throw new RuntimeException('Configuración de MySQL inválida.');
        }
        foreach (['host', 'port', 'database', 'username', 'password', 'socket'] as $field) {
            $value = getenv('CLICOMPUTER_DB_' . strtoupper($field));
            if ($value !== false) $config[$field] = $value;
        }
        $config += ['host' => 'localhost', 'port' => 3306, 'socket' => ''];
        foreach (['host', 'database', 'username', 'password', 'socket'] as $field) {
            if (!isset($config[$field]) || !is_string($config[$field]) || str_contains($config[$field], "\0")) {
                throw new RuntimeException('Falta configurar la conexión de MySQL.');
            }
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/D', $config['database']) || $config['username'] === '' || $config['password'] === ''
            || preg_match('/[;\r\n]/', $config['host'] . $config['socket'])
            || !filter_var($config['port'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]])) {
            throw new RuntimeException('Configuración de MySQL inválida.');
        }
        $destination = $config['socket'] !== '' ? 'unix_socket=' . $config['socket'] : 'host=' . $config['host'] . ';port=' . $config['port'];
        $this->connection = new PDO('mysql:' . $destination . ';dbname=' . $config['database'] . ';charset=utf8mb4', $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_TIMEOUT => 3,
        ]);
        $this->connection->exec("SET time_zone = '+00:00'");
        return $this->connection;
    }
}
