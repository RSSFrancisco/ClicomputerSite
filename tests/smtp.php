<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Services\ContactMailer;
use App\Services\SmtpConfiguration;
use PHPMailer\PHPMailer\SMTP;

function verify(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}

// Conserva PHPMailer real y sustituye únicamente el servidor SMTP, sin sockets ni correos.
final class TestSmtp extends SMTP {
    public array $commands = [];
    public string $mime = '';
    public array $options = [];
    public bool $open = false;
    public function __construct(public string $failure = '') {}
    public function connect($host, $port = null, $timeout = 30, $options = []) {
        $this->commands[] = ['connect', $host, $port];
        $this->options = $options;
        return $this->open = $this->failure !== 'connection';
    }
    public function connected() { return $this->open; }
    public function hello($host = '') { $this->commands[] = ['hello']; return true; }
    public function getServerExt($name) { return $name === 'STARTTLS'; }
    public function startTLS() { $this->commands[] = ['tls']; return $this->failure !== 'tls'; }
    public function authenticate($username, $password, $authtype = null, $OAuth = null) {
        $this->commands[] = ['auth', $username, $password];
        if ($this->failure === 'auth') { $this->setError('PRIVATE server reply', 'PRIVATE visitor data', '535', '5.7.8'); return false; }
        return true;
    }
    public function mail($from) { $this->commands[] = ['mail', $from]; return true; }
    public function recipient($address, $dsn = '') { $this->commands[] = ['recipient', $address]; return true; }
    public function data($data) {
        $this->commands[] = ['data']; $this->mime = $data;
        if ($this->failure === 'data') { $this->setError('PRIVATE refusal', '', '550', '5.7.1'); return false; }
        return true;
    }
    public function quit($close_on_error = true) { $this->commands[] = ['quit']; return true; }
    public function close() { $this->open = false; }
}

$log = sys_get_temp_dir() . '/clicomputer-smtp-test-' . bin2hex(random_bytes(8)) . '.log';
ini_set('error_log', $log);
$config = ['username' => 'info@clcomputer.com', 'password' => 'PRIVATE.smtp.password', 'port' => 587];
$values = ['name' => 'Ana Pérez', 'email' => 'PRIVATE.visitor@example.com', 'phone' => '', 'service' => 'Desarrollo web', 'message' => "PRIVATE Catálogo & redes\n¿Pueden cotizar?"];
$settings = ['email' => 'info@clcomputer.com', 'mail_from' => ''];
foreach (['', 'connection', 'tls', 'auth', 'data'] as $failure) {
    $smtp = new TestSmtp($failure);
    $mailer = new ContactMailer(static function ($mail) use ($smtp): bool {
        verify($mail->Mailer === 'smtp' && $mail->SMTPAuth, 'Unauthenticated or PHP mail transport used');
        verify($mail->SMTPDebug === 0, 'SMTP debug may expose credentials');
        $mail->setSMTPInstance($smtp);
        return $mail->send();
    }, $config);
    verify($mailer->send($values, $settings) === ($failure === ''), 'Wrong result for ' . $failure);
    $commands = array_column($smtp->commands, 0);
    verify($smtp->commands[0] === ['connect', 'smtp.titan.email', 587], 'Wrong SMTP endpoint');
    verify($smtp->options['ssl'] === ['verify_peer' => true, 'verify_peer_name' => true, 'allow_self_signed' => false], 'TLS verification disabled');
    if (in_array($failure, ['connection', 'tls'], true)) verify(!in_array('auth', $commands, true), 'Credentials sent without TLS');
    if ($failure === 'auth') verify(!in_array('mail', $commands, true), 'Mail sent without authentication');
    if ($failure === '') {
        verify(array_search('tls', $commands, true) < array_search('auth', $commands, true), 'Authentication preceded TLS');
        verify(in_array(['mail', 'info@clcomputer.com'], $smtp->commands, true), 'Envelope sender mismatch');
        verify(in_array(['recipient', 'info@clcomputer.com'], $smtp->commands, true), 'Visitor can control recipient');
        verify(str_contains($smtp->mime, 'Reply-To: PRIVATE.visitor@example.com'), 'Reply-To lost');
        [$headers, $body] = explode("\r\n\r\n", $smtp->mime, 2);
        verify(str_contains(base64_decode($body), 'Ana Pérez') && str_contains(base64_decode($body), '¿Pueden cotizar?'), 'UTF-8 message damaged');
    }
}

$smtps = (new ContactMailer(null, $config + []))->createClient('info@clcomputer.com');
verify($smtps->SMTPSecure === 'tls' && $smtps->Port === 587, 'STARTTLS missing');
$smtps = (new ContactMailer(null, array_merge($config, ['port' => 465])))->createClient('info@clcomputer.com');
verify($smtps->SMTPSecure === 'ssl' && $smtps->Port === 465, 'Implicit TLS missing');
$called = false;
foreach ([['password' => ''], ['port' => 25], ['username' => "info@clcomputer.com\r\nBcc:bad@example.com"]] as $bad) {
    $mailer = new ContactMailer(static function () use (&$called): bool { $called = true; return true; }, array_merge($config, $bad));
    verify(!$mailer->send($values, $settings) && !$called, 'Invalid configuration reached transport');
}
$logText = file_get_contents($log);
verify(!str_contains($logText, 'PRIVATE'), 'Logs leaked secret or visitor information');
verify(str_contains($logText, 'contact_mail_accepted') && str_contains($logText, '535') && str_contains($logText, '550'), 'SMTP diagnosis missing');

$private = sys_get_temp_dir() . '/clicomputer-private-test-' . bin2hex(random_bytes(8)) . '.json';
file_put_contents($private, json_encode($config));
putenv('CLICOMPUTER_SMTP_CONFIG=' . $private);
verify(SmtpConfiguration::load('fallback@example.com')['password'] === $config['password'], 'Private config not loaded');
putenv('CLICOMPUTER_SMTP_PORT=465');
verify(SmtpConfiguration::load('fallback@example.com')['encryption'] === 'ssl', 'Environment override ignored');
putenv('CLICOMPUTER_SMTP_PORT');
putenv('CLICOMPUTER_SMTP_CONFIG=' . ROOT_PATH . '/config/smtp.example.json');
try { SmtpConfiguration::load('info@clcomputer.com'); throw new LogicException('Public config accepted'); }
catch (RuntimeException $error) { verify($error->getMessage() === 'smtp_config_location', 'Wrong location rejection'); }
putenv('CLICOMPUTER_SMTP_CONFIG');
unlink($private);
unlink($log);
echo "OK: SMTP autenticado, TLS obligatorio, contenido UTF-8, errores sin falsos éxitos, configuración privada y registros sin datos sensibles.\n";
