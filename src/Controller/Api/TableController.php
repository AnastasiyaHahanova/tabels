<?php

namespace App\Controller\Api;

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
     * @return JsonResponse
     */
    public function createTable(EntityManagerInterface $entityManager, UserRepository $userRepository, Request $request): JsonResponse
    {
        $content = $request->request->all();
        $userId  = (int)$content['user_id'];
        $columns = $content['columns'] ?? [];
        $user    = $userRepository->findOneById($userId);
        if (empty($user)) {
            return new JsonResponse(sprintf('No user with id %s exist', $userId), Response::HTTP_BAD_REQUEST);
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
     * @return JsonResponse
     */
    public function updateTable(EntityManagerInterface $entityManager,
                                UserRepository $userRepository,
                                Request $request, Table $table): JsonResponse
    {
        $content = $request->request->all();
        if (isset($content['user_id'])) {
            $user = $userRepository->findOneById((int)$content['user_id']);
            if (empty($user)) {
                return new JsonResponse(sprintf('No user with id %s exist', $content['user_id']));
            }

            $table->setUser($user);
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
     * @return JsonResponse
     */
    public function deleteTable(EntityManagerInterface $entityManager, Table $table): JsonResponse
    {
        $table->setDeleted(true);
        $entityManager->flush();

        return new JsonResponse(['id' => $table->getId()], Response::HTTP_OK);
    }
}