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
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("api/v1/users")
 */
class UserController extends AbstractController
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

        $user = (new User)
            ->setUsername((string)$data['name'])
            ->setEmail($data['email']);
        $entityManager->persist($user);
        $entityManager->flush();
        $user->setPassword($encoder->encodePassword($user, $data['password']));
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['id' => $user->getId()], Response::HTTP_OK);
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