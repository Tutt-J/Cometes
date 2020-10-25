<?php

namespace App\Service\Admin;

use App\Entity\Event;
use App\Entity\EventPricing;
use App\Entity\Purchase;
use App\Entity\PurchaseContent;
use App\Entity\Content;
use App\Entity\UserEvent;
use App\Form\Admin\OfferContentType;
use App\Form\EventPriceType;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;

/**
 * Class OfferHelper
 * @package App\Service
 */
class OfferHelper
{
    /**
     * @var array
     */
    private array $events=[];

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var SessionInterface
     */
    private SessionInterface $session;

    /**
     * @var Security
     */
    private Security $security;

    /**
     * @var RequestStack
     */
    protected RequestStack $requestStack;

    /**
     * @var UrlGeneratorInterface
     */
    protected UrlGeneratorInterface $router;

    /**
     * @var FormFactoryInterface
     */
    protected FormFactoryInterface $formFactory;
    /**
     * @var Environment
     */
    protected Environment $twig;
    /**
     * @var FlashBagInterface
     */
    private FlashBagInterface $flashbag;

    const DATE_FORMAT='Y-m-d H:i';


    /**
     * BasketAdministrator constructor.
     * @param UrlGeneratorInterface $router
     * @param EntityManagerInterface $em
     * @param FormFactoryInterface $formFactory
     * @param RequestStack $requestStack
     * @param SessionInterface $session
     * @param Security $security
     * @param Environment $twig
     * @param FlashBagInterface $flashbag
     */
    public function __construct(
        UrlGeneratorInterface $router,
        EntityManagerInterface $em,
        FormFactoryInterface $formFactory,
        RequestStack $requestStack,
        SessionInterface $session,
        Security $security,
        Environment $twig,
        FlashBagInterface $flashbag
    ) {
        $this->em = $em;
        $this->session = $session;
        $this->security=$security;
        $this->router= $router;
        $this->requestStack = $requestStack;
        $this->formFactory=$formFactory;
        $this->twig = $twig;
        $this->flashbag=$flashbag;
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

    public function setItem($title){
        return [
            [
                "custom" => [
                    "name" => $title
                ],
                "quantity" => 1,
                "amount" => 0,
            ]
        ];
    }

}
