<?php
namespace App\Controller\Admin;

use App\Entity\Purchase;
use App\Service\Admin\ReportGenerator;
use App\Service\BasketAdministrator;
use App\Service\ProcessPurchase;
use App\Service\StripeHelper;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
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
     * @param StripeHelper $stripeHelper
     * @param BasketAdministrator $basketAdministrator
     * @param MailerInterface $mailer
     * @return RedirectResponse
     * @throws TransportExceptionInterface
     */
    public function refundPurchaseAction(
        StripeHelper $stripeHelper,
        BasketAdministrator $basketAdministrator,
        MailerInterface $mailer,
        ProcessPurchase $processPurchase
    )
    {
        $em = $this->getDoctrine()->getManager();

        $purchase=$this->getDoctrine()
            ->getRepository(Purchase::class)
            ->findOneBy(
                ['id' => $_POST['id']]
            );

        $items=[];

        foreach($purchase->getPurchaseContent() as $purchaseContent){
            $em->remove($purchaseContent);
            array_push($items, [
                'Entity' => $purchaseContent->getContent(),
                'isFidelity' => false
            ]);
        }

        if(!is_null($purchase->getUserEvent())){
            $purchase->getUserEvent()->getEvent()->setPrice($purchase->getAmount());
            array_push($items, [
                'Entity' => $purchase->getUserEvent()->getEvent(),
                'isFidelity' => false
            ]);
            $em->remove($purchase->getUserEvent());
        }

        $refund = $stripeHelper->refund($purchase->getStripeId());

        if($refund){
            $invoice = $processPurchase->getInvoice($items, $purchase, $purchase->getUser(), true);
            //SEND CLIENT MAIL
            $message = (new TemplatedEmail())
                ->from(new Address('hello@cometes.co', 'Com??tes'))
                ->to($purchase->getUser()->getEmail())
                ->subject('Remboursement d\'une commande')
                ->htmlTemplate('emails/refunding_order.html.twig')
                ->context([
                    'number' => 'WEB'.$purchase->getCreatedAt()->format("Y").'_'.$purchase->getId(),
                    'price' => $purchase->getAmount()
                ])
                ->attachFromPath($invoice);
            $mailer->send($message);

            $purchase->setStatus('Rembours??');
            $em->persist($purchase);
            $em->flush();

            $this->addFlash('success', 'La commande a ??t?? rembours??e');
        } else{
           $this->addFlash('error', 'Echec du remboursement, veuillez v??rifier que la commande est remboursable sur Stripe.');
        }



        return $this->redirectToRoute('purchasesAdmin');
    }
}
