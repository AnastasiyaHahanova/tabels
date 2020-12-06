<?php

namespace App\Tests\Controller\Api\v1;

use App\Entity\User;
use App\Model\Entity\User\UserModel;
use App\Repository\UserRepository;
use App\Tests\TestParameters;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CellControllerTest extends WebTestCase
{
    public  $client;
    private $application;
    private $userRepository;
    public  $entityManager;
    public  $userModel;

    public function setUp()
    {
//        $this->client         = static::createClient();
//        self::bootKernel();
//        $container = self::$container->get(UserRepository::class);;
//        $this->userRepository = self::$сontainer->get(UserRepository::class);
//        $this->entityManager  = self::$kernel->getContainer()->get('doctrine.orm.default_entity_manager');
//        $this->userRepository = $this->getMockBuilder(\App\Repository\UserRepository::class)->setConstructorArgs([User::class])->getMock();
//        $this->userModel      = $this->getMockBuilder(\App\Model\Entity\User\UserModel::class)->disableOriginalConstructor()->getMock();

    }

    public function testSomething()
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        // returns the real and unchanged service container
        $container = self::$kernel->getContainer();

        // gets the special container that allows fetching private services
        $container = self::$container;

        $user = self::$container->get('doctrine')->getRepository(User::class)->findOneBy(['username'=>TestParameters::USERNAME]);
//        dump($this->userRepository->findOneByUsername(TestParameters::USERNAME));
//        $user = $this->userRepository->findOneByUsername(TestParameters::USERNAME);
//        if (empty($user)) {
//            $user = (new User())
//                ->setUsername(TestParameters::USERNAME)
//                ->setEmail(TestParameters::USER_EMAIL)
//                ->setRawPassword(TestParameters::USER_PASSWORD);
//            $this->userModel->createUser($user);
//            $user = $this->userRepository->findOneByUsername(TestParameters::USERNAME);
//        }
//        dump($user->getToken(),'!!!');
        $crawler = $this->client->request('GET', '/api/v1/users/list', [
            'headers' => [
                'X-AUTH-TOKEN' => $user->getToken()]]);


        $this->assertResponseIsSuccessful();
    }
}
