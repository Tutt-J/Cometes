<?php

namespace App\Service;

use App\Entity\Purchase;
use App\Entity\Content;
use App\Entity\Type;
use App\Entity\UserEvent;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\StripeClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

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

    public function setCustomer($return)
    {
        $user=$this->security->getUser();
        try {
            $client = $this->stripeClient->customers->all(['email' => $user->getEmail()]);
        } catch (ApiErrorException $e) {
            $this->flashbag->add('error', $e);
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
                $this->flashbag->add('error', $e);
                $return = new RedirectResponse($this->router->generate('error'.$return, [], UrlGeneratorInterface::ABSOLUTE_URL));
            }
        } else {
            try {
                $return= $this->stripeClient->customers->retrieve(
                    $client['data'][0]['id']
                );
            } catch (ApiErrorException $e) {
                $this->flashbag->add('error', $e);
                $return = new RedirectResponse($this->router->generate('error'.$return, [], UrlGeneratorInterface::ABSOLUTE_URL));
            }
        }
        return $return;
    }

    public function registerPayment($items, $return)
    {
        $client=$this->setCustomer($return);

        try {
            $stripeCreate = $this->stripeClient->checkout->sessions->create(
                [
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
                ]
            );
            $this->session->set('stripe', $stripeCreate);
        } catch (ApiErrorException $e) {
            $this->flashbag->add('error', $e);
            return new RedirectResponse($this->router->generate('error'.$return, [], UrlGeneratorInterface::ABSOLUTE_URL));
        }
    }

    public function retrievePurchase()
    {
        try {
            return $this->stripeClient->checkout->sessions->retrieve(
                $this->session->get('stripe')['id']
            );
        } catch (ApiErrorException $e) {
            return $e;
        }
    }

    public function retrievePaymentIntents($id)
    {
        try {
            return $this->stripeClient->paymentIntents->retrieve(
                $id,
                []
            );
        } catch (ApiErrorException $e) {
            return $e;
        }
    }

    public function getStripe()
    {
        return $this->stripeClient;
    }

    public function setPurchase($charge)
    {
        $purchase=new Purchase();
        $purchase->setStripeId($charge['payment_intent']);
        $totalAmount=0;
        foreach ($charge['display_items'] as $item) {
            $totalAmount+=$item['amount']*$item['quantity'];
        }
        $purchase->setStatus("Paiement accepté");
        $purchase->setAmount($totalAmount/100);
        $purchase->setUser($this->security->getUser());

        return $purchase;
    }
}
