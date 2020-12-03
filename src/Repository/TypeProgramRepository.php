<?php

namespace App\Repository;

use App\Entity\TypeProgram;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TypeProgram|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeProgram|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeProgram[]    findAll()
 * @method TypeProgram[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeProgramRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeProgram::class);
    }
}
