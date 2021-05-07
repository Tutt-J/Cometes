<?php

namespace App\Service;

use App\Entity\Purchase;
use App\Entity\PurchaseContent;
use App\Entity\Content;
use App\Entity\Type;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ContentOnlineAdministrator
 * @package App\Service
 */
class ContentOnlineAdministrator
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
     * @var SessionInterface
     */
    private SessionInterface $session;


    /**
     * @var RequestStack
     */
    protected RequestStack $requestStack;

    /**
     * @var PaginatorInterface
     */
    private PaginatorInterface $paginator;


    /**
     * ContentOnlineAdministrator constructor.
     * @param EntityManagerInterface $em
     * @param SessionInterface $session
     * @param RequestStack $requestStack
     * @param PaginatorInterface $paginator
     */
    public function __construct(EntityManagerInterface $em, SessionInterface $session, RequestStack $requestStack, PaginatorInterface $paginator)
    {
        $this->em = $em;
        $this->session = $session;
        $this->requestStack = $requestStack;
        $this->paginator=$paginator;
    }

    /**
     * @param $slug
     * @return object[]
     */
    public function getType($slug)
    {
        return $this->em
            ->getRepository(Type::class)
            ->findBy(
                array(
                    'slug' => $slug
                ),
                array('id' => 'DESC')
            );
    }

    /**
     * @param $slug
     * @return mixed
     */
    public function getContentsToBecome($slug)
    {
        $type=$this->getType($slug);

        return $this->em
            ->getRepository(Content::class)
            ->findToBecome($type, 0);
    }

    /**
     * @param $slug
     * @return mixed
     */
    public function getContentsPack($slug)
    {
        $type=$this->em
            ->getRepository(Type::class)
            ->findBy(
                array(
                    'slug' => $slug,
                ),
                array('id' => 'DESC')
            );

        return $this->em
            ->getRepository(Content::class)
            ->findOneBy(
                array(
                    'isPack'=>1,
                    'type' => $type
                )
            );
    }

    /**
     * @param $slug
     * @param $type
     * @return mixed
     */
    public function generateContent($slug, $type)
    {
        $content = $this->em
            ->getRepository(Content::class)
            ->findOne($slug);


        if (is_null($content)) {
            throw new NotFoundHttpException('Ce contenu n\'existe pas ou n\'est plus disponible à la vente');
        }


        $this->session->set('referer', array(
            'path' => $type.'Online' ,
            'attributes' =>$this->requestStack->getCurrentRequest()->attributes->get('_route_params')
        ));

        return [
                'path' => $type,
                'content' => $content,
            ];
    }

    /*/////////////////////////////////////////////*/
    /* Functions to get bought content from a user*/
    /*/////////////////////////////////////////////*/

    /**
     * @param Purchase $purchase
     * @return array
     */
    public function getUserContents(Purchase $purchase)
    {
        $purchaseContents=$this->getPurchaseContent($purchase);

        $array=[];
        foreach ($purchaseContents as $purchaseContent) {
            $value=$this->em
                ->getRepository(Content::class)
                ->findBy(
                    array('id' => $purchaseContent->getContent)
                );
            array_push($array, $value[0]);
        }
        return $array;
    }

    /**
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


    /**
     * @param Purchase $purchase
     * @return object|null
     */
    public function getPurchaseContent(Purchase $purchase)
    {
        return $this->em
            ->getRepository(PurchaseContent::class)
            ->findBy(
                array('purchase' => $purchase),
            );
    }
}
