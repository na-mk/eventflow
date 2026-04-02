<?php
// Générer les clés JWT RSA
$config = [
    "digest_alg" => "sha256",
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
];

$keypair = openssl_pkey_new($config);
if (!$keypair) {
    die("Erreur lors de la génération des clés\n");
}

openssl_pkey_export($keypair, $privateKey);
$publicKey = openssl_pkey_get_details($keypair)['key'];

file_put_contents(__DIR__ . '/config/jwt/private.pem', $privateKey);
file_put_contents(__DIR__ . '/config/jwt/public.pem', $publicKey);

echo "Clés JWT générées avec succès\n";
echo "Privée: config/jwt/private.pem\n";
echo "Publique: config/jwt/public.pem\n";
</content>
<parameter name="filePath">c:\Users\Utilisateur\eventflow\backend\generate_keys.php