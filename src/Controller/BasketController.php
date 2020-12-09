<?php
namespace App\Controller;

use App\Service\BasketAdministrator;
use App\Service\ContentsBasketChecker;
use App\Service\ProcessPurchase;
use App\Service\PromoCode;
use App\Service\StripeHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
     * View the basket page
     *
     * @param SessionInterface $session
     * @param BasketAdministrator $basketAdministrator
     *
     * @param PromoCode $promoCode
     * @return Response
     */
    public function basketAction(SessionInterface $session, BasketAdministrator $basketAdministrator, PromoCode $promoCode)
    {
        if($session->get('basket')){
            $basketAdministrator->resetFidelity();
            $basketAdministrator->checkBasket();
        }

        //Check if promo code is always valid
        $promoCode->checkPromoCode();

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
        //Redirect if there is an error
        if (!$result) {
            return $this->redirectToRoute($session->get('referer')['path'], $session->get('referer')['attributes']);
        }

        return $this->redirectToRoute('shopBasket');
    }


    /**
     * @Route("/modification-panier",name="removeContentBasket")
     *
     * Remove some content in basket
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
                $basketAdministrator->removeContent($contentToRemove);
            }
        }
        return $this->redirectToRoute('shopBasket');
    }


    /**
     * @Route("/mon-compte/passer-la-commande", name="processBasket")
     *
     * Page to verify basket
     *
     * @param BasketAdministrator $basketAdministrator
     * @param SessionInterface $session
     * @param PromoCode $promoCode
     * @return Response
     */
    public function processBasketAction(BasketAdministrator $basketAdministrator, SessionInterface $session, PromoCode $promoCode)
    {
        //If basket does not exist, redirect
        if (empty($session->get('basket')) || is_null($session->get('basket'))) {
            return $this->redirectToRoute('shopBasket');
        }

        $promoCode->checkPromoCode();
        $basketAdministrator->checkBasket();
        $basketAdministrator->applyFidelity();

        return $this->render('basket/process_basket.html.twig');
    }


    /**
     * @Route("/mon-compte/paiement_de_la_commande", name="paymentBasket")
     *
     * Proceed to payment
     *
     * @param SessionInterface $session
     *
     * @param StripeHelper $stripeHelper
     * @param BasketAdministrator $basketAdministrator
     * @param PromoCode $promoCode
     * @return Response
     */
    public function paymentBasketAction(SessionInterface $session, StripeHelper $stripeHelper, BasketAdministrator $basketAdministrator, PromoCode $promoCode)
    {
        $promoCode->checkPromoCode();
        $basketAdministrator->checkBasket();
        $basketAdministrator->applyFidelity();

        //Format items to Stripe
        $items = $basketAdministrator->formatItems($session);

        //Register payment
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
     * Success purchase page
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

        //If we have a basket, view confirmation page else view user purchases page
        if ($session->get('basket') ) {
            //Process to purchase
            $processPurchase->processBasketPurcharse();
            //Remove and set some sessions
            $processPurchase->changeSession();
        } else {
            //Remove all rest session
            $session->remove('purchaseSuccess');
            $session->remove('purchaseSuccessInfos');

            return $this->redirectToRoute('user_purchases');
        }

        return $this->render('basket/confirm_basket.html.twig');
    }

    /**
     * @Route("/mon-compte/retour-commande", name="errorBasket")
     *
     * Error payment page return
     *
     * @return RedirectResponse
     */
    public function errorBasketAction()
    {
        $this->addFlash('error', 'Une erreur est survenue au moment du paiement... Veuillez réessayer ou nous contacter');
        return $this->redirectToRoute('processBasket');
    }
}
