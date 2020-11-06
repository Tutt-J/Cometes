<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Purchase;
use App\Entity\PurchaseContent;
use App\Entity\Content;
use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelper;

/**
 * Class ContentOnlineAdministrator
 * @package App\Service
 */
class UsersHelper
{

    /**
     * @var RequestStack
     */
    protected RequestStack $requestStack;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var UserPasswordEncoderInterface
     */
    protected UserPasswordEncoderInterface $passwordEncoder;

    /**
     * @var UrlGeneratorInterface
     */
    protected UrlGeneratorInterface $router;

    /**
     * ContentOnlineAdministrator constructor.
     * @param RequestStack $requestStack
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $router
     */
    public function __construct(RequestStack $requestStack, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em, UrlGeneratorInterface $router)
    {
        $this->em = $em;
        $this->passwordEncoder=$passwordEncoder;
        $this->requestStack = $requestStack;
        $this->router= $router;
    }

    /**
     * @param $form
     * @param $user
     * @param $path
     * @param null $token
     * @return mixed
     */
    public function reset($form, $user, $path)
    {
        // Encode the plain password, and set it.
        $encodedPassword = $this->passwordEncoder->encodePassword(
            $user,
            $form->get('plainPassword')->getData()
        );

        $user->setPassword($encodedPassword);
        $this->em->flush();

        $this->addFlash('success', 'Le mot de passe a bien été mis à jour.');


        return new RedirectResponse($this->router->generate($path));
    }

    public function delete($user)
    {
        //rm adrese
        $address = $this->em
            ->getRepository(Address::class)
            ->findOneBy(
                ['id' => $user->getAddress()]
            );

        $token = $this->em
            ->getRepository(ResetPasswordRequest::class)
            ->findOneBy(
                ['user' => $user]
            );

        $this->em->remove($address);
        if ($token) {
            $this->em->remove($token);
        }

        $user->setEmail('anonymous'.$user->getId().'@chamade.co');
        $user->setRoles(array('ROLE_DISABLE'));
        $user->setUsername('Anonyme'.$user->getId());
        $user->setFirstName('Anonyme'.$user->getId());
        $user->setLastName('Anonyme'.$user->getId());
        $user->setAddress(null);

        $this->em->persist($user);
        $this->em->flush();
    }
}
