<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;

class GenerateCurlCommand extends Command
{
    protected static $defaultName = 'generate:curl';
    private          $projectDir;
    private          $userRepository;
    private          $twig;
    private const FILENAME = 'CURL.md';

    public function __construct(string $projectDir, UserRepository $userRepository, Environment $twig)
    {
        $this->userRepository = $userRepository;
        $this->projectDir     = $projectDir;
        $this->twig           = $twig;
        parent::__construct('environment');
    }

    protected function configure()
    {
        $this
            ->setDescription('Change user password')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('host', InputArgument::REQUIRED, 'Host');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io       = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        $host     = $input->getArgument('host');
        $user     = $this->userRepository->finOneByUsername($username);

        if (empty($user)) {
            $io->error(sprintf('No user found with username %s ', $username));

            return Command::FAILURE;
        }

        $content = $this->twig->render('Curl/curl.html.twig', [
            'host'     => $host,
            'token'    => $user->getToken(),
            'username' => $user->getUsername()
        ]);

        file_put_contents(sprintf('%s/%s', $this->projectDir, self::FILENAME), $content);

        $io->success('Password changed successfully!');

        return Command::SUCCESS;
    }
}
