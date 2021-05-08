<?php
namespace App\Controller;

use App\Entity\Article;
use App\Entity\Author;
use App\Entity\Category;
use App\Service\BlogView;
use App\Service\GlobalsGenerator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AboutController
 * @package App\Controller
 */
class AboutController extends AbstractController
{
    /**
     * @Route("/a-propos/concept", name="aboutConcept")
     *
     * @param GlobalsGenerator $socialGenerator
     *
     * @return Response
     */
    public function aboutAction(GlobalsGenerator $socialGenerator)
    {
        return $this->render('about/concept.html.twig');
    }

    /**
     * @Route("/a-propos/notre-histoire", name="aboutStory")
     *
     * @param GlobalsGenerator $socialGenerator
     *
     * @return Response
     */
    public function storyAction(GlobalsGenerator $socialGenerator)
    {
        $author = $this->getDoctrine()
            ->getRepository(Author::class)
            ->findOneBy(
                ['id' => 1]
            );

        return $this->render(
            'about/story.html.twig',
            [
                'author' => $author,
            ]
        );
    }

    /**
     * @Route("/a-propos/avis-clientes", name="aboutOpinion")
     *
     * @return Response
     */
    public function opinionAction()
    {
        return $this->render('about/opinion.html.twig');
    }

    /**
     * @Route("/a-propos/podcasts/{page}", name="aboutPodcasts")
     *
     * @param int $page
     * @param BlogView $blogView
     * @return Response
     */
    public function podcastsAction( int $page = 1, BlogView $blogView)
    {

        $articles = $blogView->getArticlesByCategory('Podcasts', $page);

        return $this->render('about/podcasts.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/a-propos/presse/{page}", name="aboutPress")
     *
     * @param int $page
     * @param BlogView $blogView
     * @return Response
     */
    public function pressAction(int $page = 1, BlogView $blogView)
    {
        $articles = $blogView->getArticlesByCategory('Presse', $page);
        return $this->render('about/press.html.twig', [
            'articles' => $articles,
        ]);
    }
}
