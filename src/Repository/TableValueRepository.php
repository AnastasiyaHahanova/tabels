<?php

namespace App\Repository;

use App\Entity\TableValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TableValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method TableValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method TableValue[]    findAll()
 * @method TableValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TableValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TableValue::class);
    }

    /**
     * @param int $leftTopRow
     * @param int $leftTopColumn
     * @param int $rightBottomRow
     * @param int $rightBottomColumn
     * @return TableValue[]|[]
     */
    public function findByRange(int $tableId, int $leftTopRow, int $leftTopColumn, int $rightBottomRow, int $rightBottomColumn): array
    {
        return $this->createQueryBuilder('t')
                    ->select('t.row,t.column,t.value')
                    ->where('t.table = :table_id')
                    ->andWhere('t.row >= :left_top_row')
                    ->andWhere('t.row <= :right_bottom_row')
                    ->andWhere('t.column >= :left_top_column')
                    ->andWhere('t.column <= :right_bottom_column')
                    ->setParameters(
                        [
                            'table_id'            => $tableId,
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
     * @param int $tableId
     * @return TableValue[]|[]
     */
    public function findByRow(int $tableId, int $rowIndex): array
    {
        return $this->createQueryBuilder('t')
                    ->select('t.row,t.column,t.value')
                    ->where('t.table = :table_id')
                    ->andWhere('t.row = :row_index')
                    ->setParameters(
                        [
                            'table_id'  => $tableId,
                            'row_index' => $rowIndex
                        ]
                    )
                    ->getQuery()->getResult();
    }

    /**
     * @param int $columnIndex
     * @param int $tableId
     * @return TableValue[]|[]
     */
    public function findByColumn(int $tableId, int $columnIndex): array
    {
        return $this->createQueryBuilder('t')
                    ->select('t.row,t.column,t.value')
                    ->where('t.table = :table_id')
                    ->andWhere('t.column = :column_index')
                    ->setParameters(
                        [
                            'table_id'     => $tableId,
                            'column_index' => $columnIndex
                        ]
                    )
                    ->getQuery()->getResult();
    }

    /**
     * @param int $rowIndex
     * @param int $tableId
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @return TableValue[]|[]
     */
    public function findSumByRow(int $tableId, int $rowIndex): array
    {
        return $this->createQueryBuilder('t')
                    ->select('SUM(t.value) as sum')
                    ->where('t.table = :table_id')
                    ->andWhere('t.row = :row_index')
                    ->setParameters(
                        [
                            'table_id'  => $tableId,
                            'row_index' => $rowIndex
                        ]
                    )
                    ->getQuery()
                    ->getSingleResult();
    }

    /**
     * @param int $columnIndex
     * @param int $tableId
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @return TableValue[]|[]
     */
    public function findSumByColumn(int $tableId, int $columnIndex): array
    {
        return $this->createQueryBuilder('t')
                    ->select('SUM(t.value) as sum')
                    ->where('t.table = :table_id')
                    ->andWhere('t.column = :column_index')
                    ->setParameters(
                        [
                            'table_id'     => $tableId,
                            'column_index' => $columnIndex
                        ]
                    )
                    ->getQuery()
                    ->getSingleResult();
    }

    /**
     * @param int $rowIndex
     * @param int $tableId
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @return TableValue[]|[]
     */
    public function findAvgByRow(int $tableId, int $rowIndex): array
    {
        return $this->createQueryBuilder('t')
                    ->select('AVG(t.value) as sum')
                    ->where('t.table = :table_id')
                    ->andWhere('t.row = :row_index')
                    ->setParameters(
                        [
                            'table_id'  => $tableId,
                            'row_index' => $rowIndex
                        ]
                    )
                    ->getQuery()
                    ->getSingleResult();
    }

    /**
     * @param int $columnIndex
     * @param int $tableId
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @return TableValue[]|[]
     */
    public function findAvgByColumn(int $tableId, int $columnIndex): array
    {
        return $this->createQueryBuilder('t')
                    ->select('AVG(t.value) as sum')
                    ->where('t.table = :table_id')
                    ->andWhere('t.column = :column_index')
                    ->setParameters(
                        [
                            'table_id'     => $tableId,
                            'column_index' => $columnIndex
                        ]
                    )
                    ->getQuery()
                    ->getSingleResult();
    }

    public function calculatePercentile(int $percentile)
    {
        return $query = $this->_em->createQuery
        (sprintf('SELECT * FROM 
        (SELECT t.*,  @row_num :=@row_num + 1 AS row_num FROM App\Entity\TableValue t, (SELECT @row_num:=0) counter ORDER BY value) temp
        WHERE temp.row_num = ROUND (.%s* @row_num)', $percentile))->getOneOrNullResult();
    }
}
