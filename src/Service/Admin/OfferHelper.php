<?php

namespace App\Service\Admin;

use App\Entity\Purchase;
use App\Form\Admin\OfferContentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class OfferHelper
 * @package App\Service
 */
class OfferHelper
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var RequestStack
     */
    protected RequestStack $requestStack;

    /**
     * @var FormFactoryInterface
     */
    protected FormFactoryInterface $formFactory;

    const DATE_FORMAT='Y-m-d H:i';


    /**
     * BasketAdministrator constructor.
     * @param EntityManagerInterface $em
     * @param FormFactoryInterface $formFactory
     * @param RequestStack $requestStack
     */
    public function __construct(
        EntityManagerInterface $em,
        FormFactoryInterface $formFactory,
        RequestStack $requestStack
    ) {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->formFactory=$formFactory;
    }

    public function createForm(){
        $form = $this->formFactory->create(
            OfferContentType::class
        );
        $form->handleRequest($this->requestStack->getCurrentRequest());
        return $form;
    }

    /**
     * @param FormInterface $form
     * @return Purchase
     */
    public function setPurchase(FormInterface $form): Purchase
    {
        $purchase = new Purchase();
        $purchase->setStripeId("Offert donc pas de stripe");
        $purchase->setStatus("Offert");
        $purchase->setAmount(0);
        $purchase->setUser($form->get('user')->getData());
        $purchase->setContent($form->get('content')->getData());
        return $purchase;
    }

    public function persistAndFlush($purchase, $entity){
        $this->em->persist($entity);
        $this->em->persist($purchase);
        $this->em->flush();
    }

    public function setItem($content){
        $content->setPrice(0);
        return [
            [
                'Entity' => $content,
                'isFidelity' => false
            ]
        ];
    }

}
