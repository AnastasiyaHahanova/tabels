<?php

namespace App\Repository;

use App\Entity\Spreadsheet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Spreadsheet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Spreadsheet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Spreadsheet[]    findAll()
 * @method Spreadsheet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpreadsheetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Spreadsheet::class);
    }

    public function findOneByName(string $name): ?Spreadsheet
    {
        return $this->findOneBy(['name' => $name]);
    }
}
