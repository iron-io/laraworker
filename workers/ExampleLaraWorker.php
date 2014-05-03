<?php
require_once __DIR__ . '/libs/worker_boot.php';

$payload = decryptPayload(getPayload());
fire($payload);

function fire($payload)
{
    echo $payload;
}

