<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use function get_class;


/**
 * @method UserEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserEvent[]    findAll()
 * @method UserEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserEventRepository extends ServiceEntityRepository
{
    /**
     * UserRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserEvent::class);
    }

    /**
     * @return mixed
     */
    public function findAllNotAdmin()
    {
        return $this->createQueryBuilder('a')
            ->select('a')
            ->innerJoin('a.user', 'u')
            ->where("u.roles NOT LIKE :role")
            ->setParameters(
                array(
                    'role' =>  '%"'.'ROLE_ADMIN'.'"%'
                )
            )
            ->getQuery()
            ->getResult()
            ;
    }
}
