<?php

namespace App\Command;

use App\Entity\UserRole;
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
    name: 'app:user:roles',
    description: 'Manage user roles',
)]
class UserRolesCommand extends Command
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
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addOption('add', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Roles to add')
            ->addOption('remove', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Roles to removea');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (null === $user) {
            throw new InvalidArgumentException(sprintf('Cannot find user %s.', $email));
        }

        $roles = $user->getRoles();
        $roles = array_merge($roles, $input->getOption('add'));
        $roles = array_diff($roles, $input->getOption('remove'));

        $invalidRoles = array_filter($roles, static fn (string $role) => null === UserRole::tryFrom($role));
        if (!empty($invalidRoles)) {
            throw new InvalidArgumentException(sprintf('Invalid roles: %s', implode(', ', $invalidRoles)));
        }

        $user->setRoles($roles);
        $this->userRepository->save($user, flush: true);

        $io->success(sprintf('Roles for ser %s: %s.', $user->getUserIdentifier(), implode(', ', $user->getRoles())));

        return Command::SUCCESS;
    }
}
