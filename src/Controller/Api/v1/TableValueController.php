<?php

namespace App\Controller\Api\v1;

use App\Entity\Table;
use App\Repository\TableValueRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Request\ParamFetcherInterface;

/**
 * @Route("/api/v1/tables")
 */
class TableValueController extends AbstractController
{

    /**
     * @Rest\Get("/{id}/range_of_cells", name="tables.range_of_cells")
     *
     * @Rest\QueryParam(name="start_range", nullable=false,requirements="^\d+,\d+:\d+,\d+$", strict=true)
     * @Rest\QueryParam(name="horizontal_offset", nullable=true, requirements="^\d+$", strict=true)
     * @Rest\QueryParam(name="vertical_offset", nullable=true, requirements="^\d+$", strict=true)
     *
     * @Entity("table", options={"mapping": {"id": "id"}})
     *
     * @param ParamFetcherInterface $params
     * @param TableValueRepository  $tableValueRepository
     * @param Table                 $table
     * @return JsonResponse
     */
    public function getRangeOfCells(ParamFetcherInterface $params, TableValueRepository $tableValueRepository, Table $table): JsonResponse
    {
        $result          = [];
        $rangeParameters = $this->extractRangeParameters($params->get('start_range'), (int)$params->get('horizontal_offset'), (int)$params->get('vertical_offset'));
        if (empty($rangeParameters)) {
            return new JsonResponse('Wrong range parameters', Response::HTTP_BAD_REQUEST);
        }

        $leftTopRow        = $rangeParameters['left_top_row'] ?? 0;
        $leftTopColumn     = $rangeParameters['left_top_column'] ?? 0;
        $rightBottomRow    = $rangeParameters['right_bottom_row'] ?? 0;
        $rightBottomColumn = $rangeParameters['right_bottom_column'] ?? 0;
        $rows              = $tableValueRepository->findByRange((int)$table->getId(), $leftTopRow, $leftTopColumn, $rightBottomRow, $rightBottomColumn);

        foreach ($rows as $row) {
            $result[sprintf('%s,%s', $row['row'], $row['column'])] = $row['value'];
        }

        return new JsonResponse($result, Response::HTTP_OK);
    }

    /**
     * @Rest\Get("/{id}/rows/sum", name="tables.rows.sum")
     * @Rest\QueryParam(name="row_index", nullable=false, requirements="^\d+$", strict=true)
     * @Entity("table", options={"mapping": {"id": "id"}})
     *
     * @param ParamFetcherInterface $params
     * @param TableValueRepository  $tableValueRepository
     * @param Table                 $table
     * @return JsonResponse
     */
    public function sumRow(ParamFetcherInterface $params, TableValueRepository $tableValueRepository, Table $table): JsonResponse
    {
        $rowIndex = (int)$params->get('row_index');
        $sum      = $tableValueRepository->findSumByRow((int)$table->getId(), $rowIndex)['sum'] ?? 0;

        return new JsonResponse([
            'row' => $rowIndex,
            'sum' => $sum
        ], Response::HTTP_OK);
    }

    /**
     * @Rest\Get("/{id}/columns/sum", name="tables.columns.sum")
     * @Rest\QueryParam(name="column_index", nullable=false, requirements="^\d+$", strict=true)
     * @Entity("table", options={"mapping": {"id": "id"}})
     *
     * @param TableValueRepository  $tableValueRepository
     * @param Table                 $table
     * @param ParamFetcherInterface $params
     * @return JsonResponse
     */
    public function sumColumn(ParamFetcherInterface $params, TableValueRepository $tableValueRepository, Table $table): JsonResponse
    {
        $columnIndex = (int)$params->get('column_index');
        $sum         = $tableValueRepository->findByColumn((int)$table->getId(), $columnIndex)['sum'] ?? 0;

        return new JsonResponse([
            'column' => $columnIndex,
            'sum'    => $sum
        ], Response::HTTP_OK);

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
        $rowIndex = (int)$params->get('row_index');
        $rows     = $tableValueRepository->findByRow((int)$table->getId(), $rowIndex);
        $this->calculatePercentile((int)$params->get('percentile'), $rows);

        return new JsonResponse([
            'row'        => $rowIndex,
            'percentile' => $this->calculatePercentile((int)$params->get('percentile'), $rows)
        ], Response::HTTP_OK);
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
        $columnIndex = $params->get('column_index');
        $rows        = $tableValueRepository->findByColumn((int)$table->getId(), $columnIndex);

        return new JsonResponse([
            'column'     => $columnIndex,
            'percentile' => $this->calculatePercentile($params->get('percentile'), $rows)
        ], Response::HTTP_OK);

    }

    /**
     * @Rest\Get("/{id}/rows/avg", name="tables.rows.avg")
     * @Rest\QueryParam(name="row_index", nullable=false, requirements="^\d+$", strict=true)
     * @Entity("table", options={"mapping": {"id": "id"}})
     *
     * @param TableValueRepository  $tableValueRepository
     * @param Table                 $table
     * @param ParamFetcherInterface $params
     * @return JsonResponse
     */
    public function averageRow(ParamFetcherInterface $params, TableValueRepository $tableValueRepository, Table $table): JsonResponse
    {
        $rowIndex = (int)$params->get('row_index');
        $sum      = $tableValueRepository->findAvgByRow((int)$table->getId(), $rowIndex)['avg'] ?? 0;

        return new JsonResponse([
            'row' => $rowIndex,
            'avg' => $sum
        ], Response::HTTP_OK);
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
        $columnIndex = (int)$params->get('column_index');
        $avg         = $tableValueRepository->findAvgByColumn((int)$table->getId(), $columnIndex)['avg'] ?? 0;

        return new JsonResponse([
            'column' => $columnIndex,
            'avg'    => $avg
        ], Response::HTTP_OK);

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

    public function calculatePercentile(int $percentile, array $rows): int
    {
        $values = [];
        $count  = count($rows);
        foreach ($rows as $row) {
            $values[] = $row['value'];
        }

        sort($values);
        $percentileIndex = (int)round($percentile * 0.01 * $count);
        if (isset($values[$percentileIndex])) {
            return $values[$percentileIndex];
        }

        return 0;
    }
}