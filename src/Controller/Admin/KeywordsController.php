<?php
namespace App\Controller\Admin;

use App\Entity\Keyword;
use App\Form\Admin\KeywordType;
use App\Service\Admin\AdminDatabase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class KeywordsController
 * @package App\Controller
 *
 * @IsGranted("ROLE_ADMIN")
*/
class KeywordsController extends AbstractController
{

    /**
     * @Route("/admin/hashtags", name="keywordsAdmin")
     *
     * @return Response
     */
    public function keywordsAction()
    {
        $keywords= $this->getDoctrine()
            ->getRepository(Keyword::class)
            ->findBy(
                array(),
                array('keyword' => 'ASC')
            );

        return $this->render(
            'admin/keywords/keywords.html.twig',
            [
                'keywords' => $keywords,
            ]
        );
    }

    /**
     * @Route("/admin/hashtags/{id}/modifier", name="updateKeywordAdmin")
     *
     * @param $id
     * @param Request $request
     * @param AdminDatabase $adminDatabase
     * @return Response
     */
    public function updateKeyword($id, Request $request, AdminDatabase $adminDatabase)
    {
        $keyword = $this->getDoctrine()
            ->getRepository(Keyword::class)
            ->findOneBy(
                ['id' => $id]
            );


        $form = $this->createForm(KeywordType::class, $keyword);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $adminDatabase->basic($form);

            $this->addFlash('success', 'Le hashtag a bien été mis à jour');

            return $this->redirectToRoute('keywordsAdmin');
        }

        return $this->render(
            'admin/keywords/update.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/hashtags/supprimer", name="deleteKeywordAdmin")
     *
     * @return RedirectResponse
     */
    public function deleteKeyword()
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $keyword = $this->getDoctrine()
            ->getRepository(Keyword::class)
            ->findOneBy(
                ['id' => $_POST['id']]
            );

        $articles=$keyword->getArticles();

        foreach ($articles as $article) {
            $article->removeKeyword($keyword);
            $entityManager->persist($article);
        }

        $entityManager->remove($keyword);
        $entityManager->flush();

        return $this->redirectToRoute('keywordsAdmin');
    }
}
