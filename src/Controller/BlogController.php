<?php
// src/Controller/BlogController.php
namespace App\Controller;

use App\Entity\Article;
use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Keyword;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BlogController
 * @package App\Controller
 */
class BlogController extends AbstractController
{
    const INDEX_RENDER='blog/index.html.twig';

//    /**
//     * @Route("/journal/{page}", name="blogIndex")
//     *
//     * @param Request $request
//     * @param PaginatorInterface $paginator
//     * @param int $page
//     *
//     * @return Response
//     */
//    public function indexAction(Request $request, PaginatorInterface $paginator, int $page = 1)
//    {
//        $articles = $this->getDoctrine()
//            ->getRepository(Article::class)
//            ->findBy(
//                array('isOnline' => 1),
//                array('createdAt' => 'DESC')
//            );
//
//
//        $articles = $paginator->paginate(
//            $articles,
//            $request->query->getInt('page', $page),
//            9
//        );
//
//        return $this->render(
//            SELF::INDEX_RENDER,
//            [
//            'articles' => $articles,
//            'type' => 'all'
//            ]
//        );
//    }


    /**
     * @Route("/journal/article/{slug}",
     * name="blogArticle",
     * requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     *
     * @param Article $article
     * @return Response
     */
    public function articleAction(Article $article)
    {
        if (empty($article) || !$article->getIsOnline()) {
            throw new NotFoundHttpException('Cet article n\'existe pas');
        }

        $article_suggest = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findThreeByCategory($article);

        $article_before = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findPrev($article);

        $article_next = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findNext($article);

        return $this->render(
            'blog/article.html.twig',
            [
            'article' => $article,
            'suggests' => $article_suggest,
            'before' => $article_before,
            'next' => $article_next
            ]
        );
    }


//    /**
//     * @Route("/journal/auteur/{slug}",
//     * name="blogAuthor",
//     * requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"})
//     *
//     * @param Author $author
//     * @return Response
//     */
//    public function authorAction(Author $author)
//    {
//        $articles = $this->getDoctrine()
//            ->getRepository(Article::class)
//            ->findBy(
//                [
//                    'isOnline' => 1,
//                    'author' => $author
//                ],
//                [
//                    'createdAt' => "DESC"
//                ],
//                3
//            );
//
//        return $this->render(
//            'blog/author.html.twig',
//            [
//            'author' => $author,
//            'articles' => $articles
//            ]
//        );
//    }


    /**
     * @Route("/journal/categorie/{slug}/{page}",
     * name="blogCategory",
     * requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     *
     * @param Category $category
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param int $page
     *
     * @return Response
     */
    public function categoryAction(Category $category, Request $request, PaginatorInterface $paginator, int $page = 1)
    {
        $articles = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findBy(
                [
                    'isOnline' => 1,
                    'category' => $category
                ],
                ['createdAt' => "DESC"],
            );

        $articles = $paginator->paginate(
            $articles,
            $request->query->getInt('page', $page),
            9
        );

        return $this->render(
            SELF::INDEX_RENDER,
            [
            'articles' => $articles,
            'type' => 'cat',
            'cat' => $category
            ]
        );
    }


    /**
     * @Route("/journal/tag/{slug}/{page}",
     * name="blogTag",
     * requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     *
     * @param Keyword $keyword
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param int $page
     * @return Response
     */
    public function tagAction(Keyword $keyword, Request $request, PaginatorInterface $paginator, int $page = 1)
    {
        $articles = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findByKeyword($keyword);

        $articles = $paginator->paginate(
            $articles,
            $request->query->getInt('page', $page),
            9
        );

        return $this->render(
            SELF::INDEX_RENDER,
            [
            'articles' => $articles,
            'type' => 'tag',
            'tag' => $keyword
            ]
        );
    }
}
