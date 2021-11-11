<?php
namespace App\Controller;

use App\Entity\Article;
use App\Entity\Content;
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
     * @Route("/magie-en-ligne/cartes-cadeaux", name="giftCardsOnline")
     *
     * @param int $page
     * @param ContentOnlineAdministrator $contentOnlineAdministrator
     * @return Response
     */
    public function giftCardsAction(SessionInterface $session, ContentOnlineAdministrator $contentOnlineAdministrator, int $page = 1)
    {
        if(isset($_GET['affiliate'])){
            $session->set('affiliateGift', $_GET['affiliate']);
        }

        $giftCards = $this->getDoctrine()
            ->getRepository(Content::class)
            ->findBy(
                [
                    'isOnline' => 1,
                    'type' => $contentOnlineAdministrator->getType('giftCard')
                ],
                ['price' => 'ASC']
            );

        return $this->render(
            'online/gift_card.html.twig',
            [
                'contents' => $giftCards,
            ]
        );
    }

    /**
     * @Route("/magie-en-ligne/cartes-cadeaux/{slug}",
     * name="giftCardOnline",
     * requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"})
     *
     * @param $slug
     * @param ContentOnlineAdministrator $contentOnlineAdministrator
     * @return Response
     */
    public function giftCardAction($slug, ContentOnlineAdministrator $contentOnlineAdministrator)
    {
        return $this->render(
            SELF::INDEX_RENDER,
            $contentOnlineAdministrator->generateContent($slug, 'giftCard')
        );
    }

    /**
     * @Route("/programmes/{slug}",
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
