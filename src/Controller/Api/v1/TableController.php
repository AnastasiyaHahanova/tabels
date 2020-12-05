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
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TableController extends AbstractV1Controller
    /**
     * @Route("api/v1/tables")
     */
{
    /**
     * @Rest\Post("/", name="tables.create")
     * @param EntityManagerInterface $entityManager
     * @param UserRepository         $userRepository
     * @param Request                $request
     * @return JsonResponse
     */
    public function createTable(ValidatorInterface $validator,
                                EntityManagerInterface $entityManager,
                                UserRepository $userRepository,
                                Request $request): JsonResponse
    {
        $content = $request->getContent();
        $data    = json_decode($content, true);
        $userId  = (int)$data['user_id'];
        $columns = $data['columns'] ?? [];
        $name    = $data['name'] ?? '';
        $user    = $userRepository->findOneById($userId);
        if (empty($user)) {
            return $this->error(sprintf('No user with id %f exist', $userId));
        }

        $table = (new Table())
            ->setName($name)
            ->setColumns($columns)
            ->setUser($user);

        $validationErrors = $validator->validate($table);

        if ($validationErrors->count() > 0) {
            return $this->error((string)$validationErrors);
        }

        $entityManager->persist($table);
        $entityManager->flush();

        return $this->jsonData(['id' => $table->getId()]);
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
    public function updateTable(ValidatorInterface $validator,
                                EntityManagerInterface $entityManager,
                                UserRepository $userRepository,
                                Request $request, Table $table): JsonResponse
    {
        if ($table->isDeleted()) {
            return $this->error(sprintf('No table found with ID %s', $table->getId()));
        }

        $content = $request->getContent();
        $data    = json_decode($content, true);
        if (!is_array($data)) {
            return new JsonResponse('Invalid json', Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['user_id'])) {
            $user = $userRepository->findOneById((int)$data['user_id']);
            if (empty($user)) {
                return new JsonResponse(sprintf('No user with id %s exist', $data['user_id']));
            }

            $table->setUser($user);
        }

        if (isset($data['columns'])) {
            $table->setColumns($data['columns']);
        }

        if (isset($data['name'])) {
            $table->setName($data['name']);
        }

        $validationErrors = $validator->validate($table);
        if ($validationErrors->count() > 0) {
            return $this->error((string)$validationErrors);
        }

        $entityManager->flush();

        return $this->jsonData(['id' => $table->getId()]);
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

        return $this->jsonData(['id' => $table->getId()]);
    }
}