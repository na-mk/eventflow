<?php
require __DIR__ . '/vendor/autoload.php';

use App\Kernel;
use App\Entity\User;

$kernel = new Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();
$admin = $em->getRepository(User::class)->findOneBy(['email' => 'admin@eventflow.local']);
var_dump((bool)$admin);
if ($admin) {
    echo $admin->getId() . ' ' . $admin->getEmail() . ' ' . json_encode($admin->getRoles()) . "\n";
}
