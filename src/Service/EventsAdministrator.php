<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\EventPricing;
use App\Entity\Type;
use App\Entity\UserEvent;
use App\Form\EventType;
use App\Form\EventPriceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class EventsAdministrator
 * @package App\Service
 */
class EventsAdministrator
{

    /**
     * @var array
     */
    private array $events=[];

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var SessionInterface
     */
    private SessionInterface $session;

    /**
     * @var Security
     */
    private Security $security;

    /**
     * @var RequestStack
     */
    protected RequestStack $requestStack;

    /**
     * @var UrlGeneratorInterface
     */
    protected UrlGeneratorInterface $router;

    /**
     * @var FormFactoryInterface
     */
    protected FormFactoryInterface $formFactory;
    /**
     * @var Environment
     */
    protected Environment $twig;
    /**
     * @var FlashBagInterface
     */
    private FlashBagInterface $flashbag;

    const DATE_FORMAT='Y-m-d H:i';


    /**
     * BasketAdministrator constructor.
     * @param UrlGeneratorInterface $router
     * @param EntityManagerInterface $em
     * @param FormFactoryInterface $formFactory
     * @param RequestStack $requestStack
     * @param SessionInterface $session
     * @param Security $security
     * @param Environment $twig
     * @param FlashBagInterface $flashbag
     */
    public function __construct(
        UrlGeneratorInterface $router,
        EntityManagerInterface $em,
        FormFactoryInterface $formFactory,
        RequestStack $requestStack,
        SessionInterface $session,
        Security $security,
        Environment $twig,
        FlashBagInterface $flashbag
    ) {
        $this->em = $em;
        $this->session = $session;
        $this->security=$security;
        $this->router= $router;
        $this->requestStack = $requestStack;
        $this->formFactory=$formFactory;
        $this->twig = $twig;
        $this->flashbag=$flashbag;
    }

    /**
     * @return mixed
     */
    public function getEvents()
    {
        return $this->em
            ->getRepository(Event::class)
            ->findThreeBecomeRituals();
    }

    /**
     * @return array
     */
    public function getThreeNextEvents()
    {
        $eventsList=$this->getEvents();
        foreach ($eventsList as $event) {
            array_push(
                $this->events,
                array(
                    'path' =>$event->getType()->getSlug().'Event',
                    'slug' =>$event->getSlug(),
                    'img' =>$event->getImg(),
                    'title' =>$event->getTitle(),
                    'startDate' =>$event->getStartDate(),
                    'endDate' => $event->getEndDate()
                )
            );
        }
        return $this->events;
    }

    /**
     * @param $slug
     * @return object|null
     */
    public function getEvent($slug)
    {
        $event= $this->em
            ->getRepository(Event::class)
            ->findOneBy(
                ['slug' => $slug]
            );

        if (is_null($event)) {
            throw new NotFoundHttpException('Cet évènement n\'existe pas.');
        }

        return $event;
    }

    /**
     * @param $event
     * @return bool
     */
    public function checkAlreadyRegister($event)
    {
        //Check if user already register
        $exist=$this->em
            ->getRepository(UserEvent::class)
            ->findBy(
                [
                    'user' =>  $this->security->getUser(),
                    'event' => $event
                ]
            );

        if ($exist) {
            return true;
        }
        return false;
    }


    /**
     * @param $slug
     * @return RedirectResponse|Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderEventPage($slug)
    {
        $this->session->set('referent', [
            'path'=> $this->getEvent($slug)->getType()->getSlug().'Event',
            'slug'=> $slug
        ]);

        $event=$this->em
            ->getRepository(Event::class)
            ->findOneBy(
                ['slug' => $slug]
            );

        if ($this->generateMessageError($slug) === true) {
            if (!$event->getEventPricings()->isEmpty()) {
                $form = $this->formFactory->create(
                    EventPriceType::class,
                    new EventPricing(),
                    array('event' => $event)
                );
                $multiplePrice=true;
            } else {
                $form = $this->formFactory->create(
                    EventType::class,
                    $event
                );
                $multiplePrice=false;
            }

            $form->handleRequest($this->requestStack->getCurrentRequest());

            //To be sure for legals reasons
            if ($form->isSubmitted()
                && $form->isValid()
                && true === $form['agreeTerms']->getData()
                && true === $form['agreeCgv']->getData()) {
                return $this->submitForm($multiplePrice, $form, $event, $slug);
            }
            $formView=$form->createView();
            $errorMessage=null;
        } else {
            $formView=null;
            $errorMessage=$this->generateMessageError($slug);
        }

        $response = new Response();

        $response->setContent($this->twig->render(
            'events/event.html.twig',
            [
                'event' => $this->getEvent($slug),
                'form' => $formView,
                'maxMessage' => $errorMessage
            ]
        ));

        return $response;
    }

    /**
     * @param $slug
     * @return string
     */
    public function generateMessageError($slug)
    {
        $event=$this->getEvent($slug);
        $message=true;
        if ($this->checkAlreadyRegister($event)) {
            $message="Vous êtes déjà inscrit à cet évènement";
        }
        if (!$this->checkNbMaxParticipant($event)) {
            $message="Le nombre maximum de participantes pour cette évènement est atteint.";
        }
        if (!$this->checkEventPassed($event)) {
            $message="Cet évènement est passé ou a lieu dans moins de 24 heures. Vous ne pouvez plus vous y inscrire.";
        }
        return $message;
    }

    /**
     * @param $event
     * @return bool
     */
    public function checkNbMaxParticipant($event)
    {
        $nbRegister = $this->em
            ->getRepository(UserEvent::class)
            ->findBy(
                ['event' => $event]
            );
        if (sizeof($nbRegister)<$event->getNbMaxParticipant()) {
            return true;
        }
        return false;
    }

    public function checkEventPassed($event)
    {
        $date = date(SELF::DATE_FORMAT);
        if (($event->getStartDate())->format(SELF::DATE_FORMAT) > date(SELF::DATE_FORMAT, strtotime($date. ' + 1 days'))) {
            return true;
        }
        return false;
    }

    /**
     * @param $friend
     * @param $already
     */
    public function generateDescription($friend, $already)
    {
        if (!empty($friend) && $already == 1) {
            $this->session->set("description", "Réduction de 5% car vient avec " . $friend . " et a déjà participé à une retraite chamade");
        } elseif (!empty($friend)) {
            $this->session->set("description", "Réduction de 5% car vient avec " . $friend);
        } else {
            $this->session->set("description", "Réduction de 5% car a déjà participé à une retraite chamade");
        }
    }

    public function canRegister($event)
    {
        $isTrue=true;
        if ($this->checkAlreadyRegister($event)) {
            $this->flashbag->add('error', 'Vous êtes déjà inscrit à cet évènement.');
            $isTrue=false;
        }
        if (!$this->checkNbMaxParticipant($event)) {
            $this->flashbag->add('error', 'Le nombre maximum de participantes pour cette évènement est atteint.');
            $isTrue=false;
        }
        if (!$this->checkEventPassed($event)) {
            $this->flashbag->add('error', 'Cet évènement est passé ou a lieu dans moins de 24 heures. Vous ne pouvez plus vous y inscrire.');
            $isTrue=false;
        }
        return $isTrue;
    }

    public function getType($slug)
    {
        return $this->em
            ->getRepository(Type::class)
            ->findBy(
                array(
                    'slug' => $slug,
                ),
                array('id' => 'DESC')
            );
    }

    /**
     * @param bool $mutiplePrice
     * @param FormInterface $form
     * @param object|null $event
     * @param $slug
     * @return RedirectResponse
     */
    public function submitForm(bool $mutiplePrice, FormInterface $form, ?object $event, $slug): RedirectResponse
    {
        if ($mutiplePrice === true) {
            $price = $form->get('choice')->getData()->getPrice();
        } else {
            $price=$event->getPrice();
        }

        $this->session->set('description', "");

        if ($form->has('friend') && $form->has('already')) {
            if (!empty($form->get('friend')->getData()) || $form->get('already')->getData() == 1) {
                $price=$price - ($price * (5 / 100));
                $this->generateDescription($form->get('friend')->getData(), $form->get('already')->getData());
            }
        }

        $this->session->set('price', $price);

        return new RedirectResponse($this->router->generate('registerEvent', ['slug' => $slug]));
    }
}
