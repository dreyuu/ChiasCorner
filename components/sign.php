<?php
// sign.php
$privateKey = file_get_contents(__DIR__ . '/../cert/private-key.pem');
$dataToSign = file_get_contents('php://input');
$signature = '';
openssl_sign($dataToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256);
// Output base64 encoded signature only
echo base64_encode($signature);
