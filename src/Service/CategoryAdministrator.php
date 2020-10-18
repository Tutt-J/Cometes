<?php

namespace App\Service;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;

/**
 * Class CategoryAdministrator
 * @package App\Service
 */
class CategoryAdministrator
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * CategoryAdministrator constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return object[]
     */
    public function getCategory()
    {
        return $this->em
            ->getRepository(Category::class)
            ->findAll();
    }


    /**
     * @param string $entity
     * @return object[]
     */
    public function getNotEmptyCategory(string $entity)
    {
        $elements = $this->getCategory();

        foreach ($elements as $key => $element) {
            $children= $this->em
                ->getRepository($entity)
                ->findBy(
                    ['Category' => $element]
                );

            if (empty($children)) {
                unset($elements[$key]);
            }
        }

        return $elements;
    }
}
