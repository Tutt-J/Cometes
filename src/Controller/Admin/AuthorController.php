<?php
namespace App\Controller\Admin;

use App\Entity\Address;
use App\Entity\Article;
use App\Entity\Author;
use App\Form\Admin\ArticleType;
use App\Form\Admin\AuthorType;
use App\Service\Admin\AdminDatabase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class AuthorController
 * @package App\Controller
 *
 * @IsGranted("ROLE_ADMIN")
*/
class AuthorController extends AbstractController
{
    /**
     * @Route("/admin/auteurs", name="authorsAdmin")
     *
     * @return Response
     */
    public function authorsAction()
    {
        $authors= $this->getDoctrine()
            ->getRepository(Author::class)
            ->findBy(
                array(),
                array('name' => 'ASC')
            );

        return $this->render(
            'admin//author/authors.html.twig',
            [
                'authors' => $authors,
            ]
        );
    }

    /**
     * @Route("/admin/auteurs/creer", name="createAuthorAdmin")
     *
     * @param Request $request
     * @param AdminDatabase $adminDatabase
     * @return Response
     */
    public function createAuthor(Request $request, AdminDatabase $adminDatabase)
    {
        $article = new Author();

        $form = $this->createForm(AuthorType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $adminDatabase->basicWithImg($form);

            return $this->redirectToRoute('authorsAdmin');
        }


        return $this->render('admin/author/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/auteur/{id}/modifier", name="updateAuthorAdmin")
     *
     * @param $id
     * @param Request $request
     * @param AdminDatabase $adminDatabase
     * @return Response
     */
    public function updateAuthor($id, Request $request, AdminDatabase $adminDatabase)
    {
        $article = $this->getDoctrine()
            ->getRepository(Author::class)
            ->findOneBy(
                ['id' => $id]
            );

        $form = $this->createForm(AuthorType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminDatabase->basicWithImg($form);
            $this->addFlash('success', 'L\'auteur a bien été mis à jour');
            return $this->redirectToRoute('authorsAdmin');
        }

        return $this->render(
            'admin/author/update.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/auteur/supprimer", name="deleteAuthorAdmin")
     *
     * @return RedirectResponse
     */
    public function deleteAuthor()
    {
        if ($_POST['id'] == 4) {
            throw new AccessDeniedException("Vous ne pouvez pas supprimer cet auteur");
        }
        
        $author = $this->getDoctrine()
            ->getRepository(Author::class)
            ->findOneBy(
                ['id' => $_POST['id']]
            );

        $articles = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findBy(
                ['author' => $author]
            );

        $anonymeAuthor= $this->getDoctrine()
            ->getRepository(Author::class)
            ->findOneBy(
                ['id' => 4]
            );

        $entityManager = $this->getDoctrine()->getManager();

        foreach ($articles as $article) {
            $article->setAuthor($anonymeAuthor);
            $entityManager->persist($article);
        }

        $entityManager->remove($author);
        $entityManager->flush();

        return $this->redirectToRoute('authorsAdmin');
    }
}
