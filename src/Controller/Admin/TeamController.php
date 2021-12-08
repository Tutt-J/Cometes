<?php
namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\Author;
use App\Entity\Team;
use App\Form\Admin\TeamType;
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
class TeamController extends AbstractController
{
    /**
     * @Route("/admin/equipe", name="teamAdmin")
     *
     * @return Response
     */
    public function authorsAction()
    {
       $members = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findBy(
                array(),
                array('name' => 'ASC')
            );

        return $this->render(
            'admin/team/list.html.twig',
            [
                'members' => $members,
            ]
        );
    }

    /**
     * @Route("/admin/equipe/ajouter-un-membre", name="createMemberTeamAdmin")
     *
     * @param Request $request
     * @param AdminDatabase $adminDatabase
     * @return Response
     */
    public function createTeamMember(Request $request, AdminDatabase $adminDatabase)
    {
        $member = new Team();

        $form = $this->createForm(TeamType::class, $member);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $adminDatabase->basicWithImg($form);
            $this->addFlash('success', 'Le membre a bien été créé.');

            return $this->redirectToRoute('teamAdmin');
        }


        return $this->render('admin/team/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/equipe/{id}/modifier", name="updateMemberTeamAdmin")
     *
     * @param Team $member
     * @param Request $request
     * @param AdminDatabase $adminDatabase
     * @return Response
     */
    public function updateMemberTeamAdmin(Team $member, Request $request, AdminDatabase $adminDatabase)
    {
        $form = $this->createForm(TeamType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminDatabase->basicWithImg($form);
            $this->addFlash('success', 'Le membre a bien été mis à jour');
            return $this->redirectToRoute('teamAdmin');
        }

        return $this->render(
            'admin/team/update.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/equipe/supprimer", name="deleteTeamMember")
     *
     * @return RedirectResponse
     */
    public function deleteTeamMember()
    {
        $member = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findOneBy(
                ['id' => $_POST['id']]
            );


        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->remove($member);
        $entityManager->flush();

        $this->addFlash('success', 'Le membre a bien été supprimé.');


        return $this->redirectToRoute('teamAdmin');
    }
}
