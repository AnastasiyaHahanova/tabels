<?php

namespace App\Controller\Api\v1;

use App\Entity\Table;
use App\Repository\TableValueRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Request\ParamFetcherInterface;

/**
 * @Route("/api/v1/tables")
 */
class TableValueController extends AbstractV1Controller
{

    /**
     * @Rest\Get("/{id}/range_of_cells", name="tables.range_of_cells")
     *
     * @Rest\QueryParam(name="start_range", nullable=false,requirements="^\d+,\d+:\d+,\d+$", strict=true)
     * @Rest\QueryParam(name="horizontal_offset", nullable=true, requirements="^\d+$", strict=true)
     * @Rest\QueryParam(name="vertical_offset", nullable=true, requirements="^\d+$", strict=true)
     * @Rest\QueryParam(name="user_id", nullable=false, requirements="^\d+$", strict=true)
     *
     * @Entity("table", options={"mapping": {"id": "id"}})
     *
     * @param ParamFetcherInterface $params
     * @param TableValueRepository  $tableValueRepository
     * @param Table                 $table
     * @return JsonResponse
     */
    public function getRangeOfCells(ParamFetcherInterface $params, TableValueRepository $tableValueRepository, Table $table): Response
    {
        $result  = [];
        $tableId = (int)$table->getId();
        if ($params->get('user_id') !== $table->getId()) {
            return $this->getAccessDeniedError($table->getName(), $params->get('user_id'));
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
        $rows              = $tableValueRepository->findByRange($tableId, $leftTopRow, $leftTopColumn, $rightBottomRow, $rightBottomColumn);
        foreach ($rows as $row) {
            $key          = sprintf('%s,%s', $row['row'], $row['column']);
            $result[$key] = $row['value'];
        }

        return $this->jsonData($result);
    }

    /**
     * @Rest\Get("/{id}/rows/sum", name="tables.rows.sum")
     * @Rest\QueryParam(name="row_index", nullable=false, requirements="^\d+$", strict=true)
     * @Rest\QueryParam(name="user_id", nullable=false, requirements="^\d+$", strict=true)
     * @Entity("table", options={"mapping": {"id": "id"}})
     *
     * @param ParamFetcherInterface $params
     * @param TableValueRepository  $tableValueRepository
     * @param Table                 $table
     *
     * @return JsonResponse
     */
    public function sumRow(ParamFetcherInterface $params, TableValueRepository $tableValueRepository, Table $table): JsonResponse
    {
        if ($params->get('user_id') !== $table->getId()) {
            return $this->getAccessDeniedError($table->getName(), $params->get('user_id'));
        }

        $rowIndex = (int)$params->get('row_index');
        $sum      = $tableValueRepository->findSumByRow((int)$table->getId(), $rowIndex)['sum'] ?? 0;

        return $this->jsonData([
            'row' => $rowIndex,
            'sum' => $sum]);
    }

    /**
     * @Rest\Get("/{id}/columns/sum", name="tables.columns.sum")
     * @Rest\QueryParam(name="column_index", nullable=false, requirements="^\d+$", strict=true)
     * @Rest\QueryParam(name="user_id", nullable=false, requirements="^\d+$", strict=true)
     * @Entity("table", options={"mapping": {"id": "id"}})
     *
     * @param TableValueRepository  $tableValueRepository
     * @param Table                 $table
     * @param ParamFetcherInterface $params
     * @return JsonResponse
     */
    public function sumColumn(ParamFetcherInterface $params, TableValueRepository $tableValueRepository, Table $table): JsonResponse
    {
        if ($params->get('user_id') !== $table->getId()) {
            return $this->getAccessDeniedError($table->getName(), $params->get('user_id'));
        }

        $columnIndex = (int)$params->get('column_index');
        $sum         = $tableValueRepository->findByColumn((int)$table->getId(), $columnIndex)['sum'] ?? 0;

        return $this->jsonData([
            'column' => $columnIndex,
            'sum'    => $sum
        ]);
    }

    /**
     * @Rest\Get("/{id}/rows/percentile", name="tables.rows.percentile")
     * @Rest\QueryParam(name="row_index", nullable=false, requirements="^\d+$", strict=true)
     * @Rest\QueryParam(name="percentile", nullable=false, requirements="^\d+$", strict=true)
     * @Entity("table", options={"mapping": {"id": "id"}})
     *
     * @param ParamFetcherInterface $params
     * @param TableValueRepository  $tableValueRepository
     * @param Table                 $table
     * @return JsonResponse
     */
    public function percentileRow(ParamFetcherInterface $params, TableValueRepository $tableValueRepository, Table $table): JsonResponse
    {
        if ($params->get('user_id') !== $table->getId()) {
            return $this->getAccessDeniedError($table->getName(), $params->get('user_id'));
        }

        $rowIndex      = $params->get('row_index');
        $tableId       = (int)$table->getId();
        $countOfValues = $tableValueRepository->findCountByRow($tableId, $rowIndex)['count'] ?? 0;
        $offset        = $countOfValues * 0.01 * $params->get('percentile');
        $percentile    = $tableValueRepository->findPercentileByRow($tableId, $rowIndex, $offset)['percentile'] ?? 0;

        return $this->jsonData([
            'row'        => $rowIndex,
            'percentile' => $percentile
        ]);
    }

    /**
     * @Rest\Get("/{id}/columns/percentile", name="tables.columns.percentile")
     * @Rest\QueryParam(name="column_index", nullable=false, requirements="^\d+$", strict=true,)
     * @Rest\QueryParam(name="percentile", nullable=false, requirements="^\d{1,2}$", strict=true)
     * @Entity("table", options={"mapping": {"id": "id"}})
     *
     * @param ParamFetcherInterface $params
     * @param TableValueRepository  $tableValueRepository
     * @param Table                 $table
     * @return JsonResponse
     */
    public function percentileColumn(ParamFetcherInterface $params, TableValueRepository $tableValueRepository, Table $table): JsonResponse
    {
        if ($params->get('user_id') !== $table->getId()) {
            return $this->getAccessDeniedError($table->getName(), $params->get('user_id'));
        }

        $columnIndex   = $params->get('column_index');
        $countOfValues = $tableValueRepository->findCountByColumn((int)$table->getId(), $columnIndex)['count'] ?? 0;
        $offset        = $params->get('percentile') * 0.01 * $countOfValues;
        $percentile    = $tableValueRepository->findPercentileByColumn((int)$table->getId(), $columnIndex, $offset)['percentile'] ?? 0;

        return $this->jsonData([
            'column'     => $columnIndex,
            'percentile' => $percentile
        ]);

    }

    /**
     * @Rest\Get("/{id}/rows/avg", name="tables.rows.avg")
     * @Rest\QueryParam(name="row_index", nullable=false, requirements="^\d+$", strict=true)
     * @Rest\QueryParam(name="user_id", nullable=false, requirements="^\d+$", strict=true)
     * @Entity("table", options={"mapping": {"id": "id"}})
     *
     * @param TableValueRepository  $tableValueRepository
     * @param Table                 $table
     * @param ParamFetcherInterface $params
     * @return JsonResponse
     */
    public function averageRow(ParamFetcherInterface $params, TableValueRepository $tableValueRepository, Table $table): JsonResponse
    {
        if ($params->get('user_id') !== $table->getId()) {
            return $this->getAccessDeniedError($table->getName(), $params->get('user_id'));
        }

        $rowIndex      = (int)$params->get('row_index');
        $countOfValues = $tableValueRepository->findCountByRow((int)$table->getId(), $rowIndex)['count'] ?? 0;
        $offset        = $params->get('percentile') * 0.01 * $countOfValues;
        $percentile    = $tableValueRepository->findPercentileByColumn((int)$table->getId(), $rowIndex, $offset)['percentile'] ?? 0;

        return $this->jsonData([
            'row' => $rowIndex,
            'avg' => $percentile
        ]);
    }

    /**
     * @Rest\Get("/{id}/columns/avg", name="tables.columns.avg")
     * @Rest\QueryParam(name="column_index", nullable=false,requirements="^\d+$", strict=true)
     * @Entity("table", options={"mapping": {"id": "id"}})
     *
     * @param TableValueRepository  $tableValueRepository
     * @param Table                 $table
     * @param ParamFetcherInterface $params
     * @return JsonResponse
     */
    public function averageColumn(ParamFetcherInterface $params, TableValueRepository $tableValueRepository, Table $table): JsonResponse
    {
        if ($params->get('user_id') !== $table->getId()) {
            return $this->getAccessDeniedError($table->getName(), $params->get('user_id'));
        }

        $columnIndex = (int)$params->get('column_index');
        $avg         = $tableValueRepository->findAvgByColumn((int)$table->getId(), $columnIndex)['avg'] ?? 0;

        return $this->jsonData([
            'column' => $columnIndex,
            'avg'    => $avg
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

    public function getAccessDeniedError(string $tableName, string $userId): JsonResponse
    {
        return $this->error(sprintf('User with ID "%s" does not have access to the table %s', $userId, $tableName), 'Access denied');
    }
}