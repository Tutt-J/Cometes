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
    const USER_ROLE_REQUEST= "u.roles NOT LIKE :role";
    const USER_ROLE_BIND = '%"'.'ROLE_ADMIN'.'"%';
    const STATUS_REQUEST = 'a.status != :status';
    const STATUS_BIND = "Remboursé";


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
            ->innerJoin('a.user', 'u')
            ->where(self::WHERE_YEAR)
            ->andWhere(self::USER_ROLE_REQUEST)
            ->andWhere('MONTH(a.createdAt) = :month')
            ->andWhere('DAY(a.createdAt) = :day')
            ->andWhere(self::STATUS_REQUEST)
            ->setParameters(
                array(
                    'year' => date("Y"),
                    'month' =>date("m"),
                    'day' =>date("d"),
                    'role' => self::USER_ROLE_BIND,
                    'status' => self::STATUS_BIND
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
            ->innerJoin('a.user', 'u')
            ->where('a.createdAt >= :start')
            ->andWhere('a.createdAt <= :end')
            ->andWhere(self::USER_ROLE_REQUEST)
            ->andWhere(self::STATUS_REQUEST)
            ->setParameters(
                array(
                    'start' => $start_week,
                    'end' =>$end_week,
                    'role' => self::USER_ROLE_BIND,
                    'status' => self::STATUS_BIND
                )
            )
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
            ->innerJoin('a.user', 'u')
            ->where(self::WHERE_YEAR)
            ->andWhere('MONTH(a.createdAt) = :month')
            ->andWhere(self::USER_ROLE_REQUEST)
            ->andWhere(self::STATUS_REQUEST)
            ->setParameters(
                array(
                    'month' =>$month,
                    'year' => date("Y"),
                    'role' => self::USER_ROLE_BIND,
                    'status' => self::STATUS_BIND
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
            ->innerJoin('a.user', 'u')
            ->where(self::WHERE_YEAR)
            ->andWhere(self::USER_ROLE_REQUEST)
            ->andWhere(self::STATUS_REQUEST)
            ->setParameters(
                array(
                    'year' => date("Y"),
                    'role' => self::USER_ROLE_BIND,
                    'status' => self::STATUS_BIND
                )
            )
            ->getQuery()
            ->getResult()
            ;
    }
}
