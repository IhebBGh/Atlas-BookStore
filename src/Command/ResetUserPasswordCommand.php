<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:reset-user-password',
    description: 'Reset a user password by email (hashes the password)'
)]
class ResetUserPasswordCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'User email address');
        $this->addArgument('password', InputArgument::OPTIONAL, 'New plaintext password (if omitted, you will be prompted)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        $plainPassword = $input->getArgument('password');

        if (!$plainPassword) {
            $plainPassword = $io->askHidden('New password (will be hashed)', function ($value) {
                if (null === $value || trim($value) === '') {
                    throw new \RuntimeException('Password cannot be empty.');
                }

                if (mb_strlen($value) < 8) {
                    throw new \RuntimeException('Password must be at least 8 characters.');
                }

                return $value;
            });
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $io->error("User with email '{$email}' was not found.");
            return Command::FAILURE;
        }

        $hashed = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashed);
        $this->entityManager->flush();

        $io->success("Password updated for user '{$email}'. They can now log in with the new password.");
        return Command::SUCCESS;
    }
}
