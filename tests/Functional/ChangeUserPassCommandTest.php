<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Model\Entity\User\UserModel;
use App\Repository\UserRepository;
use App\Tests\SetUpTrait;
use App\Tests\TestParameters;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ChangeUserPassCommandTest extends KernelTestCase
{
//    private $userModel;
//    private $application;
//    private $userRepository;
//    private $encoder;
//
//    public function setUp(): void
//    {
//        self::bootKernel();
//        $this->application    = new Application(self::$kernel);
//        $this->userRepository = self::$container->get(UserRepository::class);
//        $this->encoder        = self::$container->get(UserPasswordEncoderInterface::class);
//        $this->userModel      = self::$container->get(UserModel::class);
//        $this->application->setAutoExit(false);
//    }

use SetUpTrait;

    public function testExecute(): void
    {
        self::bootKernel();
        $this->application    = new Application(self::$kernel);
        $this->userRepository = self::$container->get(UserRepository::class);
        $this->encoder        = self::$container->get(UserPasswordEncoderInterface::class);
        $this->userModel      = self::$container->get(UserModel::class);
        $this->application->setAutoExit(false);
        $command       = $this->application->find('download:roles');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $user     = $this->userRepository->findOneByUsername(TestParameters::USERNAME);
        $password = User::generatePassword();
        if (empty($user)) {
            $user = (new User())
                ->setUsername(TestParameters::USERNAME)
                ->setEmail(TestParameters::USER_EMAIL)
                ->setRawPassword($password);
            $this->userModel->createUser($user);
        }

        $command       = $this->application->find('change:user:pass');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs([TestParameters::USER_PASSWORD]);
        $commandTester->execute(['username' => TestParameters::USERNAME]);

        $this->assertEquals(0, $commandTester->getStatusCode());
        $this->assertTrue($this->encoder->isPasswordValid($user, TestParameters::USER_PASSWORD));
    }
}