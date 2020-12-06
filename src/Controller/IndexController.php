<?php
// src/Controller/IndexController.php
namespace App\Controller;

use App\Form\ContactType;
use App\Service\EventsAdministrator;
use App\Service\MailchimpAdministrator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class IndexController
 * @package App\Controller
 */
class IndexController extends AbstractController
{
    /**
     * View Homepage
     *
     * @Route("/", name="home")
     *
     * @param EventsAdministrator $eventAdministrator
     * @return Response
     */
    public function indexAction(EventsAdministrator $eventAdministrator)
    {
        $events=$eventAdministrator->getThreeNextEvents();

        return $this->render(
            'index/index.html.twig',
            [
            'events' => $events,
            ]
        );
    }

    /**
     * View Homepage
     *
     * @Route("/inscription-newsletter", name="registerNewsletter")
     * @param MailchimpAdministrator $mailchimpAdministrator
     * @param Request $request
     * @return RedirectResponse
     */
    public function registerNewsletter(MailchimpAdministrator $mailchimpAdministrator, Request $request)
    {
        if (isset($_POST['subscribe'])
            && isset($_POST['check1'])
            && isset($_POST['check2'])
            && isset($_POST['email'])
        ) {
            $addContact=$mailchimpAdministrator->addContact($_POST['email']);
            if ($addContact['status'] == 'subscribed') {
                $this->addFlash('success', 'Votre inscription à la newsletter chamade est effective !');
            } elseif (isset($addContact['title']) && $addContact['title'] == 'Member Exists') {
                $this->addFlash('info', 'Vous êtes déjà inscrit à notre newsletter');
            } else {
                $this->addFlash('error', 'Un problème est survenu lors de votre inscription à la newsletter... ');
            }
        }

        $url = $request->headers->get('referer');

        return $this->redirect($url);
    }

    /**
     * @Route("/contact", name="indexContact")
     *
     * @param Request $request
     * @param MailerInterface $mailer
     * @return Response
     * @throws TransportExceptionInterface
     */
    public function contactAction(Request $request, MailerInterface $mailer)
    {
        $form = $this->createForm(ContactType::class, null, array(
        // Time protection
        'antispam_time'     => false,

        // Honeypot protection
        'antispam_honeypot'       => true,
        'antispam_honeypot_class' => 'd-none',
        'antispam_honeypot_field' => 'email',
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = (new Email())
                ->from(new Address('postmaster@chamade.co', 'Chamade'))
                ->to('hello@chamade.co')
                ->subject('[Site web] '. $form->get('object')->getData())
                ->replyTo($form->get('afield')->getData())
                ->html('
                    <p>Nom : '.$form->get('name')->getData().'</p>
                    <p>Email : <a href="mailto:'.$form->get('afield')->getData().'">'.$form->get('afield')->getData().'</a></p>
                    <p>Objet : '.$form->get('object')->getData().'</p>
                    <p>Message : '.$form->get('message')->getData().'</p>
                ');


            $mailer->send($email);

            unset($form);

            $form = $this->createForm(ContactType::class, null, array(
                // Time protection
                'antispam_time'     => false,

                // Honeypot protection
                'antispam_honeypot'       => true,
                'antispam_honeypot_class' => 'd-none',
                'antispam_honeypot_field' => 'email',
            ));

            $this->addFlash('success', 'Votre message a bien été envoyé. Nous vous répondrons au plus vite !');
        }
        return $this->render('index/contact.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/mentions-legales", name="legalNotice")
     *
     * @return Response
     */
    public function legalNoticeAction()
    {
        return $this->render('index/legal_notice.html.twig');
    }


    /**
     * @Route("politique-des-cookies-ue", name="cookieNotice")
     *
     * @return Response
     */
    public function cookieNoticeAction()
    {
        return $this->render('index/cookie_notice.html.twig');
    }


    /**
     * @Route("politique-de-confidentialite", name="privacyPolicy")
     *
     * @return Response
     */
    public function privacyPolicyAction()
    {
        return $this->render('index/privacy_policy.html.twig');
    }


    /**
     * @Route("conditions-generales-de-vente", name="TOSales")
     *
     * @return Response
     */
    public function TOSalesAction()
    {
        return $this->render('index/tos_sales.html.twig');
    }

    /**
     * @Route("conditions-particulieres-pour-les-evenements-et-sejours", name="TOEvent")
     *
     * @return Response
     */
    public function TOEventAction()
    {
        return $this->render('index/tos_event.html.twig');
    }

    /**
     * @Route("envoyer-un-mail/{domain}/{name}", name="mailTo")
     *
     * @param $name
     * @param $domain
     * @return Response
     */
    public function mailTo($name, $domain)
    {
        return $this->redirect('mailto:'.$name.'@'.$domain);
    }

    /**
     * @Route("/cookies", name="cookiesChoice", options={"expose"=true})
     * @param Request $request
     * @param SessionInterface $session
     * @return JsonResponse|Response
     */
    public function ajaxAction(Request $request, SessionInterface $session)
    {
        if ($request->isXMLHttpRequest()) {
            $session->set('acceptCookies', $request->request->get('accept'));
            return new JsonResponse(array('message' => 'Choice for cookies updated'));
        }

        return new Response('This is not ajax!', 400);
    }
}
