<?php

namespace App\Controller\Api\v1;

use App\Entity\Spreadsheet;
use App\Repository\CellRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Request\ParamFetcherInterface;

/**
 * @Route("/api/v1/spreadsheets")
 */
class CellController extends AbstractV1Controller
{

    /**
     * @Rest\Get("/{id}/range_of_cells", name="spreadsheets.range_of_cells")
     *
     * @Rest\QueryParam(name="start_range", nullable=false,requirements="^\d+,\d+:\d+,\d+$", strict=true)
     * @Rest\QueryParam(name="horizontal_offset", nullable=true, requirements="^\d+$", strict=true)
     * @Rest\QueryParam(name="vertical_offset", nullable=true, requirements="^\d+$", strict=true)
     * @Rest\QueryParam(name="user_id", nullable=false, requirements="^\d+$", strict=true)
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
        $userId        = (int)$params->get('user_id');
        if ($userId !== $spreadsheet->getUser()->getId()) {
            return $this->getAccessDeniedError($spreadsheet->getName(), $userId);
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

        return $this->jsonData($result);
    }

    /**
     * @Rest\Get("/{id}/rows/sum", name="spreadsheets.rows.sum")
     * @Rest\QueryParam(name="row_index", nullable=false, requirements="^\d+$", strict=true)
     * @Rest\QueryParam(name="user_id", nullable=false, requirements="^\d+$", strict=true)
     * @Entity("spreadsheet", options={"mapping": {"id": "id"}})
     *
     * @param ParamFetcherInterface $params
     * @param CellRepository        $cellRepository
     * @param Spreadsheet           $spreadsheet
     *
     * @return JsonResponse
     */
    public function sumRow(ParamFetcherInterface $params, CellRepository $cellRepository, Spreadsheet $spreadsheet): JsonResponse
    {
        $userId = (int)$params->get('user_id');
        if ($userId !== $spreadsheet->getUser()->getId()) {
            return $this->getAccessDeniedError($spreadsheet->getName(), $userId);
        }

        $rowIndex = (int)$params->get('row_index');
        $sum      = $cellRepository->findSumByRow((int)$spreadsheet->getId(), $rowIndex)['sum'] ?? 0;

        return $this->jsonData([
            'row' => $rowIndex,
            'sum' => $this->formatValue($sum)
        ]);
    }

    /**
     * @Rest\Get("/{id}/columns/sum", name="spreadsheets.columns.sum")
     * @Rest\QueryParam(name="column_index", nullable=false, requirements="^\d+$", strict=true)
     * @Rest\QueryParam(name="user_id", nullable=false, requirements="^\d+$", strict=true)
     * @Entity("spreadsheet", options={"mapping": {"id": "id"}})
     *
     * @param CellRepository        $cellRepository
     * @param Spreadsheet           $spreadsheet
     * @param ParamFetcherInterface $params
     * @return JsonResponse
     */
    public function sumColumn(ParamFetcherInterface $params, CellRepository $cellRepository, Spreadsheet $spreadsheet): JsonResponse
    {
        $userId = (int)$params->get('user_id');
        if ($userId !== $spreadsheet->getUser()->getId()) {
            return $this->getAccessDeniedError($spreadsheet->getName(), $userId);
        }

        $columnIndex = (int)$params->get('column_index');
        $sum         = $cellRepository->findSumByColumn((int)$spreadsheet->getId(), $columnIndex)['sum'] ?? 0;

        return $this->jsonData([
            'column' => $columnIndex,
            'sum'    => $this->formatValue($sum)
        ]);
    }

    /**
     * @Rest\Get("/{id}/rows/percentile", name="spreadsheets.rows.percentile")
     * @Rest\QueryParam(name="row_index", nullable=false, requirements="^\d+$", strict=true)
     * @Rest\QueryParam(name="percentile", nullable=false, requirements="^\d+$", strict=true)
     * @Rest\QueryParam(name="user_id", nullable=false, requirements="^\d+$", strict=true)
     * @Entity("spreadsheet", options={"mapping": {"id": "id"}})
     *
     * @param ParamFetcherInterface $params
     * @param CellRepository        $cellRepository
     * @param Spreadsheet           $spreadsheet
     * @return JsonResponse
     */
    public function percentileRow(ParamFetcherInterface $params, CellRepository $cellRepository, Spreadsheet $spreadsheet): JsonResponse
    {
        if ((int)$params->get('user_id') !== $spreadsheet->getUser()->getId()) {
            return $this->getAccessDeniedError($spreadsheet->getName(), $params->get('user_id'));
        }

        $rowIndex      = (int)$params->get('row_index');
        $spreadsheetId = (int)$spreadsheet->getId();
        $countOfValues = $cellRepository->findCountByRow($spreadsheetId, $rowIndex)['count'] ?? 0;
        $offset        = $countOfValues * 0.01 * $params->get('percentile');
        $percentile    = $cellRepository->findPercentileByRow($spreadsheetId, $rowIndex, $offset)['percentile'] ?? 0;

        return $this->jsonData([
            'row'        => $rowIndex,
            'percentile' => $this->formatValue($percentile)
        ]);
    }

    /**
     * @Rest\Get("/{id}/columns/percentile", name="spreadsheets.columns.percentile")
     * @Rest\QueryParam(name="column_index", nullable=false, requirements="^\d+$", strict=true,)
     * @Rest\QueryParam(name="percentile", nullable=false, requirements="^\d{1,2}$", strict=true)
     * @Rest\QueryParam(name="user_id", nullable=false, requirements="^\d+$", strict=true)
     * @Entity("spreadsheet", options={"mapping": {"id": "id"}})
     *
     * @param ParamFetcherInterface $params
     * @param CellRepository        $cellRepository
     * @param Spreadsheet           $spreadsheet
     * @return JsonResponse
     */
    public function percentileColumn(ParamFetcherInterface $params, CellRepository $cellRepository, Spreadsheet $spreadsheet): JsonResponse
    {
        $userId = (int)$params->get('user_id');
        if ($userId !== $spreadsheet->getUser()->getId()) {
            return $this->getAccessDeniedError($spreadsheet->getName(), $params->get('user_id'));
        }

        $columnIndex   = (int)$params->get('column_index');
        $countOfValues = $cellRepository->findCountByColumn((int)$spreadsheet->getId(), $columnIndex)['count'] ?? 0;
        $offset        = $params->get('percentile') * 0.01 * $countOfValues;
        $percentile    = $cellRepository->findPercentileByColumn((int)$spreadsheet->getId(), $columnIndex, $offset)['percentile'] ?? 0;

        return $this->jsonData([
            'column'     => $columnIndex,
            'percentile' => $this->formatValue($percentile)
        ]);

    }

    /**
     * @Rest\Get("/{id}/rows/avg", name="spreadsheets.rows.avg")
     * @Rest\QueryParam(name="row_index", nullable=false, requirements="^\d+$", strict=true)
     * @Rest\QueryParam(name="user_id", nullable=false, requirements="^\d+$", strict=true)
     * @Entity("spreadsheet", options={"mapping": {"id": "id"}})
     *
     * @param CellRepository        $cellRepository
     * @param Spreadsheet           $spreadsheet
     * @param ParamFetcherInterface $params
     * @return JsonResponse
     */
    public function averageRow(ParamFetcherInterface $params, CellRepository $cellRepository, Spreadsheet $spreadsheet): JsonResponse
    {
        if ((int)$params->get('user_id') !== $spreadsheet->getUser()->getId()) {
            return $this->getAccessDeniedError($spreadsheet->getName(), $params->get('user_id'));
        }

        $rowIndex = (int)$params->get('row_index');
        $avg      = $cellRepository->findAvgByRow((int)$spreadsheet->getId(), $rowIndex)['avg'] ?? 0;

        return $this->jsonData([
            'row' => $rowIndex,
            'avg' => $this->formatValue($avg)
        ]);
    }

    /**
     * @Rest\Get("/{id}/columns/avg", name="spreadsheets.columns.avg")
     * @Rest\QueryParam(name="column_index", nullable=false,requirements="^\d+$", strict=true)
     * @Rest\QueryParam(name="user_id", nullable=false,requirements="^\d+$", strict=true)
     * @Entity("spreadsheet", options={"mapping": {"id": "id"}})
     *
     * @param CellRepository        $cellRepository
     * @param Spreadsheet           $spreadsheet
     * @param ParamFetcherInterface $params
     * @return JsonResponse
     */
    public function averageColumn(ParamFetcherInterface $params, CellRepository $cellRepository, Spreadsheet $spreadsheet): JsonResponse
    {
        $userId = (int)$params->get('user_id');
        if ($userId !== $spreadsheet->getUser()->getId()) {
            return $this->getAccessDeniedError($spreadsheet->getName(), $userId);
        }

        $columnIndex = (int)$params->get('column_index');
        $avg         = $cellRepository->findAvgByColumn((int)$spreadsheet->getId(), $columnIndex)['avg'] ?? 0;

        return $this->jsonData([
            'column' => $columnIndex,
            'avg'    => $this->formatValue($avg)
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

    public function getAccessDeniedError(string $spreadsheetName, int $userId): JsonResponse
    {
        return $this->error(sprintf('User with ID %s does not have access to the spreadsheet %s', $userId, $spreadsheetName), 'Access denied');
    }

    public function formatValue(float $value): string
    {
        return sprintf('%.2f', $value);
    }
}