<?php
namespace App\Controller\Admin;

use App\Entity\Content;
use App\Entity\PurchaseContent;
use App\Form\Admin\ContentType;
use App\Service\Admin\AdminDatabase;
use App\Service\Admin\OfferHelper;
use App\Service\BasketAdministrator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class OnlineController
 * @package App\Controller
 *
 * @IsGranted("ROLE_ADMIN")
*/
class OnlineController extends AbstractController
{

    /**
     * @Route("/admin/contenus-en-ligne", name="onlineAdmin")
     *
     * @return Response
     */
    public function onlineAction()
    {
        $contents= $this->getDoctrine()
            ->getRepository(Content::class)
            ->findBy(
                array(),
                array('updatedAt' => 'DESC')
            );

        return $this->render(
            'admin/online/online.html.twig',
            [
                'rituals' => $contents,
            ]
        );
    }

    /**
     * @Route("/admin/contenus-en-ligne/creer", name="createOnlineAdmin")
     *
     * @param Request $request
     * @param AdminDatabase $adminDatabase
     * @return Response
     */
    public function createPost(Request $request, AdminDatabase $adminDatabase)
    {
        $content = new Content();

        $form = $this->createForm(ContentType::class, $content);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $adminDatabase->online($form);

            return $this->redirectToRoute('onlineAdmin');
        }


        return $this->render('admin/online/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/contenus-en-ligne/{id}/modifier", name="updateOnlineAdmin")
     *
     * @param $id
     * @param Request $request
     * @param AdminDatabase $adminDatabase
     * @return Response
     */
    public function updateOnline($id, Request $request, AdminDatabase $adminDatabase)
    {
        $content = $this->getDoctrine()
            ->getRepository(Content::class)
            ->findOneBy(
                ['id' => $id]
            );

        $form = $this->createForm(ContentType::class, $content);

        $form->get('eventDate')->setData(($content->getEventDate()->format('d/m/Y')));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminDatabase->online($form);
            $this->addFlash('success', 'Le contenu a bien été mise à jour');
            return $this->redirectToRoute('onlineAdmin');
        }

        return $this->render(
            'admin/online/update.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/contenus-en-ligne/{id}/offrir", name="offerContentAdmin")
     *
     * @param $id
     * @param BasketAdministrator $basketAdministrator
     * @param OfferHelper $offerHelper
     * @param MailerInterface $mailer
     * @return Response
     * @throws TransportExceptionInterface
     */
    public function offerContentAction(
        $id,
        BasketAdministrator $basketAdministrator,
        OfferHelper $offerHelper,
        MailerInterface $mailer
    )
    {
        $content = $this->getDoctrine()
            ->getRepository(Content::class)
            ->findOneBy(
                ['id' => $id]
            );

       $form = $offerHelper->createForm();

        if ($form->isSubmitted() && $form->isValid()) {
            $purchase = $offerHelper->setPurchase($form);

            $purchaseContent=new PurchaseContent();
            $purchaseContent->setPurchase($purchase);
            $purchaseContent->setContent($content);
            $purchaseContent->setQuantity(1);
            $purchaseContent->setPrice(0);
            $purchase->addPurchaseContent($purchaseContent);

            $offerHelper->persistAndFlush($purchase, $purchaseContent);

            $items=$offerHelper->setItem($content->getTitle());

            $invoice= $basketAdministrator->getInvoice($items, $purchase, $form->get('user')->getData());

            //SEND CLIENT MAIL
            $message = (new TemplatedEmail())
                ->from(new Address('postmaster@chamade.co', 'Chamade'))
                ->to($form->get('user')->getData()->getEmail())
                ->subject('Un contenu en ligne vous a été offert')
                ->htmlTemplate('emails/offer_content.html.twig')
                ->context([
                    'name' => $content->getTitle(),
                    'reason' =>$form->get('content')->getData()
                ])
                ->attachFromPath($invoice);
            $mailer->send($message);

            $this->addFlash('success', 'Le contenu a bien été offert');
            return $this->redirectToRoute('onlineAdmin');
        }

        return $this->render(
            'admin/online/offer.html.twig',
            [
                'content' => $content,
                'form' => $form->createView(),
            ]
        );
    }
}
