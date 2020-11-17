<?php
// src/Controller/shopController
namespace App\Controller;

use App\Entity\Purchase;
use App\Entity\PurchaseContent;
use App\Entity\Content;
use App\Entity\User;
use App\Service\BasketAdministrator;
use App\Service\ContentsBasketChecker;
use App\Service\StripeHelper;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\StripeClient;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BasketController
 * @package App\Controller
 */
class BasketController extends AbstractController
{
    /**
     * @Route("/panier", name="shopBasket")
     *
     * View the basket
     *
     * @param BasketAdministrator $basketAdministrator
     *
     * @return Response
     */
    public function basketAction(BasketAdministrator $basketAdministrator)
    {
        $basketAdministrator->initializeSessions();
        $basketAdministrator->resetFidelity();
        $basketAdministrator->checkBasket();

        return $this->render('basket/basket.html.twig');
    }


    /**
     * @Route("/ajouter-panier", name="addBasket")
     *
     * Add a content on basket
     *
     * @param SessionInterface $session
     * @param BasketAdministrator $basketAdministrator
     *
     * @return RedirectResponse
     */
    public function addBasketAction(SessionInterface $session, BasketAdministrator $basketAdministrator)
    {
        $result=$basketAdministrator->addContent($_POST['id']);
        if ($result == null) {
            return $this->redirectToRoute($session->get('referer')['path'], $session->get('referer')['attributes']);
        }

        return $this->redirectToRoute('shopBasket');
    }


    /**
     * @Route("/modification-panier",name="removeContentBasket")
     *
     * @param BasketAdministrator $basketAdministrator
     *
     * @param ContentsBasketChecker $contentsBasketChecker
     * @return RedirectResponse
     */
    public function removeContentAction(BasketAdministrator $basketAdministrator, ContentsBasketChecker $contentsBasketChecker)
    {
        if (isset($_POST['removeBasket'])) {
            $contentToRemove=$contentsBasketChecker->getContent($_POST['id']);
            if (isset($contentToRemove)) {
                $basketAdministrator->removeContent($_POST['id']);
            }
        }
        return $this->redirectToRoute('shopBasket');
    }


    /**
     * @Route("/mon-compte/passer-la-commande", name="processBasket")
     *
     * @param BasketAdministrator $basketAdministrator
     * @return Response
     */
    public function processBasketAction(BasketAdministrator $basketAdministrator, SessionInterface $session)
    {
        $basketAdministrator->checkBasket();
        $basketAdministrator->applyFidelity();

        if (empty($session->get('basket')) || is_null($session->get('basket'))) {
            return $this->redirectToRoute('shopBasket');
        }

        return $this->render('basket/process_basket.html.twig');
    }


    /**
     * @Route("/mon-compte/paiement_de_la_commande", name="paymentBasket")
     *
     * @param SessionInterface $session
     *
     * @param StripeHelper $stripeHelper
     * @return Response
     *
     */
    public function paymentBasketAction(SessionInterface $session, StripeHelper $stripeHelper)
    {
        $items=[];
        foreach ($session->get('basket') as $content) {
            if ($content['isFidelity']) {
                $price=$content['Entity']->getFidelityPrice();
            } else {
                $price=$content['Entity']->getPrice();
            }
            $array= [
                'name' => $content['Entity']->getTitle(),
                'amount' => $price*100,
                'currency' => 'eur',
                'quantity' => 1,
            ];
            array_push($items, $array);
        }

        $stripeHelper->registerPayment($items, 'Basket');

        return $this->render(
            'basket/payment.html.twig',
            [
                'stripe_id' => $session->get('stripe')['id'],
                'stripe_pk' => $_ENV['STRIPE_PUBLIC']
            ]
        );
    }


    /**
     * @Route("/mon-compte/confirmation-de-votre-commande", name="successBasket")
     *
     * @param SessionInterface $session
     *
     * @param MailerInterface $mailer
     * @param BasketAdministrator $basketAdministrator
     * @param StripeHelper $stripeHelper
     * @return RedirectResponse|Response
     *
     * @throws TransportExceptionInterface
     */
    public function successBasketAction(
        SessionInterface $session,
        MailerInterface $mailer,
        BasketAdministrator $basketAdministrator,
        StripeHelper $stripeHelper
    ) {
        $em = $this->getDoctrine()->getManager();

        if ($session->get('basket') && $session->get('stripe')) {
            $charge= $stripeHelper->retrievePurchase('Basket');

            //SET PURCHASE
            $purchase=$stripeHelper->setPurchase($charge);

            //SET ALL PURCHASE CONTENTS
            for ($i=0; $i < sizeof($charge['display_items']);$i++) {
                $content = $this->getDoctrine()
                    ->getRepository(Content::class)
                    ->findOneBy(
                        ['id' => $session->get('basket')[$i]['Entity']->getId()]
                    );

                $purchaseContent=new PurchaseContent();
                $purchaseContent->setPurchase($purchase);
                $purchaseContent->setContent($content);
                $purchaseContent->setQuantity($charge['display_items'][$i]['quantity']);
                if ($session->get('basket')[$i]['isFidelity']) {
                    $purchaseContent->setPrice($content->getFidelityPrice());
                } else {
                    $purchaseContent->setPrice($content->getPrice());
                }

                $purchase->addPurchaseContent($purchaseContent);
                $em->persist($purchaseContent);
            }
            $em->persist($purchase);

            $em->flush();
            $invoice=$basketAdministrator->getInvoice($charge['display_items'], $purchase);

            $message = (new TemplatedEmail())
                ->from(new Address('postmaster@chamade.co', 'Chamade'))
                ->to($this->getUser()->getEmail())
                ->subject('Confirmation de commande')
                ->htmlTemplate('emails/purchase_confirm.html.twig')
                ->attachFromPath($invoice);
            $mailer->send($message);

            $contents='<ul>';
            foreach ($charge['display_items'] as $item) {
                $contents.= '<li>'.$item['custom']['name'].'</li>';
            }
            $contents.='</ul>';

            $emailAdmin = (new Email())
                ->from(new Address('postmaster@chamade.co', 'SITE WEB Chamade'))
                ->to('hello@chamade.co')
                ->subject('Nouvel achat sur le site')
                ->html(
                    '
                    <p>Nom : '.$this->getUser()->getFirstName().' '.$this->getUser()->getLastName().'</p>
                    <p>Email : <a href="mailto:'.$this->getUser()->getEmail().'">'.$this->getUser()->getEmail().'</a></p>
                    <p>Contenus :</p>'.$contents
                );
            $mailer->send($emailAdmin);

            $session->set('purchaseSuccess', $session->get('basket'));
            $session->set('purchaseSuccessInfos', $session->get('purchaseInfos'));
            $session->remove('basket');
            $session->remove('purchaseInfos');
            $session->remove('stripe');
        } else {
            $session->remove('purchaseSuccess');
            $session->remove('purchaseSuccessInfos');

            return $this->redirectToRoute('user_purchases');
        }

        return $this->render('basket/confirm_basket.html.twig');
    }


    /**
     * @Route("/mon-compte/retour-commande", name="errorBasket")
     *
     * @return RedirectResponse
     */
    public function errorBasketAction()
    {
        $this->addFlash('error', 'Une erreur est survenue au moment du paiement... Veuillez réessayer ou nous contacter');
        return $this->redirectToRoute('processBasket');
    }
}
