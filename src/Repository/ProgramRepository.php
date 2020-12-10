<?php

namespace App\Repository;

use App\Entity\Program;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Program|null find($id, $lockMode = null, $lockVersion = null)
 * @method Program|null findOneBy(array $criteria, array $orderBy = null)
 * @method Program[]    findAll()
 * @method Program[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProgramRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Program::class);
    }

    /**
     * @param $keyword
     * @return mixed
     */
    public function findByType($type)
    {
        return $this->createQueryBuilder('a')
            ->select('a')
            ->leftJoin('a.type', 'c')
            ->addSelect('c')
            ->andWhere('c.slug = :c')
            ->andWhere("a.is_online = :online")
            ->setParameters(array('c' => $type, 'online' => 1))
            ->getQuery()
            ->getResult();
    }

}
