<?php

namespace App\Tests\Functional;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class TableTest extends KernelTestCase
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

    public function loadTestData()
    {
        $this->assertTrue(true);

    }

    public function testExecute(): void
    {
        $this->loadTestData();

    }

    private function runCommand(string $commandName, array $inputs): void
    {
        $command       = $this->application->find($commandName);
        $commandTester = new CommandTester($command);
        $commandTester->execute($inputs);
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

}