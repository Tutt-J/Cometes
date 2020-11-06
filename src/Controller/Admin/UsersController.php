<?php
namespace App\Controller\Admin;

use App\Entity\Address;
use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Opinion;
use App\Entity\Content;
use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\UserType;
use App\Form\UserUpdateAdminType;
use App\Service\Admin\ReportGenerator;
use App\Service\UsersHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class UsersController
 * @package App\Controller
 *
 * @IsGranted("ROLE_ADMIN")
 */
class UsersController extends AbstractController
{

    /**
     * @Route("/admin/utilisateurs", name="usersAdmin")
     *
     * @return Response
     */
    public function usersAction()
    {
        $users= $this->getDoctrine()
            ->getRepository(User::class)
            ->findBy(
                array(),
                array('username' => 'ASC')
            );

        return $this->render(
            'admin/users/users.html.twig',
            [
                'users' => $users,
            ]
        );
    }

    /**
     * @Route("/admin/utilisateurs/creer", name="createUserAdmin")
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function createUser(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newUser = $form->getData();

            $newUser->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($newUser);
            $entityManager->flush();

            $this->addFlash('success', 'L\'utilisateur a bien été créé.');

            return $this->redirectToRoute('usersAdmin');
        }


        return $this->render('admin/users/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/utilisateurs/{id}/modifier", name="updateUserAdmin")
     *
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function updateUser($id, Request $request)
    {
        $article = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(
                ['id' => $id]
            );


        $form = $this->createForm(UserUpdateAdminType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'L\'utilisateur a bien été mis à jour');

            return $this->redirectToRoute('usersAdmin');
        }

        return $this->render(
            'admin/users/update.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/utilisateurs/{id}/changer-le-mot-de-passe", name="updatePasswordUserAdmin")
     *
     * @param $id
     * @param Request $request
     * @param UsersHelper $usersHelper
     * @return Response
     */
    public function updatePasswordUser($id, Request $request, UsersHelper $usersHelper)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(
                ['id' => $id]
            );

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $usersHelper->reset($form, $user, 'usersAdmin');
        }
        return $this->render(
            'admin/users/change_password.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }


    /**
     * @Route("/admin/utilisateur/supprimer", name="deleteUserAdmin")
     *
     * @param UsersHelper $usersHelper
     * @return RedirectResponse
     */
    public function deleteUser(UsersHelper $usersHelper)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(
                ['id' => $_POST['id']]
            );

        $usersHelper->delete($user);
        $this->addFlash('success', 'Le compte a été désactivé et les données ont été anonymisées.');
        return $this->redirectToRoute('usersAdmin');
    }
}
