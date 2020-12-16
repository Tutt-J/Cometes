<?php

namespace App\Service;


use App\Entity\PromoCode;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


/**
 * Class PromoCodeAdministrator
 * @package App\Service
 */
class PromoCodeAdministrator
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
     * @var UrlGeneratorInterface
     */
    protected UrlGeneratorInterface $router;


    /**
     * BasketAdministrator constructor.
     * @param EntityManagerInterface $em
     * @param SessionInterface $session
     * @param FlashBagInterface $flashbag
     */
    public function __construct(
        EntityManagerInterface $em,
        SessionInterface $session,
        FlashBagInterface $flashbag
    )
    {
        $this->em = $em;
        $this->session = $session;
        $this->flashbag=$flashbag;
    }


    /**
     * Update promoCode RestAmount
     */
    public function updatePromoCode(){
        $promoCode=$this->em
            ->getRepository(PromoCode::class)
            ->findOneBy(
                [
                    'code' =>  $this->session->get('promoCode')->getCode()
                ]
            );
        $promoCode->setRestAmount($promoCode->getRestAmount()-$this->session->get('applyPromo'));
        //Remove if 0 else persist

        $this->em->persist($promoCode);
    }


    /**
     * Verify if promo code is valid with $_POST or $_SESSION
     */
    public function checkPromoCode(){
        if($this->session->get('promoCode')){
            $this->verifyPromoCode($this->session->get('promoCode')->getCode());
        }

        if(isset($_POST['verify_code'])){
            $this->verifyPromoCode($_POST['promo_code']);
        }
    }

    /**
     *
     * Verify if promo code is valid
     *
     * @param $promoCode
     * @return bool
     */
    public function verifyPromoCode($promoCode){
        $promoCode=$this->em
            ->getRepository(PromoCode::class)
            ->findOneBy(
                [
                    'code' => $promoCode
                ]
            );

        if($promoCode == null || $promoCode->getRestAmount() <= 0){
            $this->session->remove('promoCode');
            $this->session->remove('applyPromo');
            $this->flashbag->add('error', 'Le code promotionnel est invalide.');
            return false;
        }
        $this->flashbag->add('success', 'Le code a été appliqué avec succès.');
        $this->session->set('promoCode', $promoCode);
        return true;
    }


    /**
     *
     * Create a promo Code
     *
     * @param $amount
     * @return string
     * @throws Exception
     */
    public function setPromoCode($amount){
        $chars = "123456789ABCDEFGHIJKLMNPQRSTUVWXYZ";
        $res = "";

        for ($i = 0; $i < 6; $i++) {
            $res .= $chars[random_int(0, strlen($chars) - 1)];
        }

        $promoCode=new PromoCode();
        $promoCode->setAmount($amount);
        $promoCode->setRestAmount($amount);
        $promoCode->setCode($res);
        $this->em->persist($promoCode);
        return $res;
    }

    /**
     * @param $amount
     * @param $code
     * @return string
     * @throws Exception
     */
    public function generateGiftCard($amount, $code){

        // Load And Create Image From Source
        $our_image = imagecreatefromjpeg('build/images/gift_card_mail.jpg');

        // Allocate A Color For The Text Enter RGB Value
        $color = imagecolorallocate($our_image, 255, 255, 255);

        // Set Path to Font File
        $font_path = getcwd().'/fonts/Trocchi-Regular.ttf';

        $angle=0;

        // Print Text On Image
        imagettftext($our_image, 56, $angle,650,800, $color, $font_path,  $amount.'€ avec le code '.$code);

        // Send Image to Browser
        $name='../assets/gift_cards/Carte_cadeau_'.random_int(0,100000).'.jpg';
        imagejpeg($our_image, $name);

        // Clear Memory
        imagedestroy($our_image);

        return $name;
    }
}
