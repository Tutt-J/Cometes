<?php

namespace App\Service\Admin;

use App\Entity\Category;
use App\Entity\Image;
use App\Entity\Keyword;
use App\Entity\EventPricing;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Class PostsEdit
 * @package App\Service
 */
class AdminDatabase
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;


    /**
     * @var FileUploader
     */
    protected FileUploader $fileUploader;

    /**
     * BasketAdministrator constructor.
     * @param FileUploader $fileUploader
     * @param EntityManagerInterface $em
     */
    public function __construct(FileUploader $fileUploader, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->fileUploader = $fileUploader;
    }


    /**
     * @param array $list
     * @param string $keyword
     * @param $article
     * @return void
     */
    public function setOnArticles(array $list, string $keyword, $article)
    {
        foreach ($list as $dbkeyword) {
            //Test if keyword exist in db, and if true just add it on the article
            if ($dbkeyword->getKeyword() == $keyword) {
                $article->addKeyword($dbkeyword);
            }
        }
    }

    /**
     * @param $articleKeywords
     * @param string $keyword
     * @param bool $keywords
     * @param $article
     * @return bool
     */
    public function changeKeywordState($articleKeywords, string $keyword, array $keywords, $article): bool
    {
        $state=false;
        foreach ($articleKeywords as $articleKeyword) {
            //Test if keyword is already add on this article before
            if ($articleKeyword->getKeyword() == $keyword) {
                $state = true;
            }
            //Delete the keyword if not in the list
            if (!in_array($articleKeyword->getKeyword(), $keywords)) {
                $article->removeKeyword($articleKeyword);
                $state = true;
            }
        }
        return $state;
    }

    /**
     * @param bool $state
     * @param string $keyword
     * @param $article
     */
    public function persistArticlesKeywords(bool $state, string $keyword, $article): void
    {
        //If the keyword is not add then create the keyword and add it to the article
        if (!$state) {
            $newKeyword = new Keyword();
            $newKeyword->setKeyword($keyword);
            $this->em->persist($newKeyword);
            $article->addKeyword($newKeyword);
        }
    }

    /**
     * @param $article
     * @param $keywords
     */
    public function setKeywords($article, $keywords)
    {
        $list=$this->em
            ->getRepository(Keyword::class)
            ->findAll();

        $articleKeywords=$article->getKeywords();
        $keywords = explode(",", $keywords);

        foreach ($keywords as $keyword) {
            $keyword=str_replace("#", "", trim($keyword));
            if (!empty($keyword)) {
                $this->setOnArticles($list, $keyword, $article);
                $state = $this->changeKeywordState($articleKeywords, $keyword, $keywords, $article);
                $this->persistArticlesKeywords($state, $keyword, $article);
            }
        }
    }

    /**
     * @param $article
     * @param $categoryNew
     */
    public function setCategory($article, $categoryNew)
    {
        if (!empty($categoryNew)) {
            $newCategory=new Category();
            $newCategory->setWording($categoryNew);
            $this->em->persist($newCategory);
            $article->setCategory($newCategory);
        }
    }

    /**
     * @param $form
     * @param $article
     */
    public function setImg($form, $article)
    {
        if($form->get('img')->getData()->getUrl() !== null){
            $img = $form->get('img')->get('url')->getData();
            if ($img) {
                $brochureFileName = $this->fileUploader->upload($img);
                $image=new Image();
                $image->setUrl($brochureFileName);
                $image->setAlt($form->get('img')->get('alt')->getData());
                $this->em->persist($image);
                $article->setImg($image);
            }
        }elseif($form->get('img')->get('existImage')->getData() !== null){
            $article->setImg($form->get('img')->get('existImage')->getData());
        }
    }

    /**
     * @param $date
     * @param $format
     * @return \DateTime|false
     */
    public function formatDate($date, $format)
    {
        return \DateTime::createFromFormat($format, $date);
    }

    /**
     * @param $form
     */
    public function post($form)
    {
        $article = $form->getData();
        $this->setKeywords($article, $form->get('keywords')->getData());
        $this->setCategory($article, $form->get('categoryNew')->getData());
        $this->setImg($form, $article);

        $this->em->persist($article);
        $this->em->flush();
    }


    /**
     * @param $form
     */
    public function program($form)
    {
        $program = $form->getData();

        $arrayButtons = $form->get('programButtons');

        if ($arrayButtons) {
            foreach ($arrayButtons->getData() as $button) {
                $button->setProgram($program);
                $this->em->persist($button);
            }
        }

        $this->setImg($form, $program);

        $this->em->persist($program);
        $this->em->flush();
    }

    /**
     * @param $form
     */
    public function online($form)
    {
        $content = $form->getData();

        $this->setImg($form, $content);
        $content->setEventDate($this->formatDate($form->get('eventDate')->getData(), 'd/m/Y'));
        $this->em->persist($content);
        $this->em->flush();
    }

    public function setMultiplePricing($form, $event)
    {
        $arrayPricing= $form->get('eventPricings');
        if ($arrayPricing) {
            foreach ($arrayPricing->getData() as $pricing) {
                $pricing->setEvent($event);
                $this->em->persist($pricing);
            }
        }
    }

    /**
     * @param $form
     */
    public function event($form)
    {
        $event = $form->getData();
        if($event->getPrice() == null && !empty($event->getEventPricings())){
            $event->setPrice(0);
        }
        $this->setImg($form, $event);
        $this->em->persist($event);
        $this->setMultiplePricing($form, $event);

        $this->em->flush();
    }

    /**
     * @param $form
     */
    public function basicWithImg($form)
    {
        $item = $form->getData();
        $this->setImg($form, $item);
        $this->em->persist($item);
        $this->em->flush();
    }

    /**
     * @param $form
     */
    public function basic($form)
    {
        $item = $form->getData();
        $this->em->persist($item);
        $this->em->flush();
    }

    public function updatePromoCode($form, $promoCode){

        $rest=$promoCode->getRestAmount()-$form->get('deleteAmount')->getData();
            $promoCode->setRestAmount($rest);
            $this->em->persist($promoCode);

        $this->em->flush();
    }
}
