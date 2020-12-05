<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CreateUserCommandTest extends KernelTestCase
{
    private $entityManager;
    private $application;
    private $userRepository;

    public function setUp(): void
    {
        self::bootKernel();
        $this->application    = new Application(self::$kernel);
        $this->userRepository = self::$container->get(UserRepository::class);
        $this->entityManager  = self::$container->get(EntityManagerInterface::class);
        $this->application->setAutoExit(false);
    }

    public function testExecute(): void
    {
        $username = sprintf('TestUser%s', rand(1, 100));
        $this->runCommand('download:roles', []);
        $this->runCommand('user:create', [
            'username' => $username,
            'email'    => sprintf('%s@mail.ru', $username)
        ]);

        $user = $this->userRepository->finOneByUsername($username);
        $this->assertNotEmpty($user);
        $this->assertInstanceOf(User::class, $user);
    }

    private function runCommand(string $commandName, array $inputs): void
    {
        $command       = $this->application->find($commandName);
        $commandTester = new CommandTester($command);
        $commandTester->execute($inputs);
        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}