<?php

namespace App\Controller\Api\v1;

use App\Entity\Spreadsheet;
use App\Repository\SpreadsheetRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/spreadsheets")
 */
class SpreadsheetController extends AbstractV1Controller
{
    /**
     * @Rest\Post("/", name="spreadsheets.create")
     * @param EntityManagerInterface $entityManager
     * @param UserRepository         $userRepository
     * @param Request                $request
     * @return JsonResponse
     */
    public function createSpreadsheet(ValidatorInterface $validator,
                                      EntityManagerInterface $entityManager,
                                      SpreadsheetRepository $spreadsheetRepository,
                                      Request $request): JsonResponse
    {
        $content = $request->getContent();
        $data    = json_decode($content, true);
        $columns = $data['columns'] ?? [];
        $name    = $data['name'] ?? '';

        $spreadsheet = $spreadsheetRepository->findOneByNameAndUser($name, $this->getUser());
        if ($spreadsheet) {
            return $this->error(sprintf('The spreadsheet with name %s and user %s already exists', $spreadsheet->getName(), $this->getUser()->getUsername()));
        }

        $spreadsheet = (new Spreadsheet())
            ->setName($name)
            ->setColumns($columns)
            ->setUser($this->getUser());

        $validationErrors = $validator->validate($spreadsheet);

        if ($validationErrors->count() > 0) {
            return $this->error((string)$validationErrors);
        }

        $entityManager->persist($spreadsheet);
        $entityManager->flush();

        return $this->json(['id' => $spreadsheet->getId()]);
    }

    /**
     * @Rest\Put("/{id}", name="spreadsheets.update")
     * @Entity("spreadsheet", options={"mapping": {"id": "id"}})
     * @param EntityManagerInterface $entityManager
     * @param UserRepository         $userRepository
     * @param Request                $request
     * @param Spreadsheet            $spreadsheet
     * @return JsonResponse
     */
    public function updateSpreadsheet(ValidatorInterface $validator,
                                      EntityManagerInterface $entityManager,
                                      UserRepository $userRepository,
                                      Request $request, Spreadsheet $spreadsheet): JsonResponse
    {
        if ($spreadsheet->isDeleted()) {
            return $this->error(sprintf('No table found with ID %s', $spreadsheet->getId()));
        }

        if ($this->getUser()->getId() !== $spreadsheet->getUser()->getId()) {
            return $this->error('You do not have access to perform this operation', 'Access denied');
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

            $spreadsheet->setUser($user);
        }

        if (isset($data['columns'])) {
            $spreadsheet->setColumns($data['columns']);
        }

        if (isset($data['name'])) {
            $spreadsheet->setName($data['name']);
        }

        $validationErrors = $validator->validate($spreadsheet);
        if ($validationErrors->count() > 0) {
            return $this->error((string)$validationErrors);
        }

        $entityManager->flush();

        return $this->json(['id' => $spreadsheet->getId()]);
    }

    /**
     * @Rest\Delete("/{id}", name="spreadsheets.delete")
     * @Entity("spreadsheet", options={"mapping": {"id": "id"}})
     * @param EntityManagerInterface $entityManager
     * @param Spreadsheet            $spreadsheet
     * @return JsonResponse
     */
    public function deleteSpreadsheet(EntityManagerInterface $entityManager, Spreadsheet $spreadsheet): JsonResponse
    {
        if ($this->getUser()->getId() !== $spreadsheet->getUser()->getId()) {
            return $this->error('You do not have access to perform this operation', 'Access denied');
        }

        $entityManager->remove($spreadsheet);
        $entityManager->flush();

        return $this->json(['id' => $spreadsheet->getId()]);
    }
}