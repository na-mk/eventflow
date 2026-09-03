<?php
require __DIR__ . '/vendor/autoload.php';

use App\Kernel;
use App\Entity\User;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;

(new Dotenv())->bootEnv(__DIR__ . '/.env');

$email = trim((string) ($_SERVER['ADMIN_EMAIL'] ?? $_ENV['ADMIN_EMAIL'] ?? ''));
$password = (string) ($_SERVER['ADMIN_PASSWORD'] ?? $_ENV['ADMIN_PASSWORD'] ?? '');

if ($email === '') {
    fwrite(STDERR, "ADMIN_EMAIL environment variable is required.\n");
    exit(1);
}

if ($password === '') {
    fwrite(STDERR, "ADMIN_PASSWORD environment variable is required.\n");
    exit(1);
}

$environment = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'prod';
$debug = filter_var($_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL);

$kernel = new Kernel($environment, $debug);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();
$hasher = new NativePasswordHasher();

$user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
if (!$user) {
    $user = new User();
    $user->setEmail($email);
    $user->setFirstName('Admin');
    $user->setLastName('User');
    $user->setConsentDate(new \DateTimeImmutable());
    $user->setConsentVersion('1.0');
    $em->persist($user);
}

$user->setRoles(['ROLE_ADMIN']);
$user->setPassword($hasher->hash($password));
$em->flush();

echo "Local admin is ready: $email\n";
