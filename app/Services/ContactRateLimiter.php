<?php
declare(strict_types=1);

namespace App\Services;

/** Límite compartido entre procesos, sin guardar IPs ni contenido del formulario. */
final class ContactRateLimiter
{
    public function __construct(private ?string $directory = null, private int $limit = 5, private int $window = 900) {}

    public function allow(string $address): bool
    {
        $directory = $this->directory ?? sys_get_temp_dir() . '/clicomputer-contact-' . substr(hash('sha256', ROOT_PATH), 0, 16);
        if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) return false;
        $file = @fopen($directory . '/attempts.json', 'c+');
        if (!$file) return false;
        try {
            if (!flock($file, LOCK_EX)) return false;
            $now = time();
            $entries = json_decode(stream_get_contents($file), true) ?: [];
            foreach ($entries as $key => $entry) {
                if ($entry['expires'] <= $now) unset($entries[$key]);
            }
            $key = hash('sha256', $address);
            $entry = $entries[$key] ?? ['count' => 0, 'expires' => $now + $this->window];
            if ($entry['count'] >= $this->limit) return false;
            $entry['count']++;
            $entries[$key] = $entry;
            $contents = json_encode($entries, JSON_THROW_ON_ERROR);
            rewind($file);
            return ftruncate($file, 0) && fwrite($file, $contents) === strlen($contents) && fflush($file);
        } finally {
            flock($file, LOCK_UN);
            fclose($file);
        }
    }
}
