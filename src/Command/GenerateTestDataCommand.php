<?php

namespace App\Command;

use App\Entity\Spreadsheet;
use App\Entity\Cell;
use App\Entity\User;
use App\Model\Entity\User\UserModel;
use App\Repository\SpreadsheetRepository;
use App\Repository\UserRepository;
use App\Tests\TestParameters;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GenerateTestDataCommand extends Command
{
    protected static $defaultName = 'generate:test:data';
    private          $userRepository;
    private          $userModel;
    private          $validator;
    private          $entityManager;
    private          $spreadsheetRepository;

    public function __construct(
        UserModel $userModel,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        SpreadsheetRepository $spreadsheetRepository)
    {
        $this->userRepository        = $userRepository;
        $this->entityManager         = $entityManager;
        $this->userModel             = $userModel;
        $this->validator             = $validator;
        $this->spreadsheetRepository = $spreadsheetRepository;

        parent::__construct();
    }

    protected function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = $this->userRepository->finOneByUsername(TestParameters::USERNAME);
        if (empty($user)) {
            $user = (new User())
                ->setUsername(TestParameters::USERNAME)
                ->setEmail(TestParameters::USER_EMAIL)
                ->setRawPassword(TestParameters::USER_PASSWORD);
            $this->userModel->createUser($user);
        }

        $spreadsheet = $this->spreadsheetRepository->findOneByName(TestParameters::TABLE_NAME);
        if (empty($spreadsheet)) {
            $spreadsheet = (new Spreadsheet())
                ->setName(TestParameters::TABLE_NAME)
                ->setUser($user);
            $this->entityManager->persist($spreadsheet);
            $this->entityManager->flush();
        }

        $tens = 10;
        for ($i = 1; $i <= 100; $i++) {
            if (($tens % 10) === 0) {
                $tens += 10;
            }
            for ($j = 1; $j <= 100; $j++) {
                $tableValue = (new Cell())
                    ->setSpreadsheet($spreadsheet)
                    ->setRow($i)
                    ->setColumn($j)
                    ->setValue($tens);
                $this->entityManager->persist($tableValue);
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
