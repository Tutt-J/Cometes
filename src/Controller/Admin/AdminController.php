<?php
namespace App\Controller\Admin;

use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Content;
use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\UserType;
use App\Form\UserUpdateAdminType;
use App\Service\Admin\ReportGenerator;
use App\Service\UsersHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class AdminController
 * @package App\Controller
 *
 * @IsGranted("ROLE_ADMIN")
*/
class AdminController extends AbstractController
{

    /**
     * @Route("/admin", name="homeAdmin")
     *
     * @param ReportGenerator $report
     * @return Response
     */
    public function homeAction(ReportGenerator $report)
    {
        //nb user event / nb max
        //si min atteint une couleur sinon une autre si max une autre

        return $this->render('admin/home.html.twig', [
            'purchases' => $report->getPurchases(),
            'totalDay' => $report->getTotalDay()[0]['amount'],
            'totalWeek' => $report->getTotalWeek()[0]['amount'],
            'totalMonth' => $report->getTotalMonth()[0]['amount'],
            'totalYear' => $report->getTotalYear()[0]['amount'],
            'totalEvents' => sizeof($report->getNbEvents()),
            'totalContents' => sizeof($report->getNbContents()),
            'nextEvents' => $report->getFiveNextEvents()
        ]);
    }


    /**
     * @Route("/admin/commandes", name="purchasesAdmin")
     *
     * @param ReportGenerator $report
     * @return Response
     */
    public function purchasesAction(ReportGenerator $report)
    {
        return $this->render(
            'admin/purchases.html.twig',
            [
                'purchases' => $report->getPurchases(),
            ]
        );
    }
}
