<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Link;
use App\Form\Admin\CategoryType;
use App\Form\Admin\EventType;
use App\Form\LinkType;
use App\Service\Admin\AdminDatabase;
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
class LinksController extends AbstractController
{

    /**
     * @Route("/admin/liens", name="linksAdmin")
     *
     * @return Response
     */
    public function linksAction()
    {
        $links = $this->getDoctrine()
            ->getRepository(Link::class)
            ->findAll();

        return $this->render(
            'admin/links/links.html.twig',
            [
                'links' => $links,
            ]
        );
    }

    /**
     * @Route("/admin/liens/creer", name="createLinkAdmin")
     *
     * @param Request $request
     * @param AdminDatabase $adminDatabase
     * @return Response
     */
    public function createLink(Request $request, AdminDatabase $adminDatabase)
    {
        $link = new Link();

        $form = $this->createForm(LinkType::class, $link);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminDatabase->basic($form);

            $this->addFlash('success', 'Le lien a bien été créé.');

            return $this->redirectToRoute('linksAdmin');
        }


        return $this->render('admin/links/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/liens/{id}/modifier", name="updateLinkAdmin")
     *
     * @param Category $category
     * @param Request $request
     * @param AdminDatabase $adminDatabase
     * @return Response
     */
    public function updateLinks(Link $link, Request $request, AdminDatabase $adminDatabase)
    {
        $form = $this->createForm(LinkType::class, $link);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $adminDatabase->basic($form);

            $this->addFlash('success', 'Le lien a bien été mis à jour');

            return $this->redirectToRoute('linksAdmin');
        }

        return $this->render(
            'admin/links/update.html.twig',
            [
                'form' => $form->createView(),
            ]
        );

    }

    /**
     * @Route("/admin/liens/supprimer", name="deleteLinkAdmin")
     *
     * @return RedirectResponse
     */
    public function deleteLinks()
    {
        $entityManager = $this->getDoctrine()->getManager();

        $link = $this->getDoctrine()
            ->getRepository(Link::class)
            ->findOneBy(
                ['id' => $_POST['id']]
            );

        $entityManager->remove($link);


        $entityManager->flush();

        $this->addFlash('success', 'Le lien a bien été supprimée.');


        return $this->redirectToRoute('linksAdmin');
    }
}
