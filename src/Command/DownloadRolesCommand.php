<?php

namespace App\Command;

use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DownloadRolesCommand extends Command
{
    protected static $defaultName = 'download:roles';
    private          $entityManager;

    protected function configure()
    {
    }

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        foreach (Role::EXIST_ROLES as $roleName) {
            $role = (new Role())
                ->setName($roleName);
            $this->entityManager->persist($role);
        }

        $this->entityManager->flush();

        $io->success('Roles successfully uploaded!');

        return Command::SUCCESS;
    }
}
