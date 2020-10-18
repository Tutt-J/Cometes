<?php
namespace App\Controller;

use App\Entity\Program;
use App\Service\ContentOnlineAdministrator;
use App\Service\GlobalsGenerator;
use Instagram\Exception\InstagramAuthException;
use Instagram\Exception\InstagramException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class OnlineController extends AbstractController
{
    const INDEX_RENDER='online/content.html.twig';

    /**
     * @Route("/magie-en-ligne", name="homeOnline")
     *
     * @param GlobalsGenerator $socialGenerator
     * @return Response
     * @throws InstagramAuthException
     * @throws InstagramException
     * @throws InvalidArgumentException
     */
    public function homeAction(GlobalsGenerator $socialGenerator)
    {
        return $this->render('online/home.html.twig');
    }

    /**
     * @Route("/magie-en-ligne/rituels-en-ligne", name="ritualsOnline")
     *
     * @param int $page
     * @param ContentOnlineAdministrator $contentOnlineAdministrator
     * @return Response
     */
    public function RitualsAction(ContentOnlineAdministrator $contentOnlineAdministrator, int $page = 1)
    {
        return $this->render(
            'online/rituals_online.html.twig',
            [
                'contents' => $contentOnlineAdministrator->getContentsToBecome('ritual', $page),
                'pack' => $contentOnlineAdministrator->getContentsPack('ritual'),
            ]
        );
    }

    /**
     * @Route("/magie-en-ligne/rituels-en-ligne/{slug}",
     * name="ritualOnline",
     * requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     *
     * @param $slug
     * @param ContentOnlineAdministrator $contentOnlineAdministrator
     * @return Response
     */
    public function ritualAction($slug, ContentOnlineAdministrator $contentOnlineAdministrator)
    {
        return $this->render(
            SELF::INDEX_RENDER,
            $contentOnlineAdministrator->generateContent($slug, 'ritual')
        );
    }

    /**
     * @Route("/magie-en-ligne/yoga-en-ligne", name="yogasOnline")
     *
     * @param int $page
     * @param ContentOnlineAdministrator $contentOnlineAdministrator
     * @return Response
     */
    public function yogasAction(ContentOnlineAdministrator $contentOnlineAdministrator, int $page = 1)
    {
        return $this->render(
            'online/yoga_online.html.twig',
            [
                'contents' => $contentOnlineAdministrator->getContentsToBecome('yoga', $page)
            ]
        );
    }

    /**
     * @Route("/magie-en-ligne/yoga-en-ligne/{slug}",
     * name="yogaOnline",
     * requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     *
     * @param $slug
     * @param ContentOnlineAdministrator $contentOnlineAdministrator
     * @return Response
     */
    public function yogaAction($slug, ContentOnlineAdministrator $contentOnlineAdministrator)
    {
        return $this->render(
            SELF::INDEX_RENDER,
            $contentOnlineAdministrator->generateContent($slug, 'yoga')
        );
    }

    /**
     * @Route("/magie-en-ligne/e-books", name="eBooksOnline")
     *
     * @param ContentOnlineAdministrator $contentOnlineAdministrator
     * @param GlobalsGenerator $socialGenerator
     * @param int $page
     * @return Response
     * @throws InstagramAuthException
     * @throws InstagramException
     * @throws InvalidArgumentException
     */
    public function eBooksAction(ContentOnlineAdministrator $contentOnlineAdministrator, GlobalsGenerator $socialGenerator, int $page = 1)
    {
        return $this->render(
            'online/e_books.html.twig',
            [
                'contents' => $contentOnlineAdministrator->getContentsToBecome('eBook', $page),
            ]
        );
    }


    /**
     * @Route("/magie-en-ligne/e-books/{slug}",
     * name="eBookOnline",
     * requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     *
     * @param $slug
     * @param ContentOnlineAdministrator $contentOnlineAdministrator
     * @return RedirectResponse|Response
     */
    public function eBookAction($slug, ContentOnlineAdministrator $contentOnlineAdministrator)
    {
        return $this->render(
            SELF::INDEX_RENDER,
            $contentOnlineAdministrator->generateContent($slug, 'eBook')
        );
    }

    /**
     * @Route("/magie-en-ligne/podcasts", name="podcastsOnline")
     *
     * @return Response
     */
    public function podcastsAction()
    {
        return $this->render('online/podcasts.html.twig');
    }

    /**
     * @Route("/magie-en-ligne/ateliers-en-ligne", name="videosOnline")
     *
     * @param ContentOnlineAdministrator $contentOnlineAdministrator
     * @return Response
     */
    public function videosAction(ContentOnlineAdministrator $contentOnlineAdministrator)
    {
        return $this->render(
            'online/videos.html.twig',
            [
                'contents' => $contentOnlineAdministrator->getContentsToBecome('video')
            ]
        );
    }

    /**
     * @Route("/magie-en-ligne/ateliers-en-ligne/{slug}", name="videoOnline")
     * name="videoOnline",
     * requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     *
     * @param $slug
     * @param ContentOnlineAdministrator $contentOnlineAdministrator
     * @return RedirectResponse|Response
     */
    public function videoAction($slug, contentOnlineAdministrator $contentOnlineAdministrator)
    {
        return $this->render(
            SELF::INDEX_RENDER,
            $contentOnlineAdministrator->generateContent($slug, 'video')
        );
    }


    /**
     * @Route("/magie-en-ligne/programmes", name="programsOnline")
     *
     * @param GlobalsGenerator $socialGenerator
     * @param ContentOnlineAdministrator $contentOnlineAdministrator
     * @return Response
     * @throws InstagramAuthException
     * @throws InstagramException
     * @throws InvalidArgumentException
     */
    public function programsAction(GlobalsGenerator $socialGenerator, ContentOnlineAdministrator $contentOnlineAdministrator)
    {
        $programs= $this->getDoctrine()
            ->getRepository(Program::class)
            ->findBy(
                ['is_online' => 1]
            );
        return $this->render('online/programs.html.twig', [
            'programs' => $programs
        ]);
    }

    /**
     * @Route("/magie-en-ligne/programmes/{slug}",
     * name="programOnline",
     * requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     *
     * @param string $slug
     * @return Response
     */
    public function programAction(string $slug)
    {
        $program= $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy(
                [
                    'slug' => $slug,
                    'is_online' => 1
                ]
            );
        if (empty($program)) {
            throw new NotFoundHttpException('Le contenu n\'existe pas');
        }
        return $this->render(
            'online/program.html.twig',
            [
                'program' => $program,
            ]
        );
    }
}
