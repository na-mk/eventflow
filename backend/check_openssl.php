<?php
$pk=file_get_contents('config/jwt/private.pem');
$res=openssl_pkey_get_private($pk);
var_dump($res!==false);
if ($res!==false) {
    $details=openssl_pkey_get_details($res);
    var_dump(isset($details['key']));
}
