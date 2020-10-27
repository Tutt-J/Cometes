<?php
namespace App\Controller\Admin;

use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Content;
use App\Entity\Purchase;
use App\Entity\PurchaseContent;
use App\Entity\User;
use App\Entity\UserEvent;
use App\Form\ChangePasswordType;
use App\Form\UserType;
use App\Form\UserUpdateAdminType;
use App\Service\Admin\ReportGenerator;
use App\Service\StripeHelper;
use App\Service\UsersHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
            'nextEvents' => $report->getThreeNextEvents()
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

    /**
     * @Route("/admin/commandes/remboursement", name="refundPurchaseAdmin")
     *
     * @param $id
     * @param StripeHelper $stripeHelper
     * @return RedirectResponse
     */
    public function refundPurchaseAction(StripeHelper $stripeHelper)
    {
        $em = $this->getDoctrine()->getManager();

        $purchase=$this->getDoctrine()
            ->getRepository(Purchase::class)
            ->findOneBy(
                ['id' => $_POST['id']]
            );

        foreach($purchase->getPurchaseContent() as $purchaseContent){
            $em->remove($purchaseContent);
        }

        if(!is_null($purchase->getUserEvent())){
            $em->remove($purchase->getUserEvent());
        }

        $stripeHelper->refund($purchase->getStripeId());

        $purchase->setStatus('Remboursé');
        $em->persist($purchase);
        $em->flush();

        $this->addFlash('success', 'La commande a été remboursée');
        return $this->redirectToRoute('purchasesAdmin');
    }
}
