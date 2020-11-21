<?php

namespace App\Controller\Api;

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
 * @Route("/tables")
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
     * @return JsonResponse
     */
    public function getRangeOfCells(ParamFetcherInterface $params, TableValueRepository $tableValueRepository, Table $table)
    {
        $result          = [];
        $rangeParameters = $this->extractRangeParameters($params->get('start_range'), (int)$params->get('horizontal_offset'), (int)$params->get('vertical_offset'));
        if (empty($rangeParameters)) {
            return new JsonResponse('Wrong range parameters', Response::HTTP_BAD_REQUEST);
        }
        $rows = $tableValueRepository->findByRange($table->getId(), $rangeParameters['left_top_row'], $rangeParameters['left_top_column'], $rangeParameters['right_bottom_row'], $rangeParameters['right_bottom_column']);

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
     * @return JsonResponse
     */
    public function sumRow(ParamFetcherInterface $params, TableValueRepository $tableValueRepository, Table $table)
    {
        $rowIndex = (int)$params->get('row_index');
        $rows     = $tableValueRepository->findByRow($table->getId(), $rowIndex);

        return new JsonResponse([
            'row' => $rowIndex,
            'sum' => $this->calculateSum($rows)
        ], Response::HTTP_OK);
    }

    /**
     * @Rest\Get("/{id}/columns/sum", name="tables.columns.sum")
     * @Rest\QueryParam(name="column_index", nullable=false, requirements="^\d+$", strict=true)
     * @Entity("table", options={"mapping": {"id": "id"}})
     *
     * @param ParamFetcherInterface $params
     * @return JsonResponse
     */
    public function sumColumn(ParamFetcherInterface $params, TableValueRepository $tableValueRepository, Table $table)
    {
        $columnIndex = (int)$params->get('column_index');
        $rows        = $tableValueRepository->findByColumn($table->getId(), $columnIndex);

        return new JsonResponse([
            'column' => $columnIndex,
            'sum'    => $this->calculateSum($rows)
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
    public function percentileRow(ParamFetcherInterface $params, TableValueRepository $tableValueRepository, Table $table)
    {
        $rowIndex = (int)$params->get('row_index');
        $rows     = $tableValueRepository->findByRow($table->getId(), $rowIndex);
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
     * @return JsonResponse
     */
    public function percentileColumn(ParamFetcherInterface $params, TableValueRepository $tableValueRepository, Table $table)
    {
        $columnIndex = (int)$params->get('column_index');
        $rows        = $tableValueRepository->findByColumn($table->getId(), $columnIndex);

        return new JsonResponse([
            'column'     => $columnIndex,
            'percentile' => $this->calculatePercentile((int)$params->get('percentile'), $rows)
        ], Response::HTTP_OK);

    }

    /**
     * @Rest\Get("/{id}/rows/sum", name="tables.rows.sum")
     * @Rest\QueryParam(name="row_index", nullable=false, requirements="^\d+$", strict=true)
     * @Entity("table", options={"mapping": {"id": "id"}})
     *
     * @param ParamFetcherInterface $params
     * @return JsonResponse
     */
    public function averageRow(ParamFetcherInterface $params, TableValueRepository $tableValueRepository, Table $table)
    {
        $rowIndex = (int)$params->get('row_index');
        $rows     = $tableValueRepository->findByRow($table->getId(), $rowIndex);

        return new JsonResponse([
            'row' => $rowIndex,
            'sum' => $this->calculateSum($rows)
        ], Response::HTTP_OK);
    }

    /**
     * @Rest\Get("/{id}/columns/sum", name="tables.columns.sum")
     * @Rest\QueryParam(name="column_index", nullable=false,requirements="^\d+$", strict=true)
     * @Entity("table", options={"mapping": {"id": "id"}})
     *
     * @param ParamFetcherInterface $params
     * @return JsonResponse
     */
    public function averageColumn(ParamFetcherInterface $params, TableValueRepository $tableValueRepository, Table $table)
    {
        $columnIndex = (int)$params->get('column_index');
        $rows        = $tableValueRepository->findByColumn($table->getId(), $columnIndex);

        return new JsonResponse([
            'column' => $columnIndex,
            'sum'    => $this->calculateSum($rows)
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

    public function calculateSum(array $rows): int
    {
        $result = 0;
        foreach ($rows as $row) {
            $result = $result + $row['value'];
        }

        return $result;
    }

    public function calculateAverage(array $rows): int
    {
        if (empty($rows)) {
            return 0;
        }

        $sum   = 0;
        $count = 0;
        foreach ($rows as $row) {
            $sum = $sum + $row['value'];
            $count++;
        }

        return $sum / $count;
    }

    public function calculatePercentile(int $percentile, array $rows): int
    {
        $values = [];
        $count  = 0;
        foreach ($rows as $row) {
            array_push($values, $row['value']);
            $count++;
        }

        sort($values);
        $percentileIndex = round($percentile * 0.01 * $count);
        if (isset($values[$percentileIndex])) {
            return $values[$percentileIndex];
        }

        return 0;
    }
}