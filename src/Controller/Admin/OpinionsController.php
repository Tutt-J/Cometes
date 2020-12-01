<?php
namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\Opinion;
use App\Form\Admin\EventType;
use App\Form\Admin\OpinionType;
use App\Service\Admin\AdminDatabase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class OpinionsController
 * @package App\Controller
 *
 * @IsGranted("ROLE_ADMIN")
*/
class OpinionsController extends AbstractController
{

    /**
     * @Route("/admin/temoignages", name="opinionsAdmin")
     *
     * @return Response
     */
    public function opinionsAction()
    {
        $articles = $this->getDoctrine()
            ->getRepository(Opinion::class)
            ->findBy(
                array(),
                array('client' => 'ASC')
            );
        return $this->render(
            'admin/opinions/opinions.html.twig',
            [
                'articles' => $articles,
            ]
        );
    }


    /**
     * @Route("/admin/temoignages/creer", name="createOpinionAdmin")
     *
     * @param Request $request
     * @param AdminDatabase $adminDatabase
     * @return Response
     */
    public function createOpinion(Request $request, AdminDatabase $adminDatabase)
    {
        $article = new Opinion();

        $form = $this->createForm(OpinionType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminDatabase->basic($form);
            $this->addFlash('success', 'Le témoignage a bien été créé.');

            return $this->redirectToRoute('opinionsAdmin');
        }


        return $this->render('admin/opinions/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/temoignages/{id}/modifier", name="updateOpinionAdmin")
     *
     * @param Opinion $opinion
     * @param Request $request
     * @param AdminDatabase $adminDatabase
     * @return Response
     */
    public function updateOpinion(Opinion $opinion, $request, AdminDatabase $adminDatabase)
    {
        $form = $this->createForm(OpinionType::class, $opinion);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminDatabase->basic($form);

            $this->addFlash('success', 'Le témoignage a bien été mise à jour');

            return $this->redirectToRoute('opinionsAdmin');
        }

        return $this->render(
            'admin/opinions/update.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/temoignage/supprimer", name="deleteOpinionAdmin")
     *
     * @return RedirectResponse
     */
    public function deleteOpinion()
    {
        $article = $this->getDoctrine()
            ->getRepository(Opinion::class)
            ->findOneBy(
                ['id' => $_POST['id']]
            );

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($article);
        $entityManager->flush();

        $this->addFlash('success', 'Le témoignage a bien été supprimée.');

        return $this->redirectToRoute('opinionsAdmin');
    }
}
