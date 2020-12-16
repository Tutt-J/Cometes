<?php
namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Keyword;
use App\Entity\PromoCode;
use App\Form\Admin\CategoryType;
use App\Form\Admin\KeywordType;
use App\Form\Admin\PromoCodeType;
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
    public function updatePromoCode(PromoCode $promoCode, Request $request, AdminDatabase $adminDatabase)
    {
            $form = $this->createForm(PromoCodeType::class, $promoCode);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                if($form->get('deleteAmount')->getData() <= $promoCode->getRestAmount()){
                    $adminDatabase->updatePromoCode($form, $promoCode);

                    $this->addFlash('success', 'Le montant du code a été mis à jour');

                    return $this->redirectToRoute('promoCodeAdmin');
                }

                $this->addFlash('error', 'Le montant à retirer ne peut pas être supérieur à celui disponible sur la carte');

            }

            return $this->render(
                'admin/promo_code/update.html.twig',
                [
                    'form' => $form->createView(),
                ]
            );
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
