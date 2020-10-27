<?php
namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Keyword;
use App\Form\Admin\CategoryType;
use App\Form\Admin\KeywordType;
use App\Service\Admin\AdminDatabase;
use Instagram\Exception\InstagramAuthException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class CategoriesController
 * @package App\Controller
 *
 * @IsGranted("ROLE_ADMIN")
*/
class CategoriesController extends AbstractController
{

    /**
     * @Route("/admin/categories", name="categoriesAdmin")
     *
     * @return Response
     */
    public function categoriesAction()
    {
        $categories= $this->getDoctrine()
            ->getRepository(Category::class)
            ->findBy(
                array(),
                array('wording' => 'ASC')
            );

        return $this->render(
            'admin/categories/categories.html.twig',
            [
                'categories' => $categories,
            ]
        );
    }

    /**
     * @Route("/admin/categories/{id}/modifier", name="updateCategoryAdmin")
     *
     * @param $id
     * @param Request $request
     * @param AdminDatabase $adminDatabase
     * @return Response
     */
    public function updateCategory($id, Request $request, AdminDatabase $adminDatabase)
    {
        if ($id != 13) {
            $keyword = $this->getDoctrine()
                ->getRepository(Category::class)
                ->findOneBy(
                    ['id' => $id]
                );


            $form = $this->createForm(CategoryType::class, $keyword);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $adminDatabase->basic($form);

                $this->addFlash('success', 'La catégorie a bien été mise à jour');

                return $this->redirectToRoute('categoriesAdmin');
            }

            return $this->render(
                'admin/categories/update.html.twig',
                [
                    'form' => $form->createView(),
                ]
            );
        } else {
            return $this->redirectToRoute('categoriesAdmin');
        }
    }

    /**
     * @Route("/admin/categories/supprimer", name="deleteCategoryAdmin")
     *
     * @return RedirectResponse
     */
    public function deleteCategory()
    {
        if ($_POST['id'] == 13) {
            throw new AccessDeniedException("Vous ne pouvez pas supprimer cette catégorie");
        }
        $entityManager = $this->getDoctrine()->getManager();

            $category = $this->getDoctrine()
                ->getRepository(Category::class)
                ->findOneBy(
                    ['id' => $_POST['id']]
                );

            $articles=$category->getArticles();

            $undefinedCat=$this->getDoctrine()
                ->getRepository(Category::class)
                ->findOneBy(
                    ['id' => 13]
                );
            foreach ($articles as $article) {
                $article->setCategory($undefinedCat);
                $entityManager->persist($article);
            }

            $entityManager->remove($category);
            $entityManager->flush();

        return $this->redirectToRoute('categoriesAdmin');
    }
}
