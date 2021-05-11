<?php

namespace App\Repository;

use App\Entity\ProgramCertified;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProgramCertified|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProgramCertified|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProgramCertified[]    findAll()
 * @method ProgramCertified[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProgramCertifiedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProgramCertified::class);
    }
}
