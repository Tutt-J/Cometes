<?php

namespace App\Repository;

use App\Entity\EventPricing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EventPricing|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventPricing|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventPricing[]    findAll()
 * @method EventPricing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventPricingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventPricing::class);
    }
}
