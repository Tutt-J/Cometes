<?php
namespace App\Controller;

use App\Entity\Event;
use App\Service\EventsAdministrator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BalanceController
 * @package App\Controller
 */
class BalanceController extends AbstractController
{
    /**
     * @Route("/equilibre-energetique/yoga", name="yogaBalance")
     * @param EventsAdministrator $eventsAdministrator
     * @return mixed
     */
    public function yogaAction(EventsAdministrator $eventsAdministrator)
    {
        $yoga = $this->getDoctrine()
            ->getRepository(Event::class)
            ->findBecomeEvents($eventsAdministrator->getType('yoga'));

        return $this->render('balance/yoga.html.twig',[
            'yoga' => $yoga,
        ]);
    }

    /**
     * @Route("/equilibre-energetique/sonotherapie", name="sonoConsult")
     *
     * @return Response
     */
    public function sonoAction()
    {
        return $this->render('balance/sonotherapy.html.twig');
    }

    /**
     * @Route("/equilibre-energetique/astrologie", name="astroConsult")
     *
     * @return Response
     */
    public function astroAction()
    {
        return $this->render('balance/astrology.html.twig');
    }
}
