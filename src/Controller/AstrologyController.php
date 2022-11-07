<?php

namespace App\Controller;

use App\Entity\Content;
use App\Entity\Event;
use App\Entity\Program;
use App\Entity\ProgramCertified;
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
     * @Route("/astrologie/lecture-de-carte-du-ciel", name="astroConsult")
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
     * @Route("/astrologie/les-cometes", name="listCertificateCometes")
     *
     * @return Response
     */
    public function listCertificateCometesAction()
    {
        $certified= $this->getDoctrine()
            ->getRepository(ProgramCertified::class)
            ->findBy(
                [
                    'program' => 1
                ]
            );

        return $this->render('astrology/cometes-certificate.html.twig', [
            'certified' => $certified
        ]);
    }


}
