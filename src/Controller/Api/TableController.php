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
class TableController extends AbstractController
{

    /**
     * @Rest\Get("/{id}/range_of_cells", name="tables.range_of_cells")
     *
     * @Rest\QueryParam(name="start_range", nullable=false)
     * @Rest\QueryParam(name="horizontal_offset", nullable=true)
     * @Rest\QueryParam(name="vertical_offset", nullable=true)
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
}