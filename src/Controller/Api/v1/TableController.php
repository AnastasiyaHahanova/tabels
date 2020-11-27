<?php

namespace App\Controller\Api\v1;

use App\Entity\Table;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TableController extends AbstractController
    /**
     * @Route("/tables")
     */
{
    /**
     * @Rest\Post("/create", name="tables.create")
     * @param EntityManagerInterface $entityManager
     * @param UserRepository         $userRepository
     * @param Request                $request
     * @return JsonResponse
     */
    public function createTable(EntityManagerInterface $entityManager, UserRepository $userRepository, Request $request): JsonResponse
    {
        $content = $request->getContent();
        $data = json_decode($content,true);
        $userId  = (int)$data['user_id'];
        $columns = $data['columns'] ?? [];
        $user    = $userRepository->findOneById($userId);
        if (empty($user)) {
            return new JsonResponse(sprintf('No user with id %s exist', $userId), Response::HTTP_BAD_REQUEST);
        }

        $validationErrors = Validator::validate($data,['name']);
        if ($validationErrors)
        {
            return new JsonResponse($validationErrors,Response::HTTP_BAD_REQUEST);
        }

        $table = (new Table())
            ->setName($content['name'])
            ->setColumns($columns)
            ->setUser($user);
        $entityManager->persist($table);
        $entityManager->flush();

        return new JsonResponse(['id' => $table->getId()], Response::HTTP_OK);
    }

    /**
     * @Rest\Put("/{id}", name="tables.update")
     * @Entity("table", options={"mapping": {"id": "id"}})
     * @param EntityManagerInterface $entityManager
     * @param UserRepository         $userRepository
     * @param Request                $request
     * @param Table                  $table
     * @return JsonResponse
     */
    public function updateTable(EntityManagerInterface $entityManager,
                                UserRepository $userRepository,
                                Request $request, Table $table): JsonResponse
    {
        $content = $request->getContent();
        $data = json_decode($content,true);
        if(!is_array($data))
        {
            return new JsonResponse('Invalid json',Response::HTTP_BAD_REQUEST);
        }

        if (isset($content['user_id'])) {
            $user = $userRepository->findOneById((int)$content['user_id']);
            if (empty($user)) {
                return new JsonResponse(sprintf('No user with id %s exist', $content['user_id']));
            }

            $table->setUser($user);
        }

        $validationErrors = Validator::validate($data);

        if ($validationErrors)
        {
            return new JsonResponse($validationErrors,Response::HTTP_BAD_REQUEST);
        }


        if (isset($content['columns'])) {
            $table->setColumns($content['columns']);
        }

        if (isset($content['name'])) {
            $table->setName($content['name']);
        }

        $entityManager->flush();

        return new JsonResponse(['id' => $table->getId()], Response::HTTP_OK);
    }

    /**
     * @Rest\Delete("/{id}", name="tables.delete")
     * @Entity("table", options={"mapping": {"id": "id"}})
     * @param EntityManagerInterface $entityManager
     * @param Table                  $table
     * @return JsonResponse
     */
    public function deleteTable(EntityManagerInterface $entityManager, Table $table): JsonResponse
    {
        $table->setDeleted(true);
        $entityManager->flush();

        return new JsonResponse(['id' => $table->getId()], Response::HTTP_OK);
    }
}