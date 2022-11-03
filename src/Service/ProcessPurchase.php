<?php

namespace App\Service;

use App\Entity\Content;
use App\Entity\Event;
use App\Entity\PromoCode;
use App\Entity\Purchase;
use App\Entity\PurchaseContent;
use App\Entity\UserEvent;
use Doctrine\ORM\EntityManagerInterface;
use Konekt\PdfInvoice\InvoicePrinter;
use phpDocumentor\Parser\Exception;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class ProcessPurchase
 * @package App\Service
 */
class ProcessPurchase
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;
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
     * @var StripeHelper
     */
    private StripeHelper $stripeHelper;
    /**
     * @var Purchase
     */
    private Purchase $purchase;
    /**
     * @var SendMail
     */
    private SendMail $sendMail;
    /**
     * @var object|null
     */
    private ?object $event;

    /**
     * @var array
     */
    private array $giftCard = [];
    /**
     * @var \App\Service\PromoCodeAdministrator
     */
    private \App\Service\PromoCodeAdministrator $promoCode;

    /**
     * BasketAdministrator constructor.
     * @param SendMail $sendMail
     * @param StripeHelper $stripeHelper
     * @param EntityManagerInterface $em
     * @param Security $security
     * @param SessionInterface $session
     * @param FlashBagInterface $flashbag
     */
    public function __construct(
        SendMail $sendMail,
        StripeHelper $stripeHelper,
        EntityManagerInterface $em,
        Security $security,
        SessionInterface $session,
        FlashBagInterface $flashbag,
        \App\Service\PromoCodeAdministrator $promoCode
    )
    {
        $this->em = $em;
        $this->session = $session;
        $this->flashbag=$flashbag;
        $this->security=$security;
        $this->stripeHelper = $stripeHelper;
        $this->sendMail = $sendMail;
        $this->promoCode=$promoCode;
    }

    /**
     * Process to Basket Purchase
     */
    public function processBasketPurcharse(){
        //Set purchase and it's content
        $this->setPurchaseContent();

        //If we have promoCode, update it
        if($this->session->get('promoCode')){
            $this->promoCode->updatePromoCode();
        }

        //Flush all
        $this->em->flush();

        //Send client e-mail
        $this->sendMail->sendTemplated(array_merge([$this->getInvoice($this->session->get('basket'), $this->purchase)],$this->giftCard), 'Confirmation de commande', 'purchase_confirm');

        //Remove gift cards
        foreach($this->giftCard as $giftCard){
            unlink($giftCard);
        }

        //Send admin e-mail
        $this->sendAdminContentHtmlMail();
    }

    /**
     * Process to purchase event
     */
    public function processEventPurchase(){
        //Set purchase and purchaseEvent
        $this->setPurchaseEvent();

        //If we have promo code, update it
        if($this->session->get('promoCode')){
            $this->promoCode->updatePromoCode();
        }

        //Flush all
        $this->em->flush();

        //Set event price to price set wit reductions
        $this->event->setPrice($this->session->get('price'));

        //Format items to use with invoice
        $items=[
            [
                'Entity' => $this->event,
                'isFidelity' => false
            ]
        ];

        //Send client e-mail
        $this->sendMail->sendTemplated([$this->getInvoice($items, $this->purchase)], 'Votre pré-inscription a bien été prise en compte', 'event_confirm', ['event' => $this->event]);
        //Send admin e-mail
        $this->sendAdminEventHtmlMail();
    }

    /**
     *
     * Set purchase
     *
     * @return Purchase
     * @throws Exception
     */
    public function setPurchase()
    {
        //Create purchase
        $purchase=new Purchase();

        //If there is a payment
        if($this->session->get('stripe')) {
            //Retrieve the charge Stripe
            $charge= $this->stripeHelper->retrievePurchase('Basket');
            if($charge['payment_status'] != "paid"){
               throw new AccessDeniedException("Access denied because there is no payment");
            }

            //Set Stripe Id
            $purchase->setStripeId($charge['payment_intent']);

            //Calculate amount
            $totalAmount=0;
            foreach ($charge['display_items'] as $item) {
                $totalAmount+=$item['amount']*$item['quantity'];
            }

            //Set Amount with total amount and discount
            $purchase->setAmount(($totalAmount-$charge['total_details']['amount_discount'])/100);

            //If there is a meta description, set it in content
            if(!empty($this->stripeHelper->retrievePaymentIntents($charge['payment_intent'], 'RegisterEvent')['metadata']['Description'])){
                $purchase->setContent($this->stripeHelper->retrievePaymentIntents($charge['payment_intent'], 'RegisterEvent')['metadata']['Description']);
            }
            //Set status
            $purchase->setStatus("Paiement accepté");
        } else{
            //IF PURCHASE IS FREE
            //No stripe id so set phrase with promoCode
            $purchase->setStripeId("Pas d'id stripe car offert avec la carte cadeau ". $this->session->get('promoCode')->getCode());
            //Set Amount to zero
            $purchase->setAmount(0);
            //Set status
            $purchase->setStatus("Offert avec code promotionnel ou carte cadeau");
        }

        //Set purchase user
        $purchase->setUser($this->security->getUser());

        return $purchase;
    }

    /**
     * Set purchase and it's event
     */
    public function setPurchaseEvent(){
        //Set event
        $this->event = $this->em
            ->getRepository(Event::class)
            ->findOneBy(
                ['id' => $this->session->get('event')]
            );
        //Set purchase
        $this->purchase=$this->setPurchase();
        $this->em->persist($this->purchase);

        ///Create UserEvent
        $userEvent=new UserEvent();
        $userEvent->setUser($this->security->getUser());

        $userEvent->setEvent($this->event);
        $userEvent->setPurchase($this->purchase);
        $this->em->persist($userEvent);
    }

    /**
     * Set purchase and it's content
     */
    public function setPurchaseContent(){
        //Set the purchase
        $this->purchase=$this->setPurchase();

        //SET ALL PURCHASE CONTENTS
        for ($i=0; $i < sizeof($this->session->get('basket'));$i++) {
            $content = $this->em
                ->getRepository(Content::class)
                ->findOneBy(
                    ['id' => $this->session->get('basket')[$i]['Entity']->getId()]
                );

            //If it's a gift card, set the code in database
            if($content->getType()->getSlug() == "giftCard"){
                $promoCode=$this->promoCode->setPromoCode($content->getPrice());
                array_push($this->giftCard, $this->promoCode->generateGiftCard($content->getPrice(), $promoCode));
            }

            //Create purchase content
            $purchaseContent=new PurchaseContent();
            $purchaseContent->setPurchase($this->purchase);
            $purchaseContent->setContent($content);
            $purchaseContent->setQuantity(1);
            //Choice price with fidelity
            if ($this->session->get('basket')[$i]['isFidelity']) {
                $purchaseContent->setPrice($content->getFidelityPrice());
            } else {
                $purchaseContent->setPrice($content->getPrice());
            }

            $this->purchase->addPurchaseContent($purchaseContent);
            $this->em->persist($purchaseContent);
        }
        $this->em->persist($this->purchase);
    }

    /**
     * Send HTMl Admin mail for Contents Purchase
     */
    public function sendAdminContentHtmlMail(){
        $contents='<ul>';
        foreach ($this->session->get('basket') as $item) {
            $affiliate="Non affilié";
            if($item['Entity']->getType()->getSlug() == "giftCard" && $this->session->get('affiliateGift')){
                $affiliate = "Affilié à ".$this->session->get('affiliateGift');
            }
            $contents.= '<li>'.$item['Entity']->getTitle().' - '.$affiliate.'</li>';
        }
        $contents.='</ul>';
        $html='<p>Nom : '.$this->security->getUser()->getFirstName().' '.$this->security->getUser()->getLastName().'</p>
                    <p>Email : <a href="mailto:'.$this->security->getUser()->getEmail().'">'.$this->security->getUser()->getEmail().'</a></p>
                    <p>Contenus :</p>'.$contents;
        $this->sendMail->sendBasicEmail($html, 'Nouvel achat sur le site');
    }


    /**
     * Send HTMl Admin mail for Event Purchase
     */
    public function sendAdminEventHtmlMail(){
        $html='<p>Nom : '.$this->security->getUser()->getFirstName().' '.$this->security->getUser()->getLastName().'</p>
                    <p>Email : <a href="mailto:'.$this->security->getUser()->getEmail().'">'.$this->security->getUser()->getEmail().'</a></p>
                    <p>Évènement : '.$this->event->getTitle().'</p>
                    <p>Infos : '.$this->session->get('description').'</p>
                ';
        $this->sendMail->sendBasicEmail($html, 'Nouvel inscription à un évènement');
    }


    /**
     *
     * Generate and Save invoice
     *
     * @param $items
     * @param $purchase
     * @param null $user
     * @param bool $refund
     * @return string
     */
    public function getInvoice($items, $purchase, $user=null, $refund = false)
    {
        if(is_null($user)){
            $user=$this->security->getUser();
        }
        $invoice = new InvoicePrinter("A4", "€", "fr");

        /* Header settings */
        $invoice->setLogo("build/images/Logo_noir.png");   //logo image path
        $invoice->setColor("#a3491f");      // pdf color scheme
        $invoice->setType("Facture");    // Invoice Type
        $invoice->setReference('WEB'.date('Y').'_'.$purchase->getId());   // Reference
        $invoice->setDate(date('d/m/Y', time()));   //Billing Date
        $invoice->setTime(date('H:i:s', time()));   //Billing Time
        $invoice->setFrom(array("CHAMADE","419 RUE DE BORINGES","74930 REIGNIER-ESERY"));
        $invoice->setTo(array(
            $this->stripAccents($user->getFirstName()).' '.$this->stripAccents($user->getLastName()),
            $this->stripAccents($user->getAddress()->getStreet()),
            $this->stripAccents($user->getAddress()->getPostalCode())." ".$this->stripAccents($user->getAddress()->getCity()),
            $this->stripAccents($user->getAddress()->getCountry())
        ));

        $total=0;
        foreach ($items as $item) {
            if($item['isFidelity']){
                $price=$item['Entity']->getFidelityPrice();
            } else{
                $price=$item['Entity']->getPrice();
            }
            $invoice->addItem(
                $item['Entity']->getTitle(),
                null,
                1,
                false,
                $price,
                false,
                $price
            );
            $total+=$price;
        }
        if(null != $this->session->get('applyPromo')){
            $invoice->addTotal("Réductions", $this->session->get('applyPromo'));
            $total=$total-$this->session->get('applyPromo');
        }
        $invoice->addTotal("Total", $total);
        $invoice->addTotal("Dont TVA (20%)",$this->getTVA($total));
        $invoice->flipflop();
        if($refund){
            $invoice->addBadge("Remboursé");
        } elseif($total == 0 ){
            $invoice->addBadge("Offert");
        } else{
            $invoice->addBadge("Payée");
        }

        $invoice->setFooternote("Chamade");

        if (!file_exists('../src/invoices')) {
            mkdir('../src/invoices', 0775, true);
        }

        $path='../src/invoices/Facture_WEB'.date('Y').'_'.$purchase->getId().'.pdf';
        $invoice->render($path, 'F');

        return $path;
    }

    /**
     * Update some sessions
     */
    public function changeSession(){
        $this->session->set('purchaseSuccess', $this->session->get('basket'));
        $this->session->set('purchaseSuccessInfos', $this->session->get('purchaseInfos'));
        $this->session->remove('basket');
        $this->session->remove('purchaseInfos');
        $this->session->remove('stripe');
        $this->session->remove('promoCode');
        $this->session->remove('applyPromo');
        $this->session->remove('description');
    }

    /**
     *
     * Remove accents
     *
     * @param $str
     * @return string
     */
    public function stripAccents($str) {
        return strtoupper(strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY'));
    }

    public function getTVA($price){
        return ($price*20)/100;
    }
}
