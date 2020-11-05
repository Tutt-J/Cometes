<?php
namespace App\Controller\Admin;

use App\Entity\Content;
use App\Entity\Event;
use App\Entity\Purchase;
use App\Entity\PurchaseContent;
use App\Entity\UserEvent;
use App\Form\Admin\EventType;
use App\Form\Admin\OfferContentType;
use App\Service\Admin\AdminDatabase;
use App\Service\Admin\OfferHelper;
use App\Service\BasketAdministrator;
use App\Service\StripeHelper;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class EventsController
 * @package App\Controller
 *
 * @IsGranted("ROLE_ADMIN")
*/
class EventsController extends AbstractController
{

    /**
     * @Route("/admin/evenements", name="eventsAdmin")
     *
     * @return Response
     */
    public function eventsAction()
    {
        $articles = $this->getDoctrine()
            ->getRepository(Event::class)
            ->findBy(
                array(),
                array('updatedAt' => 'DESC')
            );

        return $this->render(
            'admin/events/events.html.twig',
            [
                'articles' => $articles,
            ]
        );
    }

    /**
     * @Route("/admin/evenements/liste/{slug}", name="eventsListAdmin")
     *
     * @param $slug
     * @return Response
     */
    public function listEventAction($slug)
    {
        $event = $this->getDoctrine()
            ->getRepository(Event::class)
            ->findOneBy(
                array('slug' => $slug),
            );

        return $this->render(
            'admin/events/event_list.html.twig',
            [
                'articles' => $event,
            ]
        );
    }


    /**
     * @Route("/admin/evenements/creer", name="createEventAdmin")
     *
     * @param Request $request
     * @param AdminDatabase $adminDatabase
     * @return Response
     */
    public function createEvent(Request $request, AdminDatabase $adminDatabase)
    {
        $article = new Event();

        $form = $this->createForm(EventType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminDatabase->event($form);

            return $this->redirectToRoute('eventsAdmin');
        }


        return $this->render('admin/events/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/evenements/{id}/modifier", name="updateEventAdmin")
     *
     * @param $id
     * @param Request $request
     * @param AdminDatabase $adminDatabase
     * @return Response
     */
    public function updateEvent($id, Request $request, AdminDatabase $adminDatabase)
    {
        $event = $this->getDoctrine()
            ->getRepository(Event::class)
            ->findOneBy(
                ['id' => $id]
            );


        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminDatabase->event($form);

            $this->addFlash('success', 'L\'évènement a bien été mis à jour');

            return $this->redirectToRoute('eventsAdmin');
        }

        return $this->render(
            'admin/events/update.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/evenements/supprimer", name="deleteEventAdmin")
     *
     * @return RedirectResponse
     */
    public function deleteEvent()
    {
        $article = $this->getDoctrine()
            ->getRepository(Event::class)
            ->findOneBy(
                ['id' => $_POST['id']]
            );

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($article);
        $entityManager->flush();

        return $this->redirectToRoute('eventsAdmin');
    }

    /**
     * @Route("/admin/evenements/{id}/offrir", name="offerEventAdmin")
     *
     * @param $id
     * @param Request $request
     * @param BasketAdministrator $basketAdministrator
     * @param OfferHelper $offerHelper
     * @param MailerInterface $mailer
     * @return Response
     * @throws TransportExceptionInterface
     */
    public function offerEventAction(
        $id,
        Request $request,
        BasketAdministrator $basketAdministrator,
        OfferHelper $offerHelper,
        MailerInterface $mailer
    )
    {
        $event = $this->getDoctrine()
            ->getRepository(Event::class)
            ->findOneBy(
                ['id' => $id]
            );

        $form = $offerHelper->createForm();

        if ($form->isSubmitted() && $form->isValid()) {
            $purchase = $offerHelper->setPurchase($form);

            $userEvent = new UserEvent();
            $userEvent->setUser($form->get('user')->getData());
            $userEvent->setEvent($event);
            $userEvent->setPurchase($purchase);

            $offerHelper->persistAndFlush($purchase, $userEvent);

            $items=$offerHelper->setItem($event->getTitle());

            $invoice = $basketAdministrator->getInvoice($items, $purchase, $form->get('user')->getData());

            //SEND CLIENT MAIL
            $message = (new TemplatedEmail())
                ->from(new Address('postmaster@chamade.co', 'Chamade'))
                ->to($form->get('user')->getData()->getEmail())
                ->subject('Un évènement vous a été offert')
                ->htmlTemplate('emails/offer_event.html.twig')
                ->context([
                    'name' => $event->getTitle(),
                    'reason' => $form->get('content')->getData()
                ])
                ->attachFromPath($invoice);
            $mailer->send($message);

            $this->addFlash('success', 'L\'évènement a bien été offert');
            return $this->redirectToRoute('eventsAdmin');
        }

        return $this->render(
            'admin/events/offer.html.twig',
            [
                'event' => $event,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/evenements/annulation", name="cancelEventAdmin")
     *
     * @param StripeHelper $stripeHelper
     * @param MailerInterface $mailer
     * @return RedirectResponse
     * @throws TransportExceptionInterface
     */
    public function cancelEventAction(
        StripeHelper $stripeHelper,
        MailerInterface $mailer
    )
    {
        $event=$this->getDoctrine()
            ->getRepository(Event::class)
            ->findOneBy(
                ['id' => $_POST['id']]
            );
        $em = $this->getDoctrine()->getManager();

         foreach($event->getUserEvents() as $userEvent){
             $purchase=$userEvent->getPurchase();
             if($stripeHelper->refund($purchase->getStripeId())){
                 $purchase->setStatus('Remboursé');
                 $em->persist($purchase);
             }

             $em->remove($userEvent);

             //SEND CLIENT MAIL
             $message = (new TemplatedEmail())
                 ->from(new Address('postmaster@chamade.co', 'Chamade'))
                 ->to($purchase->getUser()->getEmail())
                 ->subject('Annulation de l\'évènement "'.$event->getTitle().'"')
                 ->htmlTemplate('emails/cancel_event.html.twig')
                 ->context([
                     'name' => $event->getTitle(),
                     'reason' => $_POST['reason']
                 ]);
             $mailer->send($message);
         }

        $em->flush();

        $this->addFlash('success', 'L\'évènement a été annulé et les participantes remboursées');
        return $this->redirectToRoute('eventsAdmin');
    }
}
