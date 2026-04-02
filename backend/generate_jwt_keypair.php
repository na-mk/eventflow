<?php
$config=[
    'private_key_bits'=>2048,
    'private_key_type'=>OPENSSL_KEYTYPE_RSA,
];
$res=openssl_pkey_new($config);
if (!$res) {
    echo "openssl_pkey_new failed\n";
    var_dump(openssl_error_string());
    exit(1);
}
$priv='';
$exported=openssl_pkey_export($res, $priv);
if (!$exported) {
    echo "openssl_pkey_export failed\n";
    var_dump(openssl_error_string());
    exit(1);
}
$details=openssl_pkey_get_details($res);
if (!$details || !isset($details['key'])) {
    echo "openssl_pkey_get_details failed\n";
    var_dump(openssl_error_string());
    exit(1);
}
$pub=$details['key'];
if (!is_dir('config/jwt')) {
    mkdir('config/jwt',0755,true);
}
file_put_contents('config/jwt/private.pem', $priv);
file_put_contents('config/jwt/public.pem', $pub);
chmod('config/jwt/private.pem',0600);
chmod('config/jwt/public.pem',0644);

echo "Generated keypair with length=" . strlen($priv) . "\n";
