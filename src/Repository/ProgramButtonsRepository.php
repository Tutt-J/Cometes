<?php

namespace App\Repository;

use App\Entity\ProgramButtons;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProgramButtons|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProgramButtons|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProgramButtons[]    findAll()
 * @method ProgramButtons[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProgramButtonsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProgramButtons::class);
    }
}
