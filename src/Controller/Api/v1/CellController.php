<?php

namespace App\Controller\Api\v1;

use App\Entity\Cell;
use App\Entity\Spreadsheet;
use App\Repository\CellRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/spreadsheets")
 */
class CellController extends AbstractV1Controller
{

    /**
     * @Rest\Get("/{id}/range_of_cells", name="spreadsheets.range_of_cells")
     *
     * @Rest\QueryParam(name="start_range", nullable=false,requirements="^[1-9]\d*,[1-9]\d*:[1-9]\d*,[1-9]\d*$", strict=true)
     * @Rest\QueryParam(name="horizontal_offset", nullable=true, requirements="^[1-9]\d*$", strict=true)
     * @Rest\QueryParam(name="vertical_offset", nullable=true, requirements="^[1-9]\d*$", strict=true)
     *
     * @Entity("spreadsheet", options={"mapping": {"id": "id"}})
     *
     * @param ParamFetcherInterface $params
     * @param CellRepository        $cellRepository
     * @param Spreadsheet           $spreadsheet
     * @return JsonResponse
     */
    public function getRangeOfCells(ParamFetcherInterface $params, CellRepository $cellRepository, Spreadsheet $spreadsheet): Response
    {
        $result        = [];
        $spreadsheetId = (int)$spreadsheet->getId();
        $user          = $this->getUser();
        if ($user->getId() !== $spreadsheet->getUser()->getId()) {
            return $this->getAccessDeniedError($spreadsheet->getName(), $user->getUsername());
        }

        $rangeParameters = $this->extractRangeParameters(
            $params->get('start_range'),
            $params->get('horizontal_offset'),
            $params->get('vertical_offset')
        );
        if (empty($rangeParameters)) {
            return $this->error('Please make sure that you have entered the correct coordinates of the upper left and lower right corners',
                'Wrong range parameters.');
        }

        $leftTopRow        = $rangeParameters['left_top_row'] ?? 0;
        $leftTopColumn     = $rangeParameters['left_top_column'] ?? 0;
        $rightBottomRow    = $rangeParameters['right_bottom_row'] ?? 0;
        $rightBottomColumn = $rangeParameters['right_bottom_column'] ?? 0;
        $rows              = $cellRepository->findByRange($spreadsheetId, $leftTopRow, $leftTopColumn, $rightBottomRow, $rightBottomColumn);
        foreach ($rows as $row) {
            $key          = sprintf('%s,%s', $row['row'], $row['column']);
            $result[$key] = $row['value'];
        }

        return $this->json($result);
    }

    /**
     * @Rest\Get("/{id}/sum", name="spreadsheets.sum")
     * @Rest\QueryParam(name="index", nullable=false, requirements="^[1-9]\d*$", strict=true)
     * @Rest\QueryParam(name="parameter_name", nullable=false,requirements="(row|column)",strict=true)
     * @Entity("spreadsheet", options={"mapping": {"id": "id"}})
     *
     * @param CellRepository        $cellRepository
     * @param Spreadsheet           $spreadsheet
     * @param ParamFetcherInterface $params
     * @return JsonResponse
     */
    public function sum(ParamFetcherInterface $params, CellRepository $cellRepository, Spreadsheet $spreadsheet): JsonResponse
    {
        $user = $this->getUser();
        if ($user->getId() !== $spreadsheet->getUser()->getId()) {
            return $this->getAccessDeniedError($spreadsheet->getName(), $user->getUsername());
        }

        $index         = (int)$params->get('index');
        $parameterName = $params->get('parameter_name');
        $sum = ($parameterName === 'row') ? $cellRepository->findSumByRow((int)$spreadsheet->getId(), $index)['sum'] : $cellRepository->findSumByColumn((int)$spreadsheet->getId(), $index)['sum'];

        return $this->json([
            $parameterName => $index,
            'sum'          => $this->formatValue($sum)
        ]);
    }

    /**
     * @Rest\Get("/{id}/percentile", name="spreadsheets.percentile")
     * @Rest\QueryParam(name="index", nullable=false, requirements="^[1-9]\d*$", strict=true, default="1")
     * @Rest\QueryParam(name="percentile", nullable=false, requirements="^[1-9]?[0-9]$|^100$", strict=true, default="95")
     * @Rest\QueryParam(name="parameter_name", nullable=false, requirements="(row|column)",strict=true)
     * @Entity("spreadsheet", options={"mapping": {"id": "id"}})
     *
     * @param ParamFetcherInterface $params
     * @param CellRepository        $cellRepository
     * @param Spreadsheet           $spreadsheet
     * @return JsonResponse
     */
    public function percentile(ParamFetcherInterface $params, CellRepository $cellRepository, Spreadsheet $spreadsheet): JsonResponse
    {
        $user = $this->getUser();
        if ($user->getId() !== $spreadsheet->getUser()->getId()) {
            return $this->getAccessDeniedError($spreadsheet->getName(), $user->getUsername());
        }

        $index         = (int)$params->get('index');
        $parameterName = $params->get('parameter_name');
        $percentilePercent = (int)$params->get('percentile');
        $spreadsheetId     = (int)$spreadsheet->getId();
        $countOfValues     = ($parameterName === 'column') ? $cellRepository->findCountByColumn($spreadsheetId, $index)['count'] : $countOfValues = $cellRepository->findCountByRow($spreadsheetId, $index)['count'];
        $percentile        = 0;
        if ($countOfValues) {
            $offset     = round($percentilePercent * 0.01 * $countOfValues);
            $offset     = $offset ? $offset - 1 : 0;
            $percentile = ($parameterName === 'column') ? $cellRepository->findPercentileByColumn($spreadsheetId, $index, (int)$offset)['percentile'] : $cellRepository->findPercentileByRow($spreadsheetId, $index, (int)$offset)['percentile'];
        }

        $message = sprintf('%s percent percentile', $percentilePercent);

        return $this->json([
            $parameterName => $index,
            $message       => $this->formatValue($percentile)
        ]);

    }

    /**
     * @Rest\Get("/{id}/avg", name="spreadsheets.avg")
     * @Rest\QueryParam(name="index", nullable=false, requirements="^[1-9]\d*$", strict=true, default="1")
     * @Entity("spreadsheet", options={"mapping": {"id": "id"}})
     * @Rest\QueryParam(name="parameter_name", nullable=false,requirements="(row|column)",strict=true)
     * @param CellRepository        $cellRepository
     * @param Spreadsheet           $spreadsheet
     * @param ParamFetcherInterface $params
     * @return JsonResponse
     */
    public function average(ParamFetcherInterface $params, CellRepository $cellRepository, Spreadsheet $spreadsheet): JsonResponse
    {
        $user = $this->getUser();
        if ($user->getId() !== $spreadsheet->getUser()->getId()) {
            return $this->getAccessDeniedError($spreadsheet->getName(), $user->getUsername());
        }

        $parameterName = $params->get('parameter_name');
        $index = (int)$params->get('index');
        $avg   = ($parameterName === 'column') ? $cellRepository->findAvgByColumn((int)$spreadsheet->getId(), $index) : $cellRepository->findAvgByRow((int)$spreadsheet->getId(), $index);
        return $this->json([
            $parameterName => $index,
            'avg'          => $this->formatValue($avg)
        ]);
    }

    public function extractRangeParameters(string $range, int $horizontalOffset, int $verticalOffset): array
    {
        [$leftTop, $rightBottom] = explode(':', $range);
        [$leftTopRow, $leftTopColumn] = explode(',', $leftTop);
        [$rightBottomRow, $rightBottomColumn] = explode(',', $rightBottom);
        $leftTopRow        = (int)$leftTopRow;
        $leftTopColumn     = (int)$leftTopColumn;
        $rightBottomRow    = (int)$rightBottomRow;
        $rightBottomColumn = (int)$rightBottomColumn;

        if (($leftTopRow >= $rightBottomRow) || ($leftTopColumn >= $rightBottomColumn)) {
            return [];
        }

        $rangeParameters = [
            'left_top_row'        => $leftTopRow,
            'left_top_column'     => $leftTopColumn,
            'right_bottom_row'    => $rightBottomRow,
            'right_bottom_column' => $rightBottomColumn];

        if ($horizontalOffset > 0) {
            $rangeParameters['left_top_column']     = $leftTopColumn + $horizontalOffset;
            $rangeParameters['right_bottom_column'] = $rightBottomColumn + $horizontalOffset;
        }

        if ($verticalOffset > 0) {
            $rangeParameters['left_top_row']     = $leftTopRow + $verticalOffset;
            $rangeParameters['right_bottom_row'] = $rightBottomRow + $verticalOffset;
        }

        return $rangeParameters;
    }

    /**
     * @Rest\Post ("/{id}/cells", name="spreadsheets.cell.set_value")
     * @param EntityManagerInterface $entityManager
     * @param CellRepository         $cellRepository
     * @param UserRepository         $userRepository
     * @param ValidatorInterface     $validator
     * @param Request                $request
     * @Entity("spreadsheet", options={"mapping": {"id": "id"}})
     * @return JsonResponse
     */
    public function setCellValue(Request $request,
                                 EntityManagerInterface $entityManager,
                                 CellRepository $cellRepository,
                                 ValidatorInterface $validator,
                                 Spreadsheet $spreadsheet
    ): JsonResponse
    {
        $content = $request->getContent();
        $data    = json_decode($content, true);
        if (!is_array($data)) {
            return $this->error('Invalid json');
        }

        $user = $this->getUser();
        if ($spreadsheet->getUser()->getId() !== $user->getId()) {
            return $this->getAccessDeniedError($spreadsheet->getName(), $user->getUsername());
        }

        if (!isset($data['row']) || !isset($data['column']) || !isset($data['value'])) {
            return $this->error('Row, column, value parameters must not be empty');
        }

        $row    = (int)$data['row'];
        $column = (int)$data['column'];
        $value  = $data['value'];

        $cell = $cellRepository->findOneByRowAndColumn($row, $column);

        if ($cell) {
            $cell->setValue($value);
        } else {
            $cell = (new Cell())
                ->setRow($row)
                ->setColumn($column)
                ->setSpreadsheet($spreadsheet)
                ->setValue($value);
        }

        $validationErrors = $validator->validate($cell);
        if ($validationErrors->count() > 0) {
            return $this->error((string)$validationErrors);
        }

        $entityManager->persist($cell);
        $entityManager->flush();

        return $this->json('Value successfully saved!');
    }

    /**
     * @Rest\Put ("/{id}/cells", name="spreadsheets.cell.delete_value")
     * @param EntityManagerInterface $entityManager
     * @param Spreadsheet            $spreadsheet
     * @param CellRepository         $cellRepository
     * @param Request                $request
     * @Entity("spreadsheet", options={"mapping": {"id": "id"}})
     * @return JsonResponse
     */
    public function deleteCellValue(Request $request,
                                    EntityManagerInterface $entityManager,
                                    Spreadsheet $spreadsheet,
                                    CellRepository $cellRepository): JsonResponse
    {
        $content = $request->getContent();
        $data    = json_decode($content, true);
        if (!is_array($data)) {
            return $this->error('Invalid json');
        }

        $user = $this->getUser();
        if ($spreadsheet->getUser()->getId() !== $user->getId()) {
            return $this->getAccessDeniedError($spreadsheet->getName(), $user->getUsername());
        }

        if (!isset($data['row']) || !isset($data['column'])) {
            return $this->error('Row and column must not be empty');
        }

        $row    = $data['row'];
        $column = $data['column'];
        if (!is_integer($row) || !is_integer($column)) {
            return $this->error('Invalid coordinates of cell');
        }

        $cell = $cellRepository->findOneByRowAndColumn($row, $column);

        if (empty($cell)) {
            return $this->error(sprintf('No cell found with row %s and column %s', $row, $column));
        }

        $entityManager->remove($cell);
        $entityManager->flush();

        return $this->json('Value successfully deleted!');
    }

    public function getAccessDeniedError(string $spreadsheetName, string $username): JsonResponse
    {
        return $this->error(sprintf('User %s does not have access to the spreadsheet %s', $username, $spreadsheetName), 'Access denied');
    }

    public function formatValue(float $value): string
    {
        return sprintf('%.2f', $value);
    }
}