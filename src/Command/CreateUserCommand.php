<?php

namespace App\Command;

use App\Entity\User;
use App\Model\Entity\User\UserModel;
use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateUserCommand extends Command
{
    protected static $defaultName = 'user:create';

    private $userRepository;
    private $userModel;
    private $validator;

    public function __construct(
        UserModel $userModel,
        UserRepository $userRepository,
        ValidatorInterface $validator)
    {
        $this->userRepository = $userRepository;
        $this->userModel      = $userModel;
        $this->validator      = $validator;
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
        $username = $input->getArgument('username') ?? '';
        $email    = $input->getArgument('email') ?? '';
        $password = User::generatePassword();
        $user     = $this->userRepository->finOneByUsername($username);

        if ($user) {
            $io->error(sprintf('User with username %s already exists', $username));

            return Command::FAILURE;
        }

        $user = (new User)
            ->setUsername($username)
            ->setEmail($email)
            ->setRawPassword($password);

        $validationErrors = $this->validator->validate($user);
        if ($validationErrors->count() > 0) {
            $io->error((string)$validationErrors);

            return Command::FAILURE;
        }

        $createdUser = $this->userModel->createUser($user);
        $io->success(sprintf('User created successfully! ID : %s', $createdUser->getId()));

        return Command::SUCCESS;
    }
}
