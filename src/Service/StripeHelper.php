<?php

namespace App\Service;

use App\Entity\Purchase;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
/**
 * Class BasketAdministrator
 * @package App\Service
 */
class StripeHelper
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;
    /**
     * @var ContentsBasketChecker
     */
    private ContentsBasketChecker $contentsBasketChecker;
    /**
     * @var SessionInterface
     */
    private SessionInterface $session;
    /**
     * @var FlashBagInterface
     */
    private FlashBagInterface $flashbag;

    /**
     * @var Security
     */
    private Security $security;

    /**
     * @var UrlGeneratorInterface
     */
    protected UrlGeneratorInterface $router;

    /**
     * @var StripeClient
     */
    private StripeClient $stripeClient;

    /**
     * BasketAdministrator constructor.
     * @param EntityManagerInterface $em
     * @param Security $security
     * @param SessionInterface $session
     * @param ContentsBasketChecker $contentsBasketChecker
     * @param FlashBagInterface $flashbag
     * @param UrlGeneratorInterface $router
     */
    public function __construct(EntityManagerInterface $em, Security $security, SessionInterface $session, ContentsBasketChecker $contentsBasketChecker, FlashBagInterface $flashbag, UrlGeneratorInterface $router)
    {
        $this->em = $em;
        $this->session = $session;
        $this->contentsBasketChecker = $contentsBasketChecker;
        $this->flashbag=$flashbag;
        $this->stripeClient=new StripeClient($_ENV['STRIPE_SECRET']);
        $this->security=$security;
        $this->router= $router;
    }

    /**
     * @param $return
     * @return \Stripe\Customer|RedirectResponse
     */
    public function setCustomer($return)
    {
        $user=$this->security->getUser();
        try {
            $client = $this->stripeClient->customers->all(['email' => $user->getEmail()]);
        } catch (ApiErrorException $e) {
                $this->flashbag->add('error', 'Impossible de r??cup??rer le listing client Stripe. Veuillez nous contacter. ('.$e.')');
            $return = new RedirectResponse($this->router->generate('error'.$return, [], UrlGeneratorInterface::ABSOLUTE_URL));
        }
        if (empty($client['data'])) {
            try {
                $return= $this->stripeClient->customers->create([
                    'name' => $user->getFirstName() . ' ' . $user->getLastName(),
                    'email' => $user->getEmail(),
                    'address' => [
                        'line1' => $user->getAddress()->getStreet(),
                        'line2' => $user->getAddress()->getOthersInformations(),
                        'postal_code' => $user->getAddress()->getPostalCode(),
                        'city' => $user->getAddress()->getCity(),
                        'country' => $user->getAddress()->getCountry(),
                    ]
                ]);
            } catch (ApiErrorException $e) {
                $this->flashbag->add('error', 'Probl??me Stripe lors de la cr??ation du client. Veuillez nous contacter. ('.$e.')');
                $return = new RedirectResponse($this->router->generate('error'.$return, [], UrlGeneratorInterface::ABSOLUTE_URL));
            }
        } else {
            try {
                $return= $this->stripeClient->customers->retrieve(
                    $client['data'][0]['id']
                );
            } catch (ApiErrorException $e) {
                $this->flashbag->add('error', 'Impossible de r??cup??rer le client sur Stripe. Veuillez nous contacter. ('.$e.')');
                $return = new RedirectResponse($this->router->generate('error'.$return, [], UrlGeneratorInterface::ABSOLUTE_URL));
            }
        }
        return $return;
    }

    /**
     *
     * Register Stripe payment
     *
     * @param $items
     * @param $return
     * @return RedirectResponse
     */
    public function registerPayment($items, $return)
    {
        $client=$this->setCustomer($return);

        try {
            $stripeRequest= [
                'customer' => $client['id'],
                'payment_method_types' => ['card'],
                'line_items' => $items,
                'payment_intent_data' => [
                    'metadata' => [
                        'Description' => $this->session->get('description')
                    ]
                ],
                'success_url' => $this->router->generate('success'.$return, [], UrlGeneratorInterface::ABSOLUTE_URL)
                ,
                'cancel_url' => $this->router->generate('error'.$return, [], UrlGeneratorInterface::ABSOLUTE_URL)
            ];

            //If we have a promo code, create a discount and apply it to stripe checkout
            if($this->session->get('applyPromo')){
                $discount=$this->createDiscount();
                $stripeRequest['discounts']= [['coupon' => $discount['id']]];
            }

            //Create Stripe checkout session
            $stripeCreate = $this->stripeClient->checkout->sessions->create($stripeRequest);

            //Set a variable with stripe session
            $this->session->set('stripe', $stripeCreate);
        } catch (ApiErrorException $e) {
            dd($e);
                $this->flashbag->add('error', 'Impossible de proc??der au paiement. Veuillez nous contacter. ('.$e.')');
            return new RedirectResponse($this->router->generate('error'.$return, [], UrlGeneratorInterface::ABSOLUTE_URL));
        }
    }

    /**
     * @return \Stripe\Coupon
     * @throws ApiErrorException
     */
    public function createDiscount(){
        return $this->stripeClient->coupons->create([
            'amount_off' => $this->session->get('applyPromo')*100,
            'currency' => 'EUR',
            'duration' => 'once',
        ]);
    }

    /**
     * @param $return
     * @return \Stripe\Checkout\Session|RedirectResponse
     */
    public function retrievePurchase($return)
    {
        try {
            return $this->stripeClient->checkout->sessions->retrieve(
               $this->session->get('stripe')['id']
            );
        } catch (ApiErrorException $e) {
            $this->flashbag->add('error', 'Le paiement n\'est pas trouv?? ou a ??chou??. Veuillez nous contacter. ('.$e.')');
            return new RedirectResponse($this->router->generate('error'.$return, [], UrlGeneratorInterface::ABSOLUTE_URL));
        }
    }

    /**
     * @param $id
     * @param $return
     * @return \Stripe\PaymentIntent|RedirectResponse
     */
    public function retrievePaymentIntents($id, $return)
    {
        try {
            return $this->stripeClient->paymentIntents->retrieve(
                $id,
                []
            );
        } catch (ApiErrorException $e) {
            $this->flashbag->add('error', 'Nous ne pouvons pas retrouver les informations de paiements. Veuillez nous contacter. ('.$e.')');
            return new RedirectResponse($this->router->generate('error'.$return, [], UrlGeneratorInterface::ABSOLUTE_URL));
        }
    }

    /**
     * @return StripeClient
     */
    public function getStripe()
    {
        return $this->stripeClient;
    }

    /**
     * @param $charge
     * @return bool
     */
    public function refund($charge){
        try {
            $this->stripeClient->refunds->create([
                'payment_intent' => $charge,
            ]);
            return true;
        } catch (ApiErrorException $e) {
            return false;
        }
    }
}
