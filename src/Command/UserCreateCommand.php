<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:user:create',
    description: 'Create user',
)]
class UserCreateCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'User email')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'User password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        while (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = $io->ask('Email');
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (null !== $user) {
            throw new InvalidArgumentException(sprintf('User %s already exists.', $user->getUserIdentifier()));
        }

        $password = $input->getOption('password');
        while (empty(trim($password))) {
            $password = $io->ask('Password');
        }

        $user = new User();
        $user
            ->setEmail($email)
            ->setPassword($this->passwordHasher->hashPassword($user, $password));
        $this->userRepository->save($user, flush: true);

        $io->success(sprintf('User %s created.', $user->getUserIdentifier()));

        return Command::SUCCESS;
    }
}
