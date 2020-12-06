<?php

namespace App\Repository;

use App\Entity\Cell;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Cell|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cell|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cell[]    findAll()
 * @method Cell[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CellRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cell::class);
    }

    /**
     * @param int $leftTopRow
     * @param int $leftTopColumn
     * @param int $rightBottomRow
     * @param int $rightBottomColumn
     * @param int $spreadsheet
     * @return Cell[]|[]
     */
    public function findByRange(int $spreadsheet, int $leftTopRow, int $leftTopColumn, int $rightBottomRow, int $rightBottomColumn): array
    {
        return $this->createQueryBuilder('t')
                    ->select('t.row,t.column,t.value')
                    ->where('t.table = :spreadsheet')
                    ->andWhere('t.row >= :left_top_row')
                    ->andWhere('t.row <= :right_bottom_row')
                    ->andWhere('t.column >= :left_top_column')
                    ->andWhere('t.column <= :right_bottom_column')
                    ->setParameters(
                        [
                            'spreadsheet'         => $spreadsheet,
                            'left_top_row'        => $leftTopRow,
                            'left_top_column'     => $leftTopColumn,
                            'right_bottom_row'    => $rightBottomRow,
                            'right_bottom_column' => $rightBottomColumn
                        ]
                    )
                    ->getQuery()->getResult();
    }

    /**
     * @param int $rowIndex
     * @param int $spreadsheet
     * @return Cell[]|[]
     */
    public function findByRow(int $spreadsheet, int $rowIndex): array
    {
        return $this->createQueryBuilder('t')
                    ->select('t.row,t.column,t.value')
                    ->where('t.table = :spreadsheet')
                    ->andWhere('t.row = :row_index')
                    ->setParameters(
                        [
                            'spreadsheet' => $spreadsheet,
                            'row_index'   => $rowIndex
                        ]
                    )
                    ->getQuery()->getResult();
    }

    /**
     * @param int $columnIndex
     * @param int $spreadsheet
     * @return Cell[]|[]
     */
    public function findByColumn(int $spreadsheet, int $columnIndex): array
    {
        return $this->createQueryBuilder('t')
                    ->select('t.row,t.column,t.value')
                    ->where('t.table = :spreadsheet')
                    ->andWhere('t.column = :column_index')
                    ->setParameters(
                        [
                            'spreadsheet'  => $spreadsheet,
                            'column_index' => $columnIndex
                        ]
                    )
                    ->getQuery()->getResult();
    }

    /**
     * @param int $rowIndex
     * @param int $spreadsheet
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @return Cell[]|[]
     */
    public function findSumByRow(int $spreadsheet, int $rowIndex): array
    {
        return $this->createQueryBuilder('t')
                    ->select('SUM(t.value) as sum')
                    ->where('t.table = :spreadsheet')
                    ->andWhere('t.row = :row_index')
                    ->setParameters(
                        [
                            'spreadsheet' => $spreadsheet,
                            'row_index'   => $rowIndex
                        ]
                    )
                    ->getQuery()
                    ->getSingleResult();
    }

    /**
     * @param int $columnIndex
     * @param int $spreadsheet
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @return Cell[]|[]
     */
    public function findSumByColumn(int $spreadsheet, int $columnIndex): array
    {
        return $this->createQueryBuilder('t')
                    ->select('SUM(t.value) as sum')
                    ->where('t.table = :spreadsheet')
                    ->andWhere('t.column = :column_index')
                    ->setParameters(
                        [
                            'spreadsheet'  => $spreadsheet,
                            'column_index' => $columnIndex
                        ]
                    )
                    ->getQuery()
                    ->getSingleResult();
    }

    /**
     * @param int $rowIndex
     * @param int $spreadsheet
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @return Cell[]|[]
     */
    public function findAvgByRow(int $spreadsheet, int $rowIndex): array
    {
        return $this->createQueryBuilder('t')
                    ->select('AVG(t.value) as avg')
                    ->where('t.table = :spreadsheet')
                    ->andWhere('t.row = :row_index')
                    ->setParameters(
                        [
                            'spreadsheet' => $spreadsheet,
                            'row_index'   => $rowIndex
                        ]
                    )
                    ->getQuery()
                    ->getSingleResult();
    }

    /**
     * @param int $columnIndex
     * @param int $spreadsheet
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @return Cell[]|[]
     */
    public function findAvgByColumn(int $spreadsheet, int $columnIndex): array
    {
        return $this->createQueryBuilder('t')
                    ->select('AVG(t.value) as avg')
                    ->where('t.table = :spreadsheet')
                    ->andWhere('t.column = :column_index')
                    ->setParameters(
                        [
                            'spreadsheet'  => $spreadsheet,
                            'column_index' => $columnIndex
                        ]
                    )
                    ->getQuery()
                    ->getSingleResult();
    }

    /**
     * @param int $rowIndex
     * @param int $spreadsheet
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @return Cell[]|[]
     */
    public function findCountByRow(int $spreadsheet, int $rowIndex): array
    {
        return $this->createQueryBuilder('t')
                    ->select('COUNT(t.value) as count')
                    ->where('t.table = :spreadsheet')
                    ->andWhere('t.row = :row_index')
                    ->setParameters(
                        [
                            'spreadsheet' => $spreadsheet,
                            'row_index'   => $rowIndex
                        ]
                    )
                    ->getQuery()
                    ->getSingleResult();
    }

    /**
     * @param int $columnIndex
     * @param int $spreadsheet
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @return Cell[]|[]
     */
    public function findCountByColumn(int $spreadsheet, int $columnIndex): array
    {
        return $this->createQueryBuilder('t')
                    ->select('COUNT(t.value) as count')
                    ->where('t.table = :spreadsheet')
                    ->andWhere('t.column = :column_index')
                    ->setParameters(
                        [
                            'spreadsheet'  => $spreadsheet,
                            'column_index' => $columnIndex
                        ]
                    )
                    ->getQuery()
                    ->getSingleResult();
    }

    /**
     * @param int $columnIndex
     * @param int $spreadsheet
     * @param int $offset
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @return Cell[]|[]
     */
    public function findPercentileByColumn(int $spreadsheet, int $columnIndex, int $offset): array
    {
        return $this->createQueryBuilder('t')
                    ->select('(t.value) as percentile')
                    ->where('t.table = :spreadsheet')
                    ->andWhere('t.column = :column_index')
                    ->setParameters(
                        [
                            'spreadsheet'  => $spreadsheet,
                            'column_index' => $columnIndex
                        ]
                    )
                    ->orderBy('t.value', 'ASC')
                    ->setMaxResults(1)
                    ->setFirstResult($offset)
                    ->getQuery()
                    ->getSingleResult();
    }

    /**
     * @param int $rowIndex
     * @param int $spreadsheet
     * @param int $offset
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @return Cell[]|[]
     */
    public function findPercentileByRow(int $spreadsheet, int $rowIndex, int $offset): array
    {
        return $this->createQueryBuilder('t')
                    ->select('(t.value) as percentile')
                    ->where('t.table = :spreadsheet')
                    ->andWhere('t.row = :row_index')
                    ->setParameters(
                        [
                            'spreadsheet' => $spreadsheet,
                            'row_index'   => $rowIndex
                        ]
                    )
                    ->orderBy('t.value', 'ASC')
                    ->setMaxResults(1)
                    ->setFirstResult($offset)
                    ->getQuery()
                    ->getSingleResult();
    }

    /**
     * @param int $rowIndex
     * @param int $spreadsheet
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @return Cell[]|[]
     */
    public function countOfValuesByRow(int $rowIndex, int $spreadsheet): array
    {
        return $this->createQueryBuilder('t')
                    ->select('COUNT(t.value) as percentile')
                    ->where('t.table = :spreadsheet')
                    ->andWhere('t.row = :row_index')
                    ->setParameters(
                        [
                            'spreadsheet' => $spreadsheet,
                            'row_index'   => $rowIndex
                        ]
                    )
                    ->getQuery()
                    ->getSingleResult();
    }
}
