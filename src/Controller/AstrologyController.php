<?php

namespace App\Controller;

use App\Entity\Program;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class AstrologyController
 * @package App\Controller
 */
class AstrologyController extends AbstractController
{
    /**
     * @Route("/astrologie/consultations", name="astroConsult")
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

        return $this->render('astrology/astrology.html.twig');
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

        return $this->render('astrology/astro_training.html.twig', [
            'programs' => $programs
        ]);
    }
}
