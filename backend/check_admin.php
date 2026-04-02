<?php
require __DIR__ . '/vendor/autoload.php';

use App\Kernel;
use App\Entity\User;

$kernel = new Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();
$hasher = $container->get('security.user_password_hasher');

$email = 'admin@eventflow.local';
$user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

if (!$user) {
    echo "Admin user not found\n";
    exit(1);
}

echo "Admin user found:\n";
echo "ID: " . $user->getId() . "\n";
echo "Email: " . $user->getEmail() . "\n";
echo "Roles: " . json_encode($user->getRoles()) . "\n";
echo "Password hash: " . $user->getPassword() . "\n";

$password = 'Admin1234!';
$valid = $hasher->isPasswordValid($user, $password);
echo "Password 'Admin1234!' valid: " . ($valid ? 'YES' : 'NO') . "\n";

$valid2 = $hasher->isPasswordValid($user, 'admin123');
echo "Password 'admin123' valid: " . ($valid2 ? 'YES' : 'NO') . "\n";
</content>
<parameter name="filePath">c:\Users\Utilisateur\eventflow\backend\check_admin.php