<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\TokenAuthenticator;
use App\Service\MailchimpAdministrator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RegistrationController
 * @package App\Controller
 */
class RegistrationController extends AbstractController
{
    /**
     * @Route("/inscription", name="app_register")
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param TokenAuthenticator $authenticator
     * @param MailchimpAdministrator $mailjetAdministrator
     * @param MailerInterface $mailer
     * @return Response
     * @throws TransportExceptionInterface
     */
    public function register(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        TokenAuthenticator $authenticator,
        MailchimpAdministrator $mailjetAdministrator,
        MailerInterface $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('subscribeNews')->getData()) {
                $addContact=$mailjetAdministrator->addContact($form->get('email')->getData());
                if ($addContact['status'] == 'subscribed') {
                    $this->addFlash('success', 'Votre inscription à la newsletter Comètes est effective !');
                } elseif ($addContact['title'] == 'Member Exists') {
                    $this->addFlash('info', 'Vous êtes déjà inscrit à notre newsletter');
                } else {
                    $this->addFlash('error', 'Un problème est survenu lors de votre inscription à la newsletter... ');
                }
            }

            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $message = (new TemplatedEmail())
                ->from(new Address('postmaster@cometes.co', 'Comètes'))
                ->to($user->getEmail())
                ->subject('Bienvenue chez Comètes !')
                ->htmlTemplate('emails/registration.html.twig')
            ;

            $mailer->send($message);

            $this->addFlash('success', 'Bienvenue chez Comètes '.$user->getFirstName().'! Votre compte a bien été créé');

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            )?: $this->redirectToRoute('app_account');
        }

        return $this->render(
            'registration/register.html.twig',
            [
            'form' => $form->createView(),
            ]
        );
    }
}
