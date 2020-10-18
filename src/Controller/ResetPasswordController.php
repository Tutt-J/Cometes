<?php

namespace App\Controller;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\ResetPasswordRequestType;
use App\Service\UsersHelper;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ResetPasswordController
 * @package App\Controller
 */
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    /**
     * @var ResetPasswordHelperInterface
     */
    private ResetPasswordHelperInterface $resetPasswordHelper;

    /**
     * ResetPasswordController constructor.
     * @param ResetPasswordHelperInterface $resetPasswordHelper
     */
    public function __construct(ResetPasswordHelperInterface $resetPasswordHelper)
    {
        $this->resetPasswordHelper = $resetPasswordHelper;
    }

    /**
     * Display & process form to request a password reset.
     *
     * @Route("/mot-de-passe-oublie", name="app_forgot_password_request")
     * @param Request $request
     * @param MailerInterface $mailer
     * @return Response
     * @throws TransportExceptionInterface
     */
    public function request(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ResetPasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData(),
                $mailer
            );
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    /**
     * Confirmation page after a user has requested a password reset.
     *
     * @Route("/mot-de-passe-oublie/verification-e-mail", name="app_check_email")
     */
    public function checkEmail(): Response
    {
        // We prevent users from directly accessing this page
        if (!$this->canCheckEmail()) {
            return $this->redirectToRoute('app_forgot_password_request');
        }

        return $this->render('reset_password/check_email.html.twig', [
            'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime(),
        ]);
    }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     *
     * @Route("/nouveau-mot-de-passe/{token}", name="app_reset_password")
     * @param Request $request
     * @param string|null $token
     * @param UsersHelper $usersHelper
     * @return Response
     */
    public function reset(Request $request, UsersHelper $usersHelper, string $token = null): Response
    {
        if ($token) {
            // We store the token in session and remove it from the URL, to avoid the URL being
            // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('error', 'Un problème est survenu, veuillez réiterer votre demande de mot de passe.');

            return $this->redirectToRoute('app_forgot_password_request');
        }

        // The token is valid; allow the user to change their password.
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->resetPasswordHelper->removeResetRequest($token);
            return $usersHelper->reset($form, $user, 'app_account');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    /**
     * @param string $emailFormData
     * @param MailerInterface $mailer
     * @return RedirectResponse
     * @throws TransportExceptionInterface
     */
    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer): RedirectResponse
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);


        // Marks that you are allowed to see the app_check_email page.
        $this->setCanCheckEmailInSession();

        // Do not reveal whether a user account was found or not.
        if (!$user || in_array('ROLE_DISABLE', $user->getRoles())) {
            $this->addFlash('error', 'Aucun compte n\'existe avec cette adresse e-mail');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        $token= $this->getDoctrine()->getRepository(ResetPasswordRequest::class)->findOneBy([
            'user' => $user,
        ]);

        if ($token) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($token);
            $em->flush();
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('error', 'Un problème est survenu, veuillez réiterer votre demande de mot de passe.');

            return $this->redirectToRoute('app_forgot_password_request');
        }

        $url = $this->generateUrl('app_reset_password', array('token' => $resetToken->getToken()), UrlGeneratorInterface::ABSOLUTE_URL);

        $message = (new TemplatedEmail())
            ->from(new Address('hello@chamade.co', 'Chamade'))
            ->to($user->getEmail())
            ->subject('Mot de passe oublié')
            ->htmlTemplate('emails/forgot_password.html.twig')
            ->context([
                'url' => $url
            ])
        ;

        $mailer->send($message);

        return $this->redirectToRoute('app_check_email');
    }
}
