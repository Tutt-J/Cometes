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

    /**
     * @Route("/journal/{page}", name="blogIndex")
     *
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param int $page
     *
     * @return Response
     */
    public function indexAction(Request $request, PaginatorInterface $paginator, int $page = 1)
    {
        $articles = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findBy(
                array(),
                array('createdAt' => 'DESC')
            );


        $articles = $paginator->paginate(
            $articles,
            $request->query->getInt('page', $page),
            6
        );

        return $this->render(
            SELF::INDEX_RENDER,
            [
            'articles' => $articles,
            'type' => 'all'
            ]
        );
    }


    /**
     * @Route("/journal/article/{slug}",
     * name="blogArticle",
     * requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     *
     * @param string $slug
     *
     * @return Response
     */
    public function articleAction(string $slug)
    {
        $article = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findOneBy(
                ['slug' => $slug]
            );

        if (empty($article)) {
            throw new NotFoundHttpException('Cet article n\'existe pas');
        }

        $article_suggest = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findThreeByCategory($article);

        $article_before = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findOneBy(
                ['id' => $article->getId()-1]
            );

        $article_next = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findOneBy(
                ['id' => $article->getId()+1]
            );

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


    /**
     * @Route("/journal/auteur/{slug}",
     * name="blogAuthor",
     * requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     *
     * @param string $slug
     *
     * @return Response
     */
    public function authorAction(string $slug)
    {
        $author = $this->getDoctrine()
            ->getRepository(Author::class)
            ->findOneBy(
                ['slug' => $slug]
            );

        return $this->render(
            'blog/author.html.twig',
            [
            'author' => $author,
            'articles' => $author->getArticles()->slice(0, 3)
            ]
        );
    }


    /**
     * @Route("/journal/categorie/{slug}/{page}",
     * name="blogCategory",
     * requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     *
     * @param string $slug
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param int $page
     *
     * @return Response
     */
    public function categoryAction(string $slug, Request $request, PaginatorInterface $paginator, int $page = 1)
    {
        $cat = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findOneBy(
                ['slug' => $slug]
            );


        $articles = $paginator->paginate(
            $cat->getArticles(),
            $request->query->getInt('page', $page),
            9
        );

        return $this->render(
            SELF::INDEX_RENDER,
            [
            'articles' => $articles,
            'type' => 'cat',
            'cat' => $cat
            ]
        );
    }


    /**
     * @Route("/journal/tag/{slug}/{page}",
     * name="blogTag",
     * requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     *
     * @param string $slug
     *
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param int $page
     * @return Response
     */
    public function tagAction(string $slug, Request $request, PaginatorInterface $paginator, int $page = 1)
    {
        $tag = $this->getDoctrine()
            ->getRepository(Keyword::class)
            ->findOneBy(
                ['slug' => $slug]
            );

        $articles = $paginator->paginate(
            $tag->getArticles(),
            $request->query->getInt('page', $page),
            9
        );

        return $this->render(
            SELF::INDEX_RENDER,
            [
            'articles' => $articles,
            'type' => 'tag',
            'tag' => $tag
            ]
        );
    }
}
