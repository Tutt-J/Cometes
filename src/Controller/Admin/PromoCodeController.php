<?php
namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Keyword;
use App\Entity\PromoCode;
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
class PromoCodeController extends AbstractController
{

    /**
     * @Route("/admin/codes-promo", name="promoCodeAdmin")
     *
     * @return Response
     */
    public function promoCodeAction()
    {
        $codes= $this->getDoctrine()
            ->getRepository(PromoCode::class)
            ->findAll();

        return $this->render(
            'admin/promo_code/promo_code.html.twig',
            [
                'codes' => $codes
            ]
        );
    }

    /**
     * @Route("/admin/promo-code/{id}/modifier", name="updatePromoCodeAdmin")
     *
     * @param Category $category
     * @param Request $request
     * @param AdminDatabase $adminDatabase
     * @return Response
     */
    public function updatePromoCode(Category $category, Request $request, AdminDatabase $adminDatabase)
    {
        if ($category->getId() != 13) {
            $form = $this->createForm(CategoryType::class, $category);

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
     * @Route("/admin/code-promo/supprimer", name="deletePromoCodeAdmin")
     *
     * @return RedirectResponse
     */
    public function deleteCodePromo()
    {
        $entityManager = $this->getDoctrine()->getManager();

        $code = $this->getDoctrine()
            ->getRepository(PromoCode::class)
            ->findOneBy(
                ['id' => $_POST['id']]
            );


        $entityManager->remove($code);
        $entityManager->flush();

        $this->addFlash('success', 'Le code promo a bien été supprimé.');

        return $this->redirectToRoute('promoCodeAdmin');
    }
}
