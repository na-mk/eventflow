<?php
$data = [
    'firstName' => 'paul',
    'lastName' => 'fa',
    'email' => 'paul@gmail.com',
    'password' => '123456',
    'phone' => '7979701',
    'roles' => ['ROLE_USER'],
    'consentGiven' => true,
];
$ch = curl_init('http://localhost:8000/api/auth/register');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
$response = curl_exec($ch);
$info = curl_getinfo($ch);
$err = curl_error($ch);
curl_close($ch);

file_put_contents('tmp_register_output.txt', "status=".$info['http_code']."\nerror=".$err."\nresponse=".$response."\n");
