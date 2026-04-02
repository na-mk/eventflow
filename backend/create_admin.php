<?php
require __DIR__ . '/vendor/autoload.php';

use App\Kernel;
use App\Entity\User;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;

(new Dotenv())->bootEnv(__DIR__ . '/.env');

$kernel = new Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();
$hasher = new NativePasswordHasher();

$email = 'admin@eventflow.local';
$password = 'Admin1234!';

$existing = $em->getRepository(User::class)->findOneBy(['email' => $email]);
if ($existing) {
    echo "Admin already exists, deleting...\n";
    $em->remove($existing);
    $em->flush();
}

$user = new User();
$user->setEmail($email);
$user->setFirstName('Admin');
$user->setLastName('User');
$user->setRoles(['ROLE_ADMIN']);
$user->setConsentDate(new \DateTimeImmutable());
$user->setConsentVersion('1.0');
$user->setPassword($hasher->hash($password));

$em->persist($user);
$em->flush();

echo "Admin created: $email / $password\n";
