<?php
namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\Content;
use App\Entity\Program;
use App\Entity\ProgramCertified;
use App\Form\Admin\ArticleType;
use App\Form\Admin\ContentType;
use App\Form\Admin\ProgramType;
use App\Form\ProgramCertifiedType;
use App\Service\Admin\AdminDatabase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class ProgramsController
 * @package App\Controller
 *
 * @IsGranted("ROLE_ADMIN")
*/
class ProgramsController extends AbstractController
{

    /**
     * @Route("/admin/programmes", name="programsAdmin")
     *
     * @return Response
     */
    public function programsAction()
    {
        $contents= $this->getDoctrine()
            ->getRepository(Program::class)
            ->findBy(
                array(),
                array('updatedAt' => 'DESC')
            );

        return $this->render(
            'admin/programs/programs.html.twig',
            [
                'rituals' => $contents,
            ]
        );
    }

    /**
     * @Route("/admin/programme/creer", name="createProgramAdmin")
     *
     * @param Request $request
     * @param AdminDatabase $adminDatabase
     * @return Response
     */
    public function createProgram(Request $request, AdminDatabase $adminDatabase)
    {
        $content = new Program();

        $form = $this->createForm(ProgramType::class, $content);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $adminDatabase->program($form);

            $this->addFlash('success', 'Le programme a bien été créé.');

            return $this->redirectToRoute('programsAdmin');
        }


        return $this->render('admin/programs/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/programmes/{id}/modifier", name="updateProgramAdmin")
     *
     * @param Program $program
     * @param Request $request
     * @param AdminDatabase $adminDatabase
     * @return Response
     */
    public function updateProgram(Program $program, Request $request, AdminDatabase $adminDatabase)
    {

        $form = $this->createForm(ProgramType::class, $program);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminDatabase->program($form);
            $this->addFlash('success', 'Le programme a bien été mise à jour');
            return $this->redirectToRoute('programsAdmin');
        }

        return $this->render(
            'admin/programs/update.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/programmes/supprimer", name="deleteProgramAdmin")
     *
     * @return RedirectResponse
     */
    public function deletePost()
    {
        $article = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy(
                ['id' => $_POST['id']]
            );
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($article);
        $entityManager->flush();

        $this->addFlash('success', 'Le programme a bien été supprimée.');

        return $this->redirectToRoute('programsAdmin');
    }

    /**
     * @Route("/admin/programmes/{id}/certifiées", name="certifiedProgramAdmin")
     *
     * @param Program $program
     * @return Response
     */
    public function certifiedProgram(Program $program)
    {
        $certified= $this->getDoctrine()
            ->getRepository(ProgramCertified::class)
            ->findBy(
                array(
                    'program' => $program
                ),
                array('updatedAt' => 'DESC')
            );

        return $this->render(
            'admin/programs/list-certified.html.twig',
            [
                'program' => $program,
                'certified' => $certified,
            ]
        );
    }

    /**
     * @Route("/admin/programmes/{id}/certifiées/creer", name="createCertifiedProgramAdmin")
     *
     * @param Program $program
     * @param Request $request
     * @param AdminDatabase $adminDatabase
     * @return Response
     */
    public function createCertifiedProgram(Program $program, Request $request, AdminDatabase $adminDatabase)
    {
        $content = new ProgramCertified();

        $form = $this->createForm(ProgramCertifiedType::class, $content);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $content->setProgram($program);
            $adminDatabase->basicWithImg($form);

            $this->addFlash('success', 'La certifiée '. $program->getTitle() .' a bien été créé.');

            return $this->redirectToRoute('certifiedProgramAdmin', ['id' => $program->getId()]);
        }


        return $this->render('admin/programs/create-certified.html.twig', [
            'program' => $program,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/programmes/{id}/certifiées/{cert}/modifier", name="updateCertifiedProgramAdmin")
     *
     * @param Program $program
     * @param ProgramCertified $programCertified
     * @param Request $request
     * @param AdminDatabase $adminDatabase
     * @return Response
     */
    public function updateCertifiedProgram(Program $program, ProgramCertified $programCertified, Request $request, AdminDatabase $adminDatabase)
    {

        $form = $this->createForm(ProgramCertifiedType::class, $programCertified);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminDatabase->basicWithImg($form);
            $this->addFlash('success', 'La certifiée '. $program->getTitle() .' a bien été mise à jour.');
            return $this->redirectToRoute('certifiedProgramAdmin', ['id' => $program->getId()]);
        }

        return $this->render(
            'admin/programs/update-certified.html.twig',
            [
                'program' => $program,

                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/programmes/certifiées/supprimer", name="deleteCertifiedProgramAdmin")
     *
     * @return RedirectResponse
     */
    public function deleteCertifiedProgramPost()
    {
        $programCertified = $this->getDoctrine()
            ->getRepository(ProgramCertified::class)
            ->findOneBy(
                ['id' => $_POST['id']]
            );
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($programCertified);
        $entityManager->flush();

        $this->addFlash('success', 'La certifiée a bien été supprimée.');

        return $this->redirectToRoute('certifiedProgramAdmin', ['id' => $programCertified->getProgram()->getId()]);
    }
}
