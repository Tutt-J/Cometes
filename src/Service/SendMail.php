<?php

namespace App\Service;

use App\Entity\Content;
use App\Entity\PromoCode;
use App\Entity\Purchase;
use App\Entity\PurchaseContent;
use Doctrine\ORM\EntityManagerInterface;
use Konekt\PdfInvoice\InvoicePrinter;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SendMail
{
    /**
     * @var Security
     */
    private Security $security;
    /**
     * @var Mailer
     */
    private Mailer $mailer;


    /**
     * BasketAdministrator constructor.

     * @param EntityManagerInterface $em

     */
    public function __construct(EntityManagerInterface $em, MailerInterface $mailer,  Security $security)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->security = $security;
    }

    public function sendTemplated($document){
        $message = (new TemplatedEmail())
            ->from(new Address('postmaster@chamade.co', 'Chamade'))
            ->to($this->security->getUser()->getEmail())
            ->subject('Confirmation de commande')
            ->htmlTemplate('emails/purchase_confirm.html.twig')
            ->attachFromPath($document);
        $this->mailer->send($message);
    }

    public function sendBasicEmail($html){
        $emailAdmin = (new Email())
            ->from(new Address('postmaster@chamade.co', 'SITE WEB Chamade'))
            ->to('hello@chamade.co')
            ->subject('Nouvel achat sur le site')
            ->html($html);
        $this->mailer->send($emailAdmin);
    }
}
