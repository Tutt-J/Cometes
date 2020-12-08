<?php
// src/Controller/shopController
namespace App\Controller;

use App\Entity\PromoCode;
use App\Entity\Purchase;
use App\Entity\PurchaseContent;
use App\Entity\Content;
use App\Entity\User;
use App\Service\BasketAdministrator;
use App\Service\ContentsBasketChecker;
use App\Service\ProcessPurchase;
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
use Symfony\Component\Process\Process;
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
    public function basketAction(BasketAdministrator $basketAdministrator, ProcessPurchase $processPurchase)
    {
        $basketAdministrator->initializeSessions();
        if(isset($_POST['verify_code'])){
            $processPurchase->verifyPromoCode($_POST['promo_code']);
        }
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
     * @param SessionInterface $session
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
     * @param BasketAdministrator $basketAdministrator
     * @return Response
     */
    public function paymentBasketAction(SessionInterface $session, StripeHelper $stripeHelper, BasketAdministrator $basketAdministrator)
    {
        $items = $basketAdministrator->formatItems($session);

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
     * @param ProcessPurchase $processPurchase
     * @return RedirectResponse|Response
     */
    public function successBasketAction(
        SessionInterface $session,
        ProcessPurchase $processPurchase
    ) {
        $em = $this->getDoctrine()->getManager();

        if ($session->get('basket') ) {
            $processPurchase->processBasketPurcharse();
            $processPurchase->changeSession();
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
