<?php
namespace App\Controller;

use App\Entity\Article;
use App\Entity\Program;
use App\Entity\PromoCode;
use App\Entity\TypeProgram;
use App\Form\Admin\ArticleType;
use App\Service\ContentOnlineAdministrator;
use App\Service\GlobalsGenerator;
use http\Env\Request;
use Instagram\Exception\InstagramAuthException;
use Instagram\Exception\InstagramException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
     * @Route("/magie-en-ligne/cartes-cadeaux", name="giftCardOnline")
     *
     * @param int $page
     * @param ContentOnlineAdministrator $contentOnlineAdministrator
     * @return Response
     */
    public function giftCardAction(SessionInterface $session, ContentOnlineAdministrator $contentOnlineAdministrator, int $page = 1)
    {
        if(isset($_GET['affiliate'])){
            $session->set('affiliateGift', $_GET['affiliate']);
        }
        return $this->render(
            'online/gift_card.html.twig',
            [
                'contents' => $contentOnlineAdministrator->getContentsToBecome('giftCard', $page),
            ]
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
     * @return Response
     */
    public function programsAction()
    {
        $programs= $this->getDoctrine()
            ->getRepository(Program::class)
            ->findByType("Program");

        return $this->render('online/programs.html.twig', [
            'programs' => $programs
        ]);
    }

    /**
     * @Route("/magie-en-ligne/programmes/{slug}",
     * name="programOnline",
     * requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     *
     * @param Program $program
     * @return Response
     */
    public function programAction(Program $program)
    {
        if (empty($program) || !$program->getIsOnline()) {
            throw new NotFoundHttpException('Le programme que vous cherchez n\'existe pas ou plus.');
        }
        return $this->render(
            'online/program.html.twig',
            [
                'program' => $program,
            ]
        );
    }
}
