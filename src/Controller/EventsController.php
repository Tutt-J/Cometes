<?php
namespace App\Controller;

use App\Entity\Event;
use App\Entity\Purchase;
use App\Entity\UserEvent;
use App\Form\EventPriceType;
use App\Service\BasketAdministrator;
use App\Service\EventsAdministrator;
use App\Service\MailjetAdministrator;
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
     * @Route("/evenements", name="homeEvent")
     * @return mixed
     */
    public function homeAction()
    {
        return $this->render('events/home.html.twig');
    }

    /**
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
     * @Route("/magie-en-ligne/cercles-de-lune/{slug}",
     * name="circleEvent",
     * requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     *
     * @param string $slug
     *
     * @param EventsAdministrator $eventsAdministrator
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function circleAction(string $slug, EventsAdministrator $eventsAdministrator)
    {
        return $eventsAdministrator->renderEventPage($slug);
    }

    /**
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
     * @param string $slug
     *
     * @param EventsAdministrator $eventsAdministrator
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function ritualAction(string $slug, EventsAdministrator $eventsAdministrator)
    {
        return $eventsAdministrator->renderEventPage($slug);
    }


    /**
     * @Route("/magie-en-ligne/ateliers", name="workshopsEvent")
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
     * @param string $slug
     *
     * @param EventsAdministrator $eventsAdministrator
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function workshopAction(string $slug, EventsAdministrator $eventsAdministrator)
    {
        return $eventsAdministrator->renderEventPage($slug);
    }

    /**
     * @Route("/evenements/retraites", name="retreatsEvent")
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
     * @param $slug
     *
     * @param EventsAdministrator $eventsAdministrator
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function retreatAction($slug, EventsAdministrator $eventsAdministrator)
    {
        return $eventsAdministrator->renderEventPage($slug, true, EventPriceType::class, 'EventPricing');
    }

    /**
     * @Route("/evenements/s-inscrire/{slug}", name="registerEvent")
     * @param $slug
     * @param MailjetAdministrator $mailjetAdministrator
     * @param EventsAdministrator $eventsAdministrator
     * @param StripeHelper $stripeHelper
     * @param SessionInterface $session
     * @return mixed
     */
    public function eventRegister(
        $slug,
        MailjetAdministrator $mailjetAdministrator,
        EventsAdministrator $eventsAdministrator,
        StripeHelper $stripeHelper,
        SessionInterface $session
    ) {
        $event=$eventsAdministrator->getEvent($slug);

        if (!$eventsAdministrator->canRegister($event)) {
            return $this->redirectToRoute($session->get('referent')['path'], ['slug'=>$session->get('referent')['slug']]);
        }
        $items=[
            [
                'name' => $event->getTitle().' du '.$event->getStartDate()->format('d/m/Y'),
                'amount' => $session->get('price')*100,
                'currency' => 'eur',
                'quantity' => 1,
            ]
        ];

        $stripeHelper->registerPayment($items, 'RegisterEvent');

        $session->set('event', $event->getId());

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
     * @param SessionInterface $session
     *
     * @param MailerInterface $mailer
     * @param StripeHelper $stripeHelper
     * @param BasketAdministrator $basketAdministrator
     * @return RedirectResponse|Response
     *
     * @throws TransportExceptionInterface
     */
    public function successRegisterAction(
        SessionInterface $session,
        MailerInterface $mailer,
        StripeHelper $stripeHelper,
        BasketAdministrator $basketAdministrator
    ) {
        $em = $this->getDoctrine()->getManager();

        if ($session->get('stripe')) {
            $charge= $stripeHelper->retrievePurchase('RegisterEvent');

            //SET PURCHASE
            $purchase=$stripeHelper->setPurchase($charge);
            $purchase->setContent($stripeHelper->retrievePaymentIntents($charge['payment_intent'], 'RegisterEvent')['metadata']['Description']);
            $em->persist($purchase);


            //SET USER EVENT
            $event = $this->getDoctrine()
                ->getRepository(Event::class)
                ->findOneBy(
                    ['id' => $session->get('event')]
                );
            $userEvent=new UserEvent();
            $userEvent->setUser($this->getUser());
            $userEvent->setEvent($event);
            $userEvent->setPurchase($purchase);
            $em->persist($userEvent);

            //FLUSH ALL
            $em->flush();

            //SEND CLIENT MAIL
            $invoice=$basketAdministrator->getInvoice($charge['display_items'], $purchase);
            $message = (new TemplatedEmail())
                ->from(new Address('postmaster@chamade.co', 'Chamade'))
                ->to($this->getUser()->getEmail())
                ->subject('Votre pré-inscription a bien été prise en compte')
                ->htmlTemplate('emails/event_confirm.html.twig')
                ->context([
                    'event' => $event,
                ])
                ->attachFromPath($invoice);
            $mailer->send($message);

            //SEND AMDIN MAIL
            $emailAdmin = (new Email())
                ->from(new Address('postmaster@chamade.co', 'SITE WEB Chamade'))
                ->to('hello@chamade.co')
                ->subject('Nouvel inscription à un évènement')
                ->html('
                    <p>Nom : '.$this->getUser()->getFirstName().' '.$this->getUser()->getLastName().'</p>
                    <p>Email : <a href="mailto:'.$this->getUser()->getEmail().'">'.$this->getUser()->getEmail().'</a></p>
                    <p>Évènement : '.$event->getTitle().'</p>
                ');
            $mailer->send($emailAdmin);

            //REMOVE SESSION VARS
            $session->remove('stripe');
            $session->remove('price');
            $session->remove('description');
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
     * @param SessionInterface $session
     * @return RedirectResponse
     */
    public function errorRegisterEventAction(SessionInterface $session)
    {
        $this->addFlash('error', 'Une erreur est survenue au moment du paiement... Veuillez réessayer ou nous contacter');
        return $this->redirectToRoute($session->get('referent')['path'], ['slug'=>$session->get('referent')['slug']]);
    }


    /**
     * @Route("/evenements/blessing-way", name="blessingEvent")
     * @return mixed
     */
    public function blessingAction()
    {
        return $this->render('events/blessing.html.twig');
    }


    /**
     * @Route("/evenements/slowbuilding", name="slowBuildingEvent")
     * @return mixed
     */
    public function slowBuildingAction()
    {
        return $this->render('events/slowbuilding.html.twig');
    }
}
