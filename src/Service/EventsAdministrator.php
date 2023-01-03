<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\EventPricing;
use App\Entity\Type;
use App\Entity\UserEvent;
use App\Form\EventType;
use App\Form\EventPriceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class EventsAdministrator
 * @package App\Service
 */
class EventsAdministrator
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

    /**
     *
     */
    const DATE_FORMAT='Y-m-d H:i';
    /**
     * @var ProcessPurchase
     */
    private ProcessPurchase $processPurchase;
    /**
     * @var PromoCodeAdministrator
     */
    private PromoCodeAdministrator $promoCode;


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
        FlashBagInterface $flashbag,
        ProcessPurchase $processPurchase,
        PromoCodeAdministrator $promoCode
    ) {
        $this->em = $em;
        $this->session = $session;
        $this->security=$security;
        $this->router= $router;
        $this->requestStack = $requestStack;
        $this->formFactory=$formFactory;
        $this->twig = $twig;
        $this->flashbag=$flashbag;
        $this->processPurchase= $processPurchase;
        $this->promoCode= $promoCode;
    }

    /**
     * @return mixed
     */
    public function getEvents()
    {
        return $this->em
            ->getRepository(Event::class)
            ->findThreeBecomeEvent();
    }

    /**
     *
     * Check if already regsiter to event
     *
     * @param $event
     * @return bool
     */
    public function checkAlreadyRegister($event)
    {
        //Check if user already register
        $exist=$this->em
            ->getRepository(UserEvent::class)
            ->findBy(
                [
                    'user' =>  $this->security->getUser(),
                    'event' => $event
                ]
            );

        if ($exist) {
            return true;
        }
        return false;
    }


    /**
     *
     * Render page for all events types
     *
     * @param $event
     * @return RedirectResponse|Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderEventPage($event)
    {
        //If event not exist or not online
        if(!$event || !$event->getIsOnline()){
            throw new NotFoundHttpException('L\'évènement "'.$event->getTitle().'" n\'est pas ou plus disponible.');
        }

        //Set reference page
        $this->session->set('referent', [
            'path'=> 'singleEvent',
            'slug'=> $event->getSlug()
        ]);

        //If there is no error
        if ($this->generateMessageError($event) === true) {
            //If there is multiple pricing, create Form with multiple price, else create simple form
            if (!$event->getEventPricings()->isEmpty()) {
                $form = $this->formFactory->create(
                    EventPriceType::class,
                    new EventPricing(),
                    array('event' => $event)
                );
                $multiplePrice=true;
            } else {
                $form = $this->formFactory->create(
                    EventType::class,
                    $event,
                    array('event' => $event)
                );
                $multiplePrice=false;
            }

            $form->handleRequest($this->requestStack->getCurrentRequest());

            //To be sure for legals reasons recheck legals
            if ($form->isSubmitted()
                && $form->isValid()
                && true === $form['agreeTerms']->getData()
                && true === $form['agreeCgv']->getData()) {
                return $this->submitForm($multiplePrice, $form, $event, $form['agreeNewsletter']->getData());
            }
            $formView=$form->createView();
            $errorMessage=null;
        } else {
            $formView=null;
            $errorMessage=$this->generateMessageError($event);
        }

        $response = new Response();

        $response->setContent($this->twig->render(
            'events/event.html.twig',
            [
                'event' => $event,
                'form' => $formView,
                'maxMessage' => $errorMessage
            ]
        ));

        return $response;
    }

    /**
     *
     * Generate error message
     *
     * @param $event
     * @return string
     */
    public function generateMessageError($event)
    {
        $message=true;
        if ($this->checkAlreadyRegister($event)) {
            $message="Vous êtes déjà inscrit à cet évènement";
        }
        if (!$this->checkNbMaxParticipant($event)) {
            $message="Le nombre maximum de participantes pour cet évènement est atteint.";
        }
        if (!$this->checkEventPassed($event)) {
            $message="Cet évènement est passé ou a lieu dans moins de 24 heures. Vous ne pouvez plus vous y inscrire.";
        }
        return $message;
    }

    /**
     * Check if there is place
     *
     * @param $event
     * @return bool
     */
    public function checkNbMaxParticipant($event)
    {
        $nbRegister = $this->em
            ->getRepository(UserEvent::class)
            ->findBy(
                ['event' => $event]
            );
        if (sizeof($nbRegister)<$event->getNbMaxParticipant()) {
            return true;
        }
        return false;
    }

    /**
     *
     * Chec if event is to become
     *
     * @param $event
     * @return bool
     */
    public function checkEventPassed($event)
    {
        $date = date(SELF::DATE_FORMAT);
        if (($event->getStartDate())->format(SELF::DATE_FORMAT) > date(SELF::DATE_FORMAT, strtotime($date. ' + 1 days'))) {
            return true;
        }
        return false;
    }

    /**
     * @param $friend
     * @param $already
     * @param $paiennes
     * @param $choice
     */
    public function generateDescription($friend, $already, $paiennes, $choice)
    {
        $description = '';
        if ($already == 1) {
            $description.="Réduction de 5% car a déjà participé à une retraite chamade. ";
        }
        if (!empty($friend)) {
            $description.="Réduction de 5% car vient avec " . $friend.'. ';
        }
        if($paiennes == 1) {
            $description.="Réduction de 5% car fait partie de la communauté des paiennes.";
        }

        if(!empty($choice)){
            $description.=" ".$choice;
        }

        if(isset($description)){
            $this->session->set("description", $this->session->get('description').$description);
        }

    }

    public function canRegister($event)
    {
        $isTrue=true;
        if ($this->checkAlreadyRegister($event)) {
            $this->flashbag->add('error', 'Vous êtes déjà inscrit à cet évènement.');
            $isTrue=false;
        }
        if (!$this->checkNbMaxParticipant($event)) {
            $this->flashbag->add('error', 'Le nombre maximum de participantes pour cet évènement est atteint.');
            $isTrue=false;
        }
        if (!$this->checkEventPassed($event)) {
            $this->flashbag->add('error', 'Cet évènement est passé ou a lieu dans moins de 24 heures. Vous ne pouvez plus vous y inscrire.');
            $isTrue=false;
        }
        return $isTrue;
    }

    public function getType($slug)
    {
        return $this->em
            ->getRepository(Type::class)
            ->findBy(
                array(
                    'slug' => $slug,
                ),
                array('id' => 'DESC')
            );
    }

    /**
     * Submit Event Form
     *
     * @param bool $mutiplePrice
     * @param FormInterface $form
     * @param object|null $event
     * @param $subscribeNewsletter
     * @return RedirectResponse
     */
    public function submitForm(bool $mutiplePrice, FormInterface $form, ?object $event, $subscribeNewsletter): RedirectResponse
    {
        dd($subscribeNewsletter);
        //If there is multiple price, choose form price, else choose event price
        if ($mutiplePrice === true) {
            $price = $form->get('choice')->getData()->getPrice();
        } else {
            $price=$event->getPrice();
        }


        //Set empty description
        $this->session->set('description', "");

        $friend='';
        if($form->has('friend') && !empty($form->get('friend')->getData())){
            $friend=$form->get('friend')->getData();
            $newPrice=$price - ($price * (5 / 100));
        }
        $already=0;
        if($form->has('already') && $form->get('already')->getData() == 1){
            $already=$form->get('already')->getData();
            $newPrice=$price - ($price * (5 / 100));
        }

        $paiennes=0;
        if($form->has('paiennes') && $form->get('paiennes')->getData() == 1){
            $paiennes=$form->get('paiennes')->getData();
            $newPrice=$price - ($price * (5 / 100));
        }
        $choice = '';
        if($form->has('choice')){
            $choice=$form->get('choice')->getData();
        }
        $this->generateDescription($friend, $already, $paiennes, $choice);

        if(isset($newPrice)){
            $price = $newPrice;
        }

        //If we have a promo code
        if(null != $form->get("promoCode")->getData()){
            //Check validity of code, if not valid return to event page
            if(!$this->promoCode->verifyPromoCode($form->get("promoCode")->getData())){
                return new RedirectResponse($this->router->generate($this->session->get('referent')['path'], ['slug' => $this->session->get('referent')['slug']]));
            }

            //Set new price
            $priceWithCode=$price-$this->session->get('promoCode')->getRestAmount();
            //If $price <0 set promo to price value else set promo to Promo Code Rest Amount Value
            if( $priceWithCode <0){
                $this->session->set('applyPromo', $price);
            } else{
                $this->session->set('applyPromo', $this->session->get('promoCode')->getRestAmount());
            }
            $this->session->set("description",$this->session->get('description')." Réduction de ".$this->session->get('applyPromo')."€ avec la carte cadeau numéro ".$this->session->get('promoCode')->getCode().".");
        }

        //Set event price
        $this->session->set('price', $price);

        return new RedirectResponse($this->router->generate('registerEvent', ['slug' => $event->getSlug()]));
    }
}
