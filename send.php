<?php
/**
 * Fasády Svrček – kontaktní formulář
 * Soubor: send.php
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ── Konfigurace ────────────────────────────────────────────────
define('RECIPIENT',   'vita.charvat@gmail.com');
define('SENDER_NAME', 'Fasády Svrček – web');
define('SUBJECT',     'Nová poptávka z webu – Fasády Svrček');
// ──────────────────────────────────────────────────────────────

// Pouze POST požadavky
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Metoda není povolena.']);
    exit;
}

// Načtení a sanitace vstupů
$name    = trim(strip_tags($_POST['name']    ?? ''));
$tel     = trim(strip_tags($_POST['tel']     ?? ''));
$message = trim(strip_tags($_POST['message'] ?? ''));

// Základní validace
$errors = [];
if (mb_strlen($name) < 2)    $errors[] = 'Zadejte jméno a příjmení.';
if (mb_strlen($tel) < 9)     $errors[] = 'Zadejte platné telefonní číslo.';
if (mb_strlen($message) < 5) $errors[] = 'Zpráva je příliš krátká.';

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Sestavení e-mailu
$ip   = $_SERVER['REMOTE_ADDR'] ?? 'neznámá';
$date = date('d. m. Y H:i');

$body  = "Nová poptávka z webu Fasády Svrček\n";
$body .= str_repeat('─', 40) . "\n\n";
$body .= "Jméno:    $name\n";
$body .= "Telefon:  $tel\n\n";
$body .= "Zpráva:\n$message\n\n";
$body .= str_repeat('─', 40) . "\n";
$body .= "Odesláno: $date\n";
$body .= "IP:       $ip\n";

// Hlavičky
$headers  = "From: " . SENDER_NAME . " <noreply@fasady-svrcek.cz>\r\n";
$headers .= "Reply-To: $name <noreply@fasady-svrcek.cz>\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Odeslání
$sent = mail(RECIPIENT, '=?UTF-8?B?' . base64_encode(SUBJECT) . '?=', $body, $headers);

if ($sent) {
    echo json_encode(['ok' => true, 'message' => 'Zpráva odeslána.']);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Odeslání selhalo. Zavolejte nám prosím.']);
}
