<?php

namespace App\Repository;

use App\Entity\Content;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;;

/**
 * @method Content|null find($id, $lockMode = null, $lockVersion = null)
 * @method Content|null findOneBy(array $criteria, array $orderBy = null)
 * @method Content[]    findAll()
 * @method Content[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContentRepository extends ServiceEntityRepository
{
    /**
     * ContentRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Content::class);
    }

    public function findToBecome($type, $isPack)
    {
        return $this->createQueryBuilder('a')
            ->select('a')
            ->where('a.type = :type')
            ->andWhere('a.isPack = :isPack')
            ->andWhere('a.eventDate >= :now or a.neverPassed = true')
            ->andWhere('a.isOnline = :online')
            ->setParameters(array('type'=> $type,'online' => 1, 'isPack' => $isPack, 'now' => date("Y-m-d")))
            ->orderBy('a.eventDate', 'asc')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findOne($slug)
    {
        $query=$this->createQueryBuilder('a')
            ->select('a')
            ->where('a.slug = :slug')
            ->andWhere('a.eventDate >= :now or a.neverPassed = true')
            ->andWhere('a.isOnline = :online')
            ->setMaxResults(1)
            ->setParameters(array('slug'=> $slug,'online' => 1, 'now' => date("Y-m-d")))
            ->getQuery()
            ->getResult()
            ;

        if (!empty($query)) {
            return $query[0];
        }
    }
}
