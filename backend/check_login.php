<?php
$email = 'test'.rand(1000,9999).'@mail.com';
$password = '123456';
$data = [
  'firstName' => 't',
  'lastName' => 't',
  'email' => $email,
  'password' => $password,
  'phone' => '0000',
  'roles' => ['ROLE_USER'],
  'consentGiven' => true,
];

$ch = curl_init('http://localhost:8000/api/auth/register');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
$resp = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo "register: status={$info['http_code']} body={$resp}\n";

$ch = curl_init('http://localhost:8000/api/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email'=>$email,'password'=>$password]));
$resp = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo "login: status={$info['http_code']} body={$resp}\n";
