<?php

namespace App\Tests\Command;

use App\Command\AnonymizeOldUsersCommand;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AnonymizeOldUsersCommandTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $hasher;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        if ($metadata !== []) {
            $tool = new SchemaTool($this->em);
            $tool->dropSchema($metadata);
            $tool->createSchema($metadata);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->clear();
        unset($this->em, $this->hasher);
    }

    public function testCommandAnonymizesInactiveUsers(): void
    {
        $user = new User();
        $user
            ->setEmail('inactive@eventflow.test')
            ->setFirstName('Ancien')
            ->setLastName('Compte')
            ->setPhone('0600000000')
            ->setRoles(['participant'])
            ->setPassword($this->hasher->hashPassword($user, 'Password123!'))
            ->setConsentDate(new \DateTimeImmutable('-30 months'))
            ->setConsentVersion('1.0')
            ->setCreatedAt(new \DateTimeImmutable('-30 months'));

        $this->em->persist($user);
        $this->em->flush();

        $application = new Application(self::$kernel);
        $command = $application->find(AnonymizeOldUsersCommand::getDefaultName() ?? 'app:anonymize-old-users');
        $tester = new CommandTester($command);

        $exitCode = $tester->execute(['--months' => 24]);

        self::assertSame(0, $exitCode);

        $this->em->clear();
        $updatedUser = $this->em->getRepository(User::class)->findOneBy(['id' => $user->getId()]);

        self::assertNotNull($updatedUser);
        self::assertTrue($updatedUser->isAnonymized());
        self::assertNull($updatedUser->getPhone());
        self::assertSame(64, strlen((string) $updatedUser->getEmail()));
    }
}
