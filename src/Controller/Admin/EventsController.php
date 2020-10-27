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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @return Response
     */
    public function offerEventAction($id, Request $request, BasketAdministrator $basketAdministrator, OfferHelper $offerHelper)
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

            $basketAdministrator->getInvoice($items, $purchase, $form->get('user')->getData());

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
     * @return RedirectResponse
     */
    public function cancelEventAction(StripeHelper $stripeHelper)
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
                $em->remove($userEvent);
            }
        }

        $em->flush();

        $this->addFlash('success', 'L\'évènement a été annulé et les participantes remboursées');
        return $this->redirectToRoute('eventsAdmin');
    }
}
