<?php

namespace App\Service\Admin;

use App\Entity\Event;
use App\Entity\Purchase;
use App\Entity\PurchaseContent;
use App\Entity\Content;
use App\Entity\UserEvent;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ReportGenerator
 * @package App\Service
 */
class ReportGenerator
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;


    /**
     * BasketAdministrator constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getPurchases()
    {
        $purchases= $this->em
           ->getRepository(Purchase::class)
           ->findBy(
               array(),
               array('updatedAt' => 'DESC')
           );

        $purchasesArray=[];

        foreach ($purchases as $purchase) {
            $arrayContent=[];

            //Event
            $userEvents=$this->em
               ->getRepository(UserEvent::class)
               ->findOneBy([
                   'purchase' => $purchase
               ]);

            if (!is_null($userEvents)) {
                array_push($arrayContent, $userEvents->getEvent()->getTitle());
            }

            $purchaseContents = $this->em
               ->getRepository(PurchaseContent::class)
               ->findBy(
                   ['purchase' => $purchase]
               );
            foreach ($purchaseContents as $purchaseContent) {
                $product = $this->em
                   ->getRepository(Content::class)
                   ->findOneBy(
                       ['id' => $purchaseContent->getContent()]
                   );

                array_push($arrayContent, $product->getTitle());
            }
            $array=[
               'purchase' => $purchase,
               'contents' => $arrayContent
           ];
            array_push($purchasesArray, $array);
        }

        return $purchasesArray;
    }

    public function getTotalDay()
    {
        return $this->em
            ->getRepository(Purchase::class)
            ->findByDay();
    }

    public function getTotalWeek()
    {
        return $this->em
            ->getRepository(Purchase::class)
            ->findByWeek();
    }

    public function getTotalMonth()
    {
        return $this->em
            ->getRepository(Purchase::class)
            ->findByMonth(date('m'));
    }

    public function getTotalYear()
    {
        return $this->em
            ->getRepository(Purchase::class)
            ->findByYear();
    }

    public function getNbEvents()
    {
        return $this->em
            ->getRepository(UserEvent::class)
            ->findAllNotAdmin();
    }

    public function getNbContents()
    {
        return $this->em
            ->getRepository(PurchaseContent::class)
            ->findAllNotAdmin();
    }

    public function getThreeNextEvents()
    {
        return $this->em
            ->getRepository(Event::class)
            ->findThreeBecomeEvent();
    }
}
