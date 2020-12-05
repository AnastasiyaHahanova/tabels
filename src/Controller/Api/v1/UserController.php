<?php

namespace App\Controller\Api\v1;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("api/v1/users")
 */
class UserController extends AbstractV1Controller
{
    /**
     * @Rest\Post("/", name="users.create")
     * @param EntityManagerInterface       $entityManager
     * @param Request                      $request
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse
     */
    public function createUser(RoleRepository $roleRepository,
                               ValidatorInterface $validator,
                               EntityManagerInterface $entityManager,
                               Request $request,
                               UserPasswordEncoderInterface $encoder): JsonResponse
    {
        $content = $request->getContent();
        $data    = json_decode($content, true);
        if (!is_array($data)) {
            return $this->error('Invalid json', 'Validation error');
        }

        $name             = $data['username'] ?? '';
        $password         = $data['password'] ?? '';
        $email            = $data['email'] ?? '';
        $role             = $roleRepository->findOneByName(Role::USER);
        $user             = (new User)
            ->setUsername($name)
            ->setEmail($email)
            ->setRawPassword($password)
            ->setRoles([$role]);
        $validationErrors = $validator->validate($user);
        if ($validationErrors->count() > 0) {
            return $this->error((string)$validationErrors, 'Invalid user parameters');
        }

        $entityManager->persist($user);
        $entityManager->flush();

        $user->setPassword($encoder->encodePassword($user, $password));
        $token = hash('ripemd320', sprintf('%s-%s-%s', $user->getUsername(), $password, microtime()));
        $user->setToken($token);
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->jsonData(['id' => $user->getId()]);
    }

    /**
     * @Rest\Put("/{id}", name="users.update")
     * @Entity("user", options={"mapping": {"id": "id"}})
     * @param EntityManagerInterface       $entityManager
     * @param Request                      $request
     * @param UserPasswordEncoderInterface $encoder
     * @param User                         $user
     * @return JsonResponse
     */
    public function updateUser(User $user,
                               Request $request,
                               EntityManagerInterface $entityManager,
                               ValidatorInterface $validator,
                               UserPasswordEncoderInterface $encoder): JsonResponse
    {
        if ($user->isDeleted()) {
            return $this->error(sprintf('No user found with ID %s', $user->getId()), 'Invalid user parameters');
        }

        $json    = $request->getContent();
        $content = json_decode($json, true);
        if (!is_array($content)) {
            return $this->error('Invalid json', 'Invalid user parameters');
        }

        if (isset($content['username'])) {
            $user->setUsername($content['username']);
        }

        if (isset($content['password'])) {
            $user->setRawPassword($content['password']);
            $user->setPassword($encoder->encodePassword($user, $content['password']));
        }

        if (isset($content['email'])) {
            $user->setEmail($content['email']);
        }

        $validationErrors = $validator->validate($user);
        if ($validationErrors->count() > 0) {
            $errors = (string)($validationErrors);

            return $this->error($errors, 'Invalid user parameters');
        }

        $entityManager->flush();

        return $this->jsonData(['id' => $user->getId()]);
    }

    /**
     * @Rest\Delete("/{id}", name="users.delete")
     * @Entity("user", options={"mapping": {"id": "id"}})
     * @return JsonResponse
     */
    public function deleteUser(EntityManagerInterface $entityManager, User $user): JsonResponse
    {
        $user->setDeleted(true);
        $entityManager->flush();

        return $this->jsonData(['id' => $user->getId()]);
    }

    /**
     * @Rest\Get("/list", name="users.list")
     * @Rest\QueryParam(name="page", nullable=true, requirements="^\d+$", strict=true)
     * @Rest\QueryParam(name="items_on_page", nullable=true, requirements="^\d+$", strict=true)
     * @Security("is_granted('ROLE_ADMIN')")
     * @param PaginatorInterface $paginator
     * @param UserRepository     $userRepository
     * @param Request            $request
     * @return JsonResponse
     */
    public function getUserList(Request $request, PaginatorInterface $paginator, UserRepository $userRepository): JsonResponse
    {
        $page          = $request->request->get('page') ?? 1;
        $itemsOnPage   = $request->request->get('items_on_page') ?? 20;
        $allUsersQuery = $userRepository->createQueryBuilder('u');
        $users         = $paginator->paginate($allUsersQuery, $request->query->getInt('page', $page), $itemsOnPage);
        $result        = [
            'total_count'   => $users->getTotalItemCount(),
            'page'          => $page,
            'items_on_page' => $itemsOnPage,
            'users'         => $this->formatUsersData($users->getItems())
        ];

        return $this->jsonData($result);
    }

    public function formatUsersData(array $users): array
    {
        $result = [];
        foreach ($users as $user) {
            $userData = [
                'username' => $user->getUsername(),
                'email'    => $user->getEmail(),
                'roles'    => $user->getRoles()
            ];
            $result[] = $userData;
        }

        return $result;
    }
}