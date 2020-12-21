<?php

namespace App\Command;

use App\Entity\Role;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadRolesCommand extends Command
{
    protected static $defaultName = 'download:roles';
    private          $entityManager;
    private          $roleRepository;

    public function __construct(EntityManagerInterface $entityManager, RoleRepository $roleRepository)
    {
        $this->entityManager  = $entityManager;
        $this->roleRepository = $roleRepository;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rows       = $this->roleRepository->getRoleNames();
        $existRoles = array_map(function ($r) {
            return $r['name'];
        }, $rows);

        if (empty($existRoles)) {
            $this->downLoadAll();
        } else {
            foreach (Role::EXIST_ROLES as $role) {
                if (in_array($role, $existRoles)) {
                    continue;
                }

                $this->createRole($role);
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }

    public function downLoadAll(): void
    {
        foreach (Role::EXIST_ROLES as $roleName) {
            $this->createRole($roleName);
        }
    }

    public function createRole(string $name): void
    {
        $role = (new Role())->setName($name);
        $this->entityManager->persist($role);
    }
}
