<?php

namespace App\Repository;

use App\Entity\TableValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}
