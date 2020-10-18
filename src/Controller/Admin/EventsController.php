<?php
namespace App\Controller\Admin;

use App\Entity\Event;
use App\Form\Admin\EventType;
use App\Service\Admin\AdminDatabase;
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
                array('createdAt' => 'DESC')
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
        $form->get('startDate')->setData(($event->getStartDate()->format('d/m/Y H:i')));

        if (!is_null($event->getEndDate())) {
            $form->get('endDate')->setData(($event->getEndDate()->format('d/m/Y H:i')));
        }

        $form->get('eventPriceType')->setData($event->getEventPricings());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminDatabase->event($form);

            $this->addFlash('success', 'L\'évènement a bien été mise à jour');

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
}
