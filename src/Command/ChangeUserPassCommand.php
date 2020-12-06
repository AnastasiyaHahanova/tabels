<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ChangeUserPassCommand extends Command
{
    protected static $defaultName = 'change:user:pass';
    private          $userRepository;
    private          $entityManager;
    private          $encoder;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder)
    {
        $this->userRepository = $userRepository;
        $this->entityManager  = $entityManager;
        $this->encoder        = $encoder;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Change user password')
            ->addArgument('username', InputArgument::REQUIRED, 'Username');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io       = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        $user     = $this->userRepository->findOneByUsername($username);

        if (empty($user)) {
            $io->error(sprintf('No user found with username %s ', $username));

            return Command::FAILURE;
        }

        $question = new Question('Enter new password: ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        $helper   = $this->getHelper('question');
        $password = $helper->ask($input, $output, $question);

        if (empty($password)) {
            $io->error('Empty password');

            return Command::FAILURE;
        }

        if (mb_strlen($password) < User::PASSWORD_LENGTH) {
            $io->error('Password is too short');

            return Command::FAILURE;
        }

        $user->setPassword($this->encoder->encodePassword($user, $password));
        $token = User::generateToken($user->getUsername(), $password);
        $user->setToken($token);
        $this->entityManager->flush();

        $io->success('Password changed successfully!');

        return Command::SUCCESS;
    }
}
