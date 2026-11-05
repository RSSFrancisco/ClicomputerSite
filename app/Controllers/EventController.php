<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\View;
use App\Models\Site;

final class EventController
{
    private const ROBOTS = 'noindex, nofollow';

    public function __construct(private Site $site, private View $view, private PageController $pages) {}

    public function show(string $path): Response
    {
        $headers = ['Content-Type' => 'text/html; charset=UTF-8', 'X-Robots-Tag' => self::ROBOTS, 'Referrer-Policy' => 'no-referrer'];
        if ($path === '/eventos.html' || str_ends_with($path, '/')) {
            return new Response('', 301, $headers + ['Location' => $path === '/eventos.html' ? '/eventos' : rtrim($path, '/')]);
        }
        $events = require ROOT_PATH . '/data/events.php';
        $event = null;
        $page = ['title' => 'Eventos · Momentos para recordar', 'description' => 'Invitaciones para compartir los momentos más especiales.'];
        $template = 'events';
        if ($path !== '/eventos') {
            if (!preg_match('~^/eventos/([a-z0-9-]+)(?:/(calendario\.ics|invitacion\.png|musica\.mp3))?$~D', $path, $match) || !isset($events[$match[1]])) {
                $response = $this->pages->notFound();
                $response->headers = array_merge($response->headers, $headers);
                return $response;
            }
            $event = $events[$match[1]];
            $resource = $match[2] ?? '';
            if ($resource === 'invitacion.png') {
                return new Response((string) file_get_contents(ROOT_PATH . '/data/events/' . $event['slug'] . '/invitacion.png'), 200, array_merge($headers, [
                    'Content-Type' => 'image/png', 'Cache-Control' => 'public, max-age=86400', 'X-Content-Type-Options' => 'nosniff',
                ]));
            }
            if ($resource === 'calendario.ics') return $this->calendar($event, $headers);
            if ($resource === 'musica.mp3' && !empty($event['music']['file'])) return $this->audio($event, $headers);
            $page = ['title' => $event['title'], 'description' => $event['description']];
            $template = 'wedding';
        }
        $data = compact('events', 'event', 'page') + ['canonical' => rtrim($this->site->settings()['url'], '/') . $path];
        $content = $this->view->render('pages/' . $template, $data);
        return new Response($this->view->render('layouts/event', $data + compact('content')), 200, $headers);
    }

    private function audio(array $event, array $headers): Response
    {
        $file = ROOT_PATH . '/data/events/' . $event['slug'] . '/' . $event['music']['file'];
        $size = filesize($file);
        $start = 0;
        $end = $size - 1;
        $status = 200;
        $headers = array_merge($headers, [
            'Content-Type' => 'audio/mpeg', 'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=86400', 'X-Content-Type-Options' => 'nosniff',
        ]);
        // Safari y la reanudación del audio solicitan partes del MP3.
        $range = isset($_SERVER['HTTP_IF_RANGE']) ? '' : trim($_SERVER['HTTP_RANGE'] ?? '');
        if (preg_match('~^bytes=(\d*)-(\d*)$~D', $range, $match) && ($match[1] !== '' || $match[2] !== '')) {
            if ($match[1] === '') {
                $length = min((int) $match[2], $size);
                $start = $length > 0 ? $size - $length : $size;
            } else {
                $start = (int) $match[1];
                if ($match[2] !== '') $end = min((int) $match[2], $size - 1);
            }
            if ($start > $end || $start >= $size) {
                return new Response('', 416, $headers + ['Content-Range' => 'bytes */' . $size, 'Content-Length' => '0']);
            }
            $status = 206;
            $headers['Content-Range'] = 'bytes ' . $start . '-' . $end . '/' . $size;
        }
        $headers['Content-Length'] = (string) ($end - $start + 1);
        $handle = fopen($file, 'rb');
        if ($handle === false) throw new \RuntimeException('No se pudo abrir el audio de la invitación.');
        try {
            fseek($handle, $start);
            $body = stream_get_contents($handle, $end - $start + 1);
            if ($body === false) throw new \RuntimeException('No se pudo leer el audio de la invitación.');
        } finally {
            fclose($handle);
        }
        return new Response($body, $status, $headers);
    }

    private function calendar(array $event, array $headers): Response
    {
        $escape = static fn (string $text): string => str_replace(["\\", ";", ",", "\r", "\n"], ["\\\\", "\\;", "\\,", '', "\\n"], $text);
        $lines = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Clicomputer//Invitaciones//ES', 'CALSCALE:GREGORIAN', 'METHOD:PUBLISH'];
        foreach (['ceremony' => 'Misa', 'reception' => 'Recepción'] as $key => $label) {
            $place = $event[$key];
            $startUtc = (new \DateTimeImmutable($place['start']))->setTimezone(new \DateTimeZone('UTC'))->format('Ymd\THis\Z');
            array_push($lines, 'BEGIN:VEVENT', 'UID:' . $event['slug'] . '-' . $key . '@clcomputer.com',
                'DTSTAMP:' . gmdate('Ymd\THis\Z'), 'DTSTART:' . $startUtc,
                'SUMMARY:' . $escape($label . ' · ' . implode(' y ', $event['names'])),
                'LOCATION:' . $escape($place['venue'] . ', ' . $place['address']),
                'DESCRIPTION:' . $escape($key === 'reception' ? 'Hora de llegada: 5:30 p. m. puntual. ' . $place['reference'] . '.' : 'Con la bendición de Dios, te invitamos a nuestra boda.'),
                'END:VEVENT');
        }
        $lines[] = 'END:VCALENDAR';
        // RFC 5545: pliega las líneas largas sin cortar caracteres UTF-8.
        $folded = [];
        foreach ($lines as $line) {
            $chunk = '';
            foreach (preg_split('//u', $line, -1, PREG_SPLIT_NO_EMPTY) as $character) {
                if (strlen($chunk . $character) > 73) { $folded[] = $chunk; $chunk = ' '; }
                $chunk .= $character;
            }
            $folded[] = $chunk;
        }
        return new Response(implode("\r\n", $folded) . "\r\n", 200, array_merge($headers, [
            'Content-Type' => 'text/calendar; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $event['slug'] . '.ics"',
            'Cache-Control' => 'no-store',
        ]));
    }
}
