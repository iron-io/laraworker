<?php
require __DIR__ . '/../../bootstrap/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/start.php';
use Illuminate\Encryption\Encrypter;

$app->setRequestForConsoleEnvironment();
$app->boot();

function decryptPayload($payload)
{
    $crypt = new Encrypter(Config::get('app.key'));
    $payload = $crypt->decrypt($payload);
    return json_decode(json_encode($payload), FALSE);
}
