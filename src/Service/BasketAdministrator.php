<?php

namespace App\Service;

use App\Entity\Content;
use App\Entity\PromoCode;
use App\Entity\Type;
use App\Entity\UserEvent;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Konekt\PdfInvoice\InvoicePrinter;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class BasketAdministrator
 * @package App\Service
 */
class BasketAdministrator
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

    private UserContentAdministrator $userContentAdministrator;

    /**
     * BasketAdministrator constructor.
     * @param EntityManagerInterface $em
     * @param Security $security
     * @param SessionInterface $session
     * @param ContentsBasketChecker $contentsBasketChecker
     * @param FlashBagInterface $flashbag
     * @param UserContentAdministrator $userContentAdministrator
     */
    public function __construct(
        EntityManagerInterface $em,
        Security $security,
        SessionInterface $session,
        ContentsBasketChecker $contentsBasketChecker,
        FlashBagInterface $flashbag,
        UserContentAdministrator $userContentAdministrator
    ) {
        $this->em = $em;
        $this->session = $session;
        $this->contentsBasketChecker = $contentsBasketChecker;
        $this->flashbag=$flashbag;
        $this->security=$security;
        $this->userContentAdministrator=$userContentAdministrator;
    }

    /**
     *
     */
    public function initializeSessions()
    {
        if (!is_array($this->session->get('basket'))) {
            $this->session->set('basket', []);
        }

        if (!is_array($this->session->get('purchaseInfos'))) {
            $this->session->set('purchaseInfos', array(
                "totalContent" => 0,
                "totalAmount" => 0,
            ));
        }
    }

    public function setPurchaseInfos()
    {
        $price=0;
        if (!empty($this->session->get('basket'))) {
            foreach ($this->session->get('basket') as $content) {
                if ($content['isFidelity']) {
                    $price += $content['Entity']->getFidelityPrice();
                } else {
                    $price += $content['Entity']->getPrice();
                }
            }
        }

        if(null !== $this->session->get('promoCode')){
            $priceWithCode=$price-$this->session->get('promoCode')->getRestAmount();
            if( $priceWithCode <0){
                $priceWithCode=0;
                $this->session->set('applyPromo', $price);
            } else{
                $this->session->set('applyPromo', $this->session->get('promoCode')->getRestAmount());
            }
            $price=$priceWithCode;
            $this->session->set('description', 'Réduction de '.$this->session->get('applyPromo').'€ avec la carte cadeau numéro '.$this->session->get('promoCode')->getCode());
        }

        $this->session->set('purchaseInfos', array(
            "totalContent" => sizeof($this->session->get('basket')),
            "totalAmount" => $price,
        ));
    }


    /**
     * @param int $id
     * @return object|null
     */
    public function getContent(int $id)
    {
        return $this->contentsBasketChecker->getContent($id);
    }

    /**
     * @param int $id
     * @return bool|null
     */
    public function addContent(int $id)
    {
        $this->initializeSessions();

        //Check if content exist
        if (!empty($this->getContent($id))) {
            //Check if content already on basket
            if (!empty($this->session->get('basket'))) {
                foreach ($this->session->get('basket') as $content) {
                    if ($content['Entity']->getid() == $id) {
                        $this->flashbag->add('error', 'Ce contenu se trouve déjà dans votre panier.');
                        return null;
                    }
                }
            }
            //Check content if is online, already buy or to become
            if ($this->contentsBasketChecker->checkContent($this->getContent($id))) {
                //Add content to basket
                return $this->addContentAction($id);
            }
        } else {
            $this->flashbag->add('error', 'Le contenu demandé n\'est pas trouvé');
        }
        return null;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function addContentAction(int $id)
    {
        //Create Content on basket
        $contentToAdd=$this->getContent($id);
        $array= array(
            'Entity' => $contentToAdd,
            'imageUrl' => $contentToAdd->getImg()->getUrl(),
            'imageAlt' => $contentToAdd->getImg()->getAlt(),
            'path' => $this->getContent($id)->getType()->getSlug().'Online',
            'isFidelity' => false
        );

        //Update session variables
        $sessionVal = $this->session->get('basket');
        array_push($sessionVal, $array);
        $this->session->set('basket', $sessionVal);
        $this->setPurchaseInfos();

        return true;
    }


    /**
     * @param int $id
     */
    public function removeContent(int $id)
    {
        $this->resetFidelity();

        $contentToRemove=$this->getContent($id);

        $val = $this->session->get('basket');

        //Delete item
        foreach ($val as $key => $content) {
            if ($content['Entity']->getId() == $contentToRemove->getId()) {
                unset($val[$key]);
                $val = array_values($val);
                $this->session->set('basket', $val);
            }
        }

        $this->setPurchaseInfos();
    }

    public function resetFidelity()
    {
        $val = $this->session->get('basket');
        if (!empty($val)) {
            foreach ($val as $key => $content) {
                $replace=array("isFidelity" => false);
                $array= array_replace($val[$key], $replace);
                $val[$key]=$array;
            }
        }

        $this->session->set('basket', $val);
        $this->setPurchaseInfos();
    }

    /**
     *
     */
    public function applyFidelity()
    {
        $this->resetFidelity();

        $types = $this->em
            ->getRepository(Type::class)
            ->findBy(
                ['forContent' => 1]
            );

        //initialize some vars and array
        $nbTypeDatabase=[];
        $nbTypeBasket=[];
        $classedContents=[];
        $nbFidelityToApply=[];
        foreach ($types as $type) {
            $nbTypeDatabase[$type->getSlug()]=0;
            $nbTypeBasket[$type->getSlug()]=0;
            $nbFidelityToApply[$type->getSlug()]=[];
        }

        //Get contents buy by user
        $userContents = $this->userContentAdministrator->getUserContents();

        //Get number of type in database
        foreach ($userContents as $content) {
            $nbTypeDatabase[$content->getContent()->getType()->getSlug()]=($nbTypeDatabase[$content->getContent()->getType()->getSlug()]+1)%3;
        }

        //Get number of type in basket
        foreach ($this->session->get('basket') as $content) {
            $nbTypeBasket[$content['Entity']->getType()->getSlug()]++;
        }

        //Calculate fidelity to applu for each type
        foreach ($types as $type) {
            $nbFidelityToApply[$type->getSlug()]=floor(($nbTypeBasket[$type->getSlug()]+(($nbTypeDatabase[$type->getSlug()])%3))/3);
            $classedContents[$type->getSlug()]=[];
        }

        //Set an array which classed backet contents by type
        foreach ($this->session->get('basket') as $content) {
            array_push($classedContents[$content['Entity']->getType()->getSlug()], $content);
        }


        //Apply fidelity
        foreach ($types as $type) {
            $classedContents[$type->getSlug()]=$this->constructFidelity($classedContents[$type->getSlug()], $nbFidelityToApply[$type->getSlug()]);
        }

        //Recreate the array for session variable
        if (isset($classedContents)) {
            $this->reformatArray($classedContents);
        }
    }

    public function constructFidelity($classedContents, $nbFidelityToApply)
    {
        //Replace isFidelity by true for all contents needed
        for ($x=0; $x < sizeof($classedContents); $x++) {
            if ($x<$nbFidelityToApply) {
                $replace=array("isFidelity" => true);
                $array= array_replace($classedContents[$x], $replace);
                $classedContents[$x]=$array;
                $classedContents=array_values($classedContents);
            }
        }

        //Sort the array
        return $this->sortArray($classedContents);
    }

    public function reformatArray($classedContents)
    {
        $value=[];
        foreach ($classedContents as $type) {
            foreach ($type as $item) {
                array_push($value, $item);
            }
        }
        $this->session->set('basket', $value);

        $this->setPurchaseInfos();
    }

    public function sortArray($classedContents)
    {
        uasort($classedContents, function ($b, $a) {
            if ($a['isFidelity'] === $b['isFidelity']) {
                return 0;
            }

            return $a['isFidelity'] > $b['isFidelity'] ? -1 : 1;
        });
        return array_values($classedContents);
    }

    /**
     * Check Content for every content on basket
     */
    public function checkBasket()
    {
        if (!empty($this->session->get('basket'))) {
            foreach ($this->session->get('basket') as $content) {
                if (!$this->contentsBasketChecker->checkContent($this->getContent($content['Entity']->getId()))) {
                    $this->removeContent($content['Entity']->getId());
                }
            }
        }
    }



    function verifyPromoCode($promoCode){
        $promoCode=$this->em
            ->getRepository(PromoCode::class)
            ->findOneBy(
                [
                    'code' => $promoCode
                ]
            );

        if($promoCode == null){
            $this->session->remove('promoCode');
            $this->session->remove('applyPromo');
            $this->flashbag->add('error', 'Le code promotionnel est invalide.');
        }
        $this->flashbag->add('success', 'Le code a été appliqué avec succès.');
        $this->session->set('promoCode', $promoCode);
    }



    /**
     * @param SessionInterface $session
     * @return array
     */
    public function formatItems(SessionInterface $session): array
    {
        $items = [];
        foreach ($this->session->get('basket') as $content) {
            if ($content['isFidelity']) {
                $price = $content['Entity']->getFidelityPrice();
            } else {
                $price = $content['Entity']->getPrice();
            }
            $array = [
                'name' => $content['Entity']->getTitle(),
                'amount' => $price * 100,
                'currency' => 'eur',
                'quantity' => 1,
            ];
            array_push($items, $array);
        }
        return $items;
    }
}
