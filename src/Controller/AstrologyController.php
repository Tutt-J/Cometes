<?php

namespace App\Controller;

use App\Entity\Content;
use App\Entity\Event;
use App\Entity\Program;
use App\Service\ContentOnlineAdministrator;
use App\Service\EventsAdministrator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;


/**
 * Class AstrologyController
 * @package App\Controller
 */
class AstrologyController extends AbstractController
{
    /**
     * @Route("/astrologie/theme-astral", name="astroConsult")
     *
     * @param SessionInterface $session
     * @return Response
     */
    public function astroConsultAction(SessionInterface $session)
    {
        
      if(isset($_GET['affiliate'])){
          $session->set('affiliate', $_GET['affiliate']);
      }
      if(null == $session->get('affiliate')){
          $session->set('affiliate', "");
      }

        return $this->render('astrology/consult.html.twig');
    }

    /**
     * @Route("/astrologie/formations", name="astroTraining")
     *
     * @param SessionInterface $session
     * @return Response
     */
    public function astroTrainingAction(SessionInterface $session)
    {
        $programs= $this->getDoctrine()
            ->getRepository(Program::class)
            ->findByType("Astrology");

        return $this->render('astrology/training.html.twig', [
            'programs' => $programs
        ]);
    }

    /**
     * @Route("/astrologie/initiations", name="initiationsOnline")
     *
     * @param EventsAdministrator $eventsAdministrator
     * @return Response
     */
    public function astroInitiationAction(EventsAdministrator $eventsAdministrator)
    {
        $initiations = $this->getDoctrine()
            ->getRepository(Event::class)
            ->findBecomeEvents($eventsAdministrator->getType('initiation'));

        return $this->render(
            'astrology/initiations.html.twig',
            [
                'initiations' => $initiations,
            ]
        );

    }

    /**
     * @Route("/astrologie/evenements/initiations/{slug}",
     * name="initiationsEvent",
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
    public function initiationsAction(Event $event, EventsAdministrator $eventsAdministrator)
    {
        return $eventsAdministrator->renderEventPage($event);
    }


    /**
     * @Route("/astrologie/initiations/{slug}",
     * name="initiationOnline",
     * requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     *
     * @param $slug
     * @param ContentOnlineAdministrator $contentOnlineAdministrator
     * @return Response
     */
    public function introWorkshopAction($slug, ContentOnlineAdministrator $contentOnlineAdministrator)
    {
        return $this->render(
            'online/content.html.twig',
            $contentOnlineAdministrator->generateContent($slug, 'initiation')
        );
    }
}
