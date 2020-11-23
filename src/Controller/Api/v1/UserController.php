<?php

namespace App\Controller\Api\v1;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/users")
 */
class UserController extends AbstractController
{
    /**
     * @Rest\Post("/create", name="users.create")
     * @return JsonResponse
     */
    public function createUser(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $content  = $request->request->all();
        $username = $content['name'];
        $password = $content['password'];
        $email    = $content['email'];
        $user     = (new User)
            ->setUsername($username)
            ->setPassword($password)
            ->setEmail($email);
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['id' => $user->getId()], Response::HTTP_OK);
    }

    /**
     * @Rest\Put("/{id}", name="users.update")
     * @Entity("user", options={"mapping": {"id": "id"}})
     * @return JsonResponse
     */
    public function updateUser(EntityManagerInterface $entityManager, Request $request, User $user): JsonResponse
    {
        $content = $request->request->all();
        if (isset($content['username'])) {
            $user->setUsername($content['username']);
        }

        if (isset($content['password'])) {
            $user->setPassword($content['password']);
        }

        if (isset($content['email'])) {
            $user->setEmail($content['email']);
        }

        $entityManager->flush();

        return new JsonResponse(['id' => $user->getId()], Response::HTTP_OK);
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

        return new JsonResponse(['id' => $user->getId()], Response::HTTP_OK);
    }
}