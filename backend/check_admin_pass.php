<?php
require __DIR__.'/vendor/autoload.php';

$kernel = new App\Kernel('dev', true);
$kernel->boot();
$em = $kernel->getContainer()->get('doctrine')->getManager();
$repo = $em->getRepository(App\Entity\User::class);
$user = $repo->findOneBy(['email' => 'admin@eventflow.local']);
if (!$user) {
    echo "not_found\n";
    exit(0);
}
$hasher = $kernel->getContainer()->get('security.password_hasher');
$valid = $hasher->isPasswordValid($user, 'Admin1234!');

echo 'id='.$user->getId()."\n";
echo 'email='.$user->getEmail()."\n";
echo 'roles='.json_encode($user->getRoles())."\n";
echo 'hash='.$user->getPassword()."\n";
echo 'valid=' . ($valid ? 'ok' : 'bad') . "\n";
