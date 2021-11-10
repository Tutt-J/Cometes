<?php
namespace App\Controller;

use App\Entity\Event;
use App\Entity\Purchase;
use App\Entity\UserEvent;
use App\Form\EventPriceType;
use App\Repository\EventRepository;
use App\Service\BasketAdministrator;
use App\Service\EventsAdministrator;
use App\Service\MailjetAdministrator;
use App\Service\ProcessPurchase;
use App\Service\SendMail;
use App\Service\StripeHelper;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
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
        $events = $this->getDoctrine()
            ->getRepository(Event::class)
            ->findBecomeEvents();
        return $this->render('events/home.html.twig', [
            "events" => $events
        ]);
    }



    /**
     *
     * One event circle page
     *
     * @Route("/evenements/{slug}",
     * name="singleEvent",
     * requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     *
     * @param Event $event
     * @param EventsAdministrator $eventsAdministrator
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function eventAction(Event $event, EventsAdministrator $eventsAdministrator)
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
                'tax_rates' => [$_ENV['STRIPE_TAX']],
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
        $session->remove('event');
        $session->remove('stripe');
        $session->remove('promoCode');
        $session->remove('applyPromo');
        $this->addFlash('error', 'Une erreur est survenue au moment du paiement... Veuillez réessayer ou nous contacter');
        return $this->redirectToRoute($session->get('referent')['path'], ['slug'=>$session->get('referent')['slug']]);
    }
}
