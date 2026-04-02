<?php
require __DIR__ . '/vendor/autoload.php';

use phpseclib3\Crypt\RSA;

$key = RSA::createKey(2048);
$private = $key->toString('PKCS1');
$public = $key->getPublicKey()->toString('PKCS8');

if (!is_dir('config/jwt')) {
    mkdir('config/jwt', 0755, true);
}

file_put_contents('config/jwt/private.pem', $private);
file_put_contents('config/jwt/public.pem', $public);
chmod('config/jwt/private.pem', 0600);
chmod('config/jwt/public.pem', 0644);

echo "Generated phpseclib RSA key pair, private length=" . strlen($private) . "\n";
