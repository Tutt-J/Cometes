<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Type;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;;
use Exception;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    /**
     * EventRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @param $type
     * @return mixed
     * @throws Exception
     */
    public function findBecomeEvents()
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.startDate > :now')
            ->andWhere('r.isOnline = :online')
            ->setParameter('now', new DateTime())
            ->setParameter('online', 1)
            ->orderBy('r.startDate', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function findThreeBecomeEvent()
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.startDate > :now')
            ->andWhere('r.isOnline = :online')
            ->setParameter('now', new DateTime())
            ->setParameter('online', 1)
            ->orderBy('r.startDate', 'ASC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult()
            ;
    }
}
