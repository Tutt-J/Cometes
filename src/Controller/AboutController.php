<?php
namespace App\Controller;

use App\Entity\Article;
use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Team;
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
     * @Route("/a-propos/qui-suis-je", name="aboutStory")
     *
     * @return Response
     */
    public function storyAction()
    {
        $stephanie = $this->getDoctrine()
            ->getRepository(Author::class)
            ->findOneBy(
                ['id' => 5]
            );

        $salome = $this->getDoctrine()
            ->getRepository(Author::class)
            ->findOneBy(
                ['id' => 1]
            );
        return $this->render(
            'about/story.html.twig',
            [
                'stephanie' => $stephanie,
                'salome' => $salome,
            ]
        );
    }

    /**
     * @Route("/a-propos/equipe", name="aboutTeam")
     *
     * @return Response
     */
    public function teamAction(){
        $members = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findBy(
                [],
                ['createdAt' => 'ASC']
            );

        return $this->render('about/team.html.twig', [
            'members' => $members,
        ]);
    }

    /**
     * @Route("/a-propos/podcasts/{page}", name="aboutPodcasts")
     *
     * @param int $page
     * @param BlogView $blogView
     * @return Response
     */
    public function podcastsAction( BlogView $blogView, int $page = 1)
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
    public function pressAction(BlogView $blogView, int $page = 1)
    {
        $articles = $blogView->getArticlesByCategory('Presse', $page);
        return $this->render('about/press.html.twig', [
            'articles' => $articles,
        ]);
    }
}
