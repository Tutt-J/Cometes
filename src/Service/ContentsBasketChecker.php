<?php

namespace App\Service;

use App\Entity\Purchase;
use App\Entity\PurchaseContent;
use App\Entity\Content;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ContentsBasketChecker
 * @package App\Service
 */
class ContentsBasketChecker
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;
    /**
     * @var object|string
     */
    private $user;

    /**
     * @var FlashBagInterface
     */
    private FlashBagInterface $flashbag;

    /**
     * @var SessionInterface
     */
    private SessionInterface $session;

    /**
     * ContentsBasketChecker constructor.
     * @param EntityManagerInterface $em
     * @param SessionInterface $session
     * @param TokenStorageInterface $tokenStorage
     * @param FlashBagInterface $flashbag
     */
    public function __construct(EntityManagerInterface $em, SessionInterface $session, TokenStorageInterface $tokenStorage, FlashBagInterface $flashbag)
    {
        $this->em = $em;
        $this->user = $tokenStorage->getToken()->getUser();
        $this->flashbag=$flashbag;
        $this->session = $session;
    }

    /**
     * Get all contents for a user
     *
     * @return object[]
     */
    public function getPurchases()
    {
        return $this->em
            ->getRepository(Purchase::class)
            ->findBy(
                array('user' => $this->user),
                array('createdAt' => "DESC")
            );
    }

    //Check si le rituel est déjà acheté

    public function checkPack($content, $contentToTest)
    {
        $contents = $this->em
            ->getRepository(Content::class)
            ->findBy(
                ['pack' => $content]
            );

        foreach ($contents as $content) {
            if ($contentToTest->getId() == $content->getId()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if content is already bought by a specific user
     *
     * @param Content $contentToTest
     * @return bool
     */
    public function checkContentAlreadyBuy(Content $contentToTest)
    {
        $return = true;
        $purchases=$this->getPurchases();
        foreach ($purchases as $purchase) {
            $purchaseContents = $this->em
                ->getRepository(PurchaseContent::class)
                ->findBy(
                    ['purchase' => $purchase]
                );

            foreach ($purchaseContents as $purchaseContent) {
                $content = $this->em
                    ->getRepository(Content::class)
                    ->findOneBy(
                        ['id' => $purchaseContent->getContent()]
                    );
                if ($content->getIsPack()) {
                    $return=$this->checkPack($content, $contentToTest);
                }
                if ($contentToTest->getId() == $content->getId()) {
                    $return=false;
                }

            }
        }
        return $return;
    }

    //Check si le contenu est online

    /**
     * Check if content is online
     *
     * @param Content $content
     * @return bool
     */
    public function checkContentIsOnline(Content $content)
    {
        if ($content->getIsOnline() == 1) {
            return true;
        }
        return false;
    }

    public function checkEventPassed(Content $content)
    {
        if ($content->getEventDate()->format('Y-m-d') >= date('Y-m-d') || $content->getNeverPassed() === true) {
            return true;
        }
        return false;
    }

    /**
     *
     * Check if content is online and not already buy
     *
     * @param Content $content
     * @return bool|int
     */
    public function checkContent(Content $content)
    {
        //check si online et link
        $isTrue=true;
        if (!$this->checkContentIsOnline($content)) {
            $this->flashbag->add('error', 'Le contenu "'.$content->getTitle().'" n\'est plus disponible à la vente, il a été supprimé de votre panier.');
            $isTrue=false;
        }
        //check if ritual is already buy
        if (!$this->checkContentAlreadyBuy($content)) {
            $this->flashbag->add('error', 'Vous avez déjà acheté le contenu "'.$content->getTitle().'", il a été supprimé de votre panier');
            $isTrue=false;
        }
        //chekc if to become
        if (!$this->checkEventPassed($content)) {
            $this->flashbag->add('error', 'Le contenu "'.$content->getTitle().'" est déjà passé, il a été supprimé de votre panier');
            $isTrue=false;
        }
        return $isTrue;
    }

    /**
     * @param int $id
     * @return object|null
     */
    public function getContent(int $id)
    {
        return $this->em
            ->getRepository(Content::class)
            ->findOneBy(
                ['id' => $id]
            );
    }
}
