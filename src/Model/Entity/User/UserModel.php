<?php

namespace App\Model\Entity\User;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserModel
{
    private $userRepository;
    private $entityManager;
    private $roleRepository;
    private $validator;
    private $encoder;

    public function __construct(
        UserPasswordEncoderInterface $encoder,
        ValidatorInterface $validator,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
        $this->userRepository = $userRepository;
        $this->entityManager  = $entityManager;
        $this->validator      = $validator;
        $this->encoder        = $encoder;
    }

    public function createUser(User $user, string $roleName = Role::USER): User
    {
        $role = $this->roleRepository->findOneByName($roleName);
        $user->setRoles([$role]);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $user->setPassword($this->encoder->encodePassword($user, $user->getRawPassword()));
        $token = User::generateToken($user->getUsername(), $user->getRawPassword());
        $user->setToken($token);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}