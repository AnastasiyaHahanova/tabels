<?php

namespace App\Controller\Api\v1;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
    public function createUser(EntityManagerInterface $entityManager, Request $request, UserPasswordEncoderInterface $encoder): JsonResponse
    {
        $content = $request->getContent();
        $data    = json_decode($content, true);
        if (!is_array($data)) {
            return new JsonResponse('Invalid json', Response::HTTP_BAD_REQUEST);
        }

        $validateErrors = Validator::validate($data, ['name', 'password', 'email']);
        if ($validateErrors) {
            return new JsonResponse($validateErrors, Response::HTTP_BAD_REQUEST);
        }

        ['name' => $name, 'password' => $password, 'email' => $email] = $data;

        $user = (new User)
            ->setUsername($name)
            ->setEmail($email)
            ->setRoles([Role::USER]);
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
    public function updateUser(EntityManagerInterface $entityManager, Request $request, User $user, UserPasswordEncoderInterface $encoder): JsonResponse
    {
        $json    = $request->getContent();
        $content = json_decode($json, true);
        if (!is_array($content)) {
            return new JsonResponse('Invalid json', Response::HTTP_BAD_REQUEST);
        }

        $validateErrors = Validator::validate($content);
        if ($validateErrors) {
            return new JsonResponse($validateErrors, Response::HTTP_BAD_REQUEST);
        }

        if (isset($content['username'])) {
            $user->setUsername($content['username']);
        }

        if (isset($content['password'])) {
            $user->setPassword($encoder->encodePassword($user, $content['password']));
        }

        if (isset($content['email'])) {
            $user->setEmail($content['email']);
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
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->jsonData(['id' => $user->getId()]);
    }

    /**
     * @Rest\Get("/list", name="users.list")
     * @Rest\QueryParam(name="page", nullable=true, requirements="^\d+$", strict=true)
     * @Rest\QueryParam(name="items_on_page", nullable=true, requirements="^\d+$", strict=true)
     *
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
            'users'         => $users->getItems()
        ];

        return $this->jsonData($result);
    }
}