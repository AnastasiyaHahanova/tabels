<?php

namespace App\Command;

use App\Repository\SpreadsheetRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

class GenerateCurlCommand extends Command
{
    protected static $defaultName = 'generate:curl';
    private          $projectDir;
    private          $userRepository;
    private          $spreadsheetRepository;
    private          $fileSystem;
    private          $twig;
    private const SUBDIR = 'curl';

    public function __construct(string $projectDir, Filesystem $fileSystem, UserRepository $userRepository, SpreadsheetRepository $spreadsheetRepository, Environment $twig)
    {
        $this->spreadsheetRepository = $spreadsheetRepository;
        $this->fileSystem            = $fileSystem;
        $this->userRepository        = $userRepository;
        $this->projectDir            = $projectDir;
        $this->twig                  = $twig;
        parent::__construct('environment');
    }

    protected function configure()
    {
        $this
            ->setDescription('Change user password')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('host', InputArgument::REQUIRED, 'Host')
            ->addArgument('spreadsheet', InputArgument::OPTIONAL, 'Spreadsheet Name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io              = new SymfonyStyle($input, $output);
        $spreadsheetId   = 1;
        $host            = $input->getArgument('host');
        $username        = $input->getArgument('username');
        $spreadsheetName = $input->getArgument('spreadsheet');
        $user            = $this->userRepository->findOneByUsername($username);
        if (empty($user)) {
            $io->error(sprintf('No user found with username %s ', $username));

            return Command::FAILURE;
        }

        if ($spreadsheetName) {
            $spreadsheet = $this->spreadsheetRepository->findOneByName($spreadsheetName);

            if (empty($spreadsheet)) {
                $io->error(sprintf('No spreadsheet found with name %s ', $spreadsheetName));

                return Command::FAILURE;
            }

            if ($user->getId() !== $spreadsheet->getUser()->getId()) {
                $io->error(sprintf('User with username %s does not have access to the spreadsheet %s ', $username, $spreadsheetName));

                return Command::FAILURE;
            }

            $spreadsheetId = $spreadsheet->getId();
        }

        $content = $this->twig->render('Curl/curl.html.twig', [
            'host'        => $host,
            'spreadsheet' => $spreadsheetId,
            'user_id'     => $user->getId(),
            'token'       => $user->getToken(),
            'username'    => $user->getUsername()
        ]);

        $fileName = strtolower(sprintf('curl_for_%s_%s.md', $username, $spreadsheetName));
        $filePath = sprintf('%s/%s/%s', $this->projectDir, self::SUBDIR, $fileName);
        $dir      = dirname($filePath);
        if (!is_dir($dir)) {
            $this->fileSystem->mkdir($dir);
        }

        file_put_contents($filePath, $content);
        $io->success(sprintf('Requests generated successfully! Check the directory %s/%s', $this->projectDir, self::SUBDIR));

        return Command::SUCCESS;
    }
}
