<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    const IS_ONLINE ="a.isOnline = :online";

    /**
     * ArticleRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @param $current
     * @return mixed
     */
    public function findThreeByCategory($current)
    {
        return $this->createQueryBuilder('a')
            ->addSelect('RAND() as HIDDEN rand')
            ->andWhere('a.category = :val')
            ->andWhere('a.id != :id')
            ->andWhere(SELF::IS_ONLINE)
            ->setParameters(array('val'=> $current->getCategory(), 'id' => $current->getId(), 'online' => 1))
            ->orderBy('rand')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findPrev($current){
        return $this->createQueryBuilder('a')
            ->andWhere('a.id < :id')
            ->andWhere(SELF::IS_ONLINE)
            ->setParameters(array( 'id' => $current->getId(), 'online' => 1))
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findNext($current){
        return $this->createQueryBuilder('a')
            ->andWhere('a.id > :id')
            ->andWhere(SELF::IS_ONLINE)
            ->setParameters(array( 'id' => $current->getId(), 'online' => 1))
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param $keyword
     * @return mixed
     */
    public function findByKeyword($keyword)
    {
        return $this->createQueryBuilder('a')
            ->select('a')
            ->leftJoin('a.keywords', 'c')
            ->addSelect('c')
            ->andWhere('c.keyword = :c')
            ->andWhere(SELF::IS_ONLINE)
            ->setParameters(array('c' => $keyword->getKeyword(), 'online' => 1))
            ->getQuery()
            ->getResult();
    }

    public function findNews(){
        return $this->createQueryBuilder('a')
            ->select('a')
            ->where('a.category = :press or a.category= :podcast')
            ->andWhere("a.isOnline = :status")
            ->setParameters(array( 'press' => 15, 'podcast' => 14, "status" => 1))
            ->orderBy('a.updatedAt', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }
}
