<?php

namespace App\Command;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateUserCommand extends Command
{
    protected static $defaultName = 'user:create';

    private $userRepository;
    private $entityManager;
    private $roleRepository;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager,RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
        $this->userRepository = $userRepository;
        $this->entityManager  = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create user command')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('email', InputArgument::OPTIONAL, 'Email');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io       = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        $email    = $input->getArgument('email') ?? '';

        if (empty($username)) {
            $io->error('Empty username');

            return Command::FAILURE;
        }

        $user = $this->userRepository->finOneByUsername($username);

        if ($user) {
            $io->error(sprintf('User with username %s already exists', $username));

            return Command::FAILURE;
        }

        $role = $this->roleRepository->findOneByName(Role::ADMIN);

        $user = (new User)
            ->setUsername($username)
            ->setEmail($email)
            ->setRoles([$role]);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $io->success('User created successfully!');

        return Command::SUCCESS;
    }
}
