<?php
namespace App\Controller;

use App\Entity\Author;
use App\Service\GlobalsGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
