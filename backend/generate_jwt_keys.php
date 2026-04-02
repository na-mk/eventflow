<?php
$config = array(
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
);

$res = openssl_pkey_new($config);
openssl_pkey_export($res, $privKey);
$pubKey = openssl_pkey_get_details($res);
$pubKey = $pubKey['key'];

// Crée le dossier s'il n'existe pas
@mkdir('config/jwt', 0755, true);

file_put_contents('config/jwt/private.pem', $privKey);
file_put_contents('config/jwt/public.pem', $pubKey);

echo "Clés JWT générées avec succès !\n";
echo "Private: config/jwt/private.pem\n";
echo "Public: config/jwt/public.pem\n";
