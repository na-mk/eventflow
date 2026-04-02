<?php

namespace App\Command;

use App\Entity\ConsentLog;
use App\Repository\UserRepository;
use App\Service\ConsentLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:anonymize-old-users',
    description: 'Anonymize inactive users older than 2 years (RGPD compliance)',
)]
class AnonymizeOldUsersCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
        private ConsentLogService $consentLogService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'months',
            'm',
            InputOption::VALUE_OPTIONAL,
            'Number of months of inactivity before anonymization',
            24
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $months = (int) $input->getOption('months');

        $before = new \DateTimeImmutable("-{$months} months");
        $users = $this->userRepository->findInactiveUsersToAnonymize($before);

        if (empty($users)) {
            $io->success("No users to anonymize (inactive since {$months} months).");
            return Command::SUCCESS;
        }

        $io->section("Anonymizing {$months}+ months inactive users...");
        $count = 0;

        foreach ($users as $user) {
            // Log avant anonymisation
            $this->consentLogService->log(
                $user,
                ConsentLog::ACTION_DATA_DELETED,
                null,
                "Auto-anonymized: inactive for {$months}+ months"
            );

            $emailHash = hash('sha256', (string) $user->getEmail());
            $user->setEmail($emailHash);
            $user->setFirstName('Utilisateur supprimé');
            $user->setLastName('Compte supprimé');
            $user->setPhone(null);
            $user->setIsAnonymized(true);
            $user->setConsentDate(null);

            $io->text("  ✓ User #{$user->getId()} anonymized");
            $count++;
        }

        $this->em->flush();
        $io->success("Done. {$count} user(s) anonymized.");

        return Command::SUCCESS;
    }
}
