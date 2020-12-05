<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\TestParameters;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ChangeUserPassCommandTest extends KernelTestCase
{
    private $entityManager;
    private $application;
    private $userRepository;
    private $encoder;

    public function setUp(): void
    {
        self::bootKernel();
        $this->application    = new Application(self::$kernel);
        $this->userRepository = self::$container->get(UserRepository::class);
        $this->encoder        = self::$container->get(UserPasswordEncoderInterface::class);
        $this->entityManager  = self::$container->get(EntityManagerInterface::class);
        $this->application->setAutoExit(false);
    }

    public function testExecute(): void
    {
        $user = $this->userRepository->finOneByUsername(TestParameters::USERNAME);
        $password      = User::generatePassword();

        $command       = $this->application->find('change:user:pass');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs([$password]);
        $commandTester->execute(['username' => TestParameters::USERNAME]);

        $this->assertEquals(0, $commandTester->getStatusCode());
        $this->assertTrue($this->encoder->isPasswordValid($user,$password));
    }
}