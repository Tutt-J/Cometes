<?php

namespace App\Repository;

use App\Entity\Purchase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Purchase|null find($id, $lockMode = null, $lockVersion = null)
 * @method Purchase|null findOneBy(array $criteria, array $orderBy = null)
 * @method Purchase[]    findAll()
 * @method Purchase[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PurchaseRepository extends ServiceEntityRepository
{
    const WHERE_YEAR ="YEAR(a.createdAt) = :year";
    const SELECT_SUM ="SUM(a.amount) as amount";

    /**
     * PurchaseRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Purchase::class);
    }

    /**
     * @return mixed
     */
    public function findByDay()
    {
        return $this->createQueryBuilder('a')
            ->select(self::SELECT_SUM)
            ->where(self::WHERE_YEAR)
            ->andWhere('MONTH(a.createdAt) = :month')
            ->andWhere('DAY(a.createdAt) = :day')
            ->setParameters(
                array(
                    'year' => date("Y"),
                    'month' =>date("m"),
                    'day' =>date("d")
                )
            )
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return mixed
     */
    public function findByWeek()
    {
        $start_week = date("Y-m-d", strtotime('monday this week'));
        $end_week = date("Y-m-d", strtotime('sunday this week'));

        return $this->createQueryBuilder('a')
            ->select(self::SELECT_SUM)
            ->where('a.createdAt >= :start')
            ->andWhere('a.createdAt <= :end')
            ->setParameter('start', $start_week)
            ->setParameter('end', $end_week)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return mixed
     */
    public function findByMonth($month)
    {
        return $this->createQueryBuilder('a')
            ->select(self::SELECT_SUM)
            ->where(self::WHERE_YEAR)
            ->andWhere('MONTH(a.createdAt) = :month')
            ->setParameters(
                array(
                    'month' =>$month,
                    'year' => date("Y")
                )
            )
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return mixed
     */
    public function findByYear()
    {
        return $this->createQueryBuilder('a')
            ->select(self::SELECT_SUM)
            ->where(self::WHERE_YEAR)
            ->setParameters(
                array(
                    'year' => date("Y"),
                )
            )
            ->getQuery()
            ->getResult()
            ;
    }
}
