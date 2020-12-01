<?php
namespace App\Controller\Admin;

use App\Entity\Article;
use App\Form\Admin\ArticleType;
use App\Service\Admin\AdminDatabase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class PostsController
 * @package App\Controller
 *
 * @IsGranted("ROLE_ADMIN")
*/
class PostsController extends AbstractController
{

    /**
     * @Route("/admin/articles", name="postsAdmin")
     *
     * @return Response
     */
    public function postsAction()
    {
        $articles = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findBy(
                array(),
                array('updatedAt' => 'DESC')
            );

        return $this->render(
            'admin/articles/articles.html.twig',
            [
                'articles' => $articles,
            ]
        );
    }

    /**
     * @Route("/admin/articles/creer", name="createPostAdmin")
     *
     * @param Request $request
     * @param AdminDatabase $adminDatabase
     * @return Response
     */
    public function createPost(Request $request, AdminDatabase $adminDatabase)
    {
        $article = new Article();

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $adminDatabase->post($form);
            $this->addFlash('success', 'L\'article a bien été créé.');

            return $this->redirectToRoute('postsAdmin');
        }


        return $this->render('admin/articles/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/articles/{id}/modifier", name="updatePostAdmin")
     *
     * @param Article $article
     * @param Request $request
     * @param AdminDatabase $adminDatabase
     * @return Response
     */
    public function updatePost(Article $article, Request $request, AdminDatabase $adminDatabase)
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminDatabase->post($form);
            $this->addFlash('success', 'L\'article a bien été mise à jour');
            return $this->redirectToRoute('postsAdmin');
        } elseif (!$form->isSubmitted()) {
            $keywords='';
            foreach ($article->getKeywords() as $key => $keyword) {
                $keywords.=$keyword;
                if ($key<sizeof($article->getKeywords())-1) {
                    $keywords.=',';
                }
            }
            $form->get('keywords')->setData($keywords);
        }

        return $this->render(
            'admin/articles/update.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/articles/supprimer", name="deletePostAdmin")
     *
     * @return RedirectResponse
     */
    public function deletePost()
    {
        $article = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findOneBy(
                ['id' => $_POST['id']]
            );
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($article);
        $entityManager->flush();

        $this->addFlash('success', 'L\'article a bien été supprimé.');

        return $this->redirectToRoute('postsAdmin');
    }
}
