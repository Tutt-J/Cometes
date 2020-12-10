<?php
namespace App\Controller;

use App\Entity\Event;
use App\Entity\Purchase;
use App\Entity\UserEvent;
use App\Form\EventPriceType;
use App\Service\BasketAdministrator;
use App\Service\EventsAdministrator;
use App\Service\MailjetAdministrator;
use App\Service\ProcessPurchase;
use App\Service\SendMail;
use App\Service\StripeHelper;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class EventsController
 * @package App\Controller
 */
class EventsController extends AbstractController
{

    /**
     * View Event Index page
     *
     * @Route("/evenements", name="homeEvent")
     * @return mixed
     */
    public function homeAction()
    {
        return $this->render('events/home.html.twig');
    }

    /**
     *
     * Circles Events page
     *
     * @Route("/magie-en-ligne/cercles-de-lune", name="circlesEvent")
     * @param EventsAdministrator $eventsAdministrator
     * @return mixed
     */
    public function circlesAction(EventsAdministrator $eventsAdministrator)
    {
        $circles = $this->getDoctrine()
            ->getRepository(Event::class)
            ->findBecomeEvents($eventsAdministrator->getType('circle'));

        return $this->render(
            'events/circles.html.twig',
            [
                'circles' => $circles,
            ]
        );
    }

    /**
     *
     * One event circle page
     *
     * @Route("/magie-en-ligne/cercles-de-lune/{slug}",
     * name="circleEvent",
     * requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     *
     * @param Event $event
     * @param EventsAdministrator $eventsAdministrator
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function circleAction(Event $event, EventsAdministrator $eventsAdministrator)
    {
        return $eventsAdministrator->renderEventPage($event);
    }

    /**
     *
     * All Rituals Event Page
     *
     * @Route("/evenements/rituels", name="ritualsEvent")
     *
     * @param EventsAdministrator $eventsAdministrator
     * @return Response
     */
    public function ritualsAction(EventsAdministrator $eventsAdministrator)
    {
        $rituals = $this->getDoctrine()
            ->getRepository(Event::class)
            ->findBecomeEvents($eventsAdministrator->getType('ritual'));

        return $this->render(
            'events/rituals.html.twig',
            [
                'rituals' => $rituals,
            ]
        );
    }


    /**
     * @Route("/evenements/rituels/{slug}",
     * name="ritualEvent",
     * requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     *
     * One ritual event page
     *
     * @param Event $event
     * @param EventsAdministrator $eventsAdministrator
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function ritualAction(Event $event, EventsAdministrator $eventsAdministrator)
    {
        return $eventsAdministrator->renderEventPage($event);
    }


    /**
     * @Route("/magie-en-ligne/ateliers", name="workshopsEvent")
     *
     * All Workshop Event Page
     *
     * @param EventsAdministrator $eventsAdministrator
     * @return Response
     */
    public function workshopsAction(EventsAdministrator $eventsAdministrator)
    {
        $workshops = $this->getDoctrine()
            ->getRepository(Event::class)
            ->findBecomeEvents($eventsAdministrator->getType('workshop'));

        return $this->render(
            'events/workshop.html.twig',
            [
                'workshops' =>$workshops,
            ]
        );
    }

    /**
     * @Route("/magie-en-ligne/ateliers/{slug}",
     * name="workshopEvent",
     * requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     *
     * One workshop event page
     *
     * @param Event $event
     * @param EventsAdministrator $eventsAdministrator
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function workshopAction(Event $event, EventsAdministrator $eventsAdministrator)
    {
        return $eventsAdministrator->renderEventPage($event);
    }

    /**
     * @Route("/evenements/retraites", name="retreatsEvent")
     *
     * All retreats Event Page
     *
     * @param EventsAdministrator $eventsAdministrator
     * @return Response
     */
    public function retreatsAction(EventsAdministrator $eventsAdministrator)
    {
        $retreats = $this->getDoctrine()
            ->getRepository(Event::class)
            ->findBecomeEvents($eventsAdministrator->getType('retreat'));

        return $this->render(
            'events/retreats.html.twig',
            [
                'retreats' => $retreats,
            ]
        );
    }


    /**
     * @Route("/evenements/retraites/{slug}",
     * name="retreatEvent",
     * requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     *
     * One retreat Event Page
     *
     * @param Event $event
     * @param EventsAdministrator $eventsAdministrator
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function retreatAction(Event $event, EventsAdministrator $eventsAdministrator)
    {
        return $eventsAdministrator->renderEventPage($event);
    }

    /**
     * @Route("/evenements/s-inscrire/{slug}", name="registerEvent")
     *
     * Register to an event
     *
     * @param Event $event
     * @param MailjetAdministrator $mailjetAdministrator
     * @param EventsAdministrator $eventsAdministrator
     * @param StripeHelper $stripeHelper
     * @param SessionInterface $session
     * @return mixed
     */
    public function eventRegister(
        Event $event,
        MailjetAdministrator $mailjetAdministrator,
        EventsAdministrator $eventsAdministrator,
        StripeHelper $stripeHelper,
        SessionInterface $session
    ) {
        //If event is offline
        if(!$event->getIsOnline()){
            throw new NotFoundHttpException('L\'évènement "'.$event->getTitle().'" n\'est pas ou plus disponible.');
        }

        //If we can't register to the event, redirect to event page
        if (!$eventsAdministrator->canRegister($event)) {
            return $this->redirectToRoute($session->get('referent')['path'], ['slug'=>$session->get('referent')['slug']]);
        }

        //Put event in session
        $session->set('event', $event);

        //If we have a promo code
        if($session->get('applyPromo')) {
            $total=$session->get('price')-$session->get('applyPromo');
            //>if total is 0, then no payment directly success
            if($total<=0){
                return $this->redirectToRoute('successRegisterEvent');
            }
        }

        //Create items format array for Stripe
        $items=[
            [
                'name' => $event->getTitle().' du '.$event->getStartDate()->format('d/m/Y'),
                'amount' => $session->get('price')*100,
                'currency' => 'eur',
                'quantity' => 1,
            ]
        ];

        //Register stripe Payment
        $stripeHelper->registerPayment($items, 'RegisterEvent');

        //Add contact to Mailjet Event List
        $mailjetAdministrator->addContact($this->getUser()->getEmail(), substr($event->getTitle(), 0, 45).' '.($event->getStartDate())->format('Y'));

        return $this->render(
            'basket/payment.html.twig',
            [
                'stripe_id' => $session->get('stripe')['id'],
                'stripe_pk' => $_ENV['STRIPE_PUBLIC']
            ]
        );
    }

    /**
     * @Route("/mon-compte/confirmation-d-inscription", name="successRegisterEvent")
     *
     * Success Register page
     *
     * @param ProcessPurchase $processPurchase
     * @param SessionInterface $session
     *
     * @return RedirectResponse|Response
     */
    public function successRegisterAction(
        ProcessPurchase $processPurchase,
        SessionInterface $session
    ) {
        //If we have an event, view confirmation else view purchases user page
        if($session->get('event')){
            $event=$this->getDoctrine()
                ->getRepository(Event::class)
                ->findOneBy(
                    [
                        'id' => $session->get('event')->getId()
                    ]
                );
            //Process to purchase
            $processPurchase->processEventPurchase();

            //Remove some session
            $session->remove('stripe');
            $session->remove('promoCode');
            $session->remove('applyPromo');
            $session->remove('description');
            $session->remove('event');
        } else {
            return $this->redirectToRoute('user_purchases');
        }
        return $this->render('events/confirm_register.html.twig', [
            'event' => $event
        ]);
    }


    /**
     * @Route("/mon-compte/retour-inscription", name="errorRegisterEvent")
     *
     * Return page is Stripe Error
     *
     * @param SessionInterface $session
     * @return RedirectResponse
     */
    public function errorRegisterEventAction(SessionInterface $session)
    {
        $this->addFlash('error', 'Une erreur est survenue au moment du paiement... Veuillez réessayer ou nous contacter');
        return $this->redirectToRoute($session->get('referent')['path'], ['slug'=>$session->get('referent')['slug']]);
    }


    /**
     *
     * Blessing way Events
     *
     * @Route("/evenements/blessing-way", name="blessingEvent")
     * @return mixed
     */
    public function blessingAction()
    {
        return $this->render('events/blessing.html.twig');
    }


    /**
     *
     * SlowBuilding Events
     *
     * @Route("/evenements/slowbuilding", name="slowBuildingEvent")
     * @return mixed
     */
    public function slowBuildingAction()
    {
        return $this->render('events/slowbuilding.html.twig');
    }
}
