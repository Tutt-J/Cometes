<?php

namespace App\Form\Admin;

use App\Entity\Address;
use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Keyword;
use App\Entity\Type;
use App\Entity\User;
use App\Form\ImageType;
use App\Repository\AddressRepository;
use App\Repository\AuthorRepository;
use App\Repository\CategoryRepository;
use App\Repository\KeywordRepository;
use App\Repository\TypeRepository;
use KMS\FroalaEditorBundle\Form\Type\FroalaEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\DateTimeValidator;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class EventType extends BaseType
{
    const NOTEMPTY_MESSAGE ="Ce champ ne peut pas être vide.";

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('subTitle', TextType::class, [
                'constraints' => [
                    new Length([
                        'normalizer' => 'trim',
                        'max' => 255,
                        'maxMessage' => 'Le titre ne doit pas dépasser {{limit}} caractères',
                        'allowEmptyString' => false,
                    ]),
                ],
                'label' => 'Sous-titre',
                'label_html' => true,
                'required' => false
            ])
            ->add('type', EntityType::class, [
                'constraints' => array(
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ]),
                ),
                'class' => Type::class,
                'query_builder' => function (TypeRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.forEvent = 1');
                },
                'choice_label' => 'wording',
                'required' => true,
                'multiple'=>true,
                'expanded'=>true,
                'label' => 'Type(s)<span class="text-danger"> *</span>',
                'label_html' => true,
                'choice_translation_domain' => 'messages',
            ])
            ->add('onlineEvent', CheckboxType::class, array(
                'label' => 'Ceci est un évènement en ligne'
            ))
            ->add('isCollaboration', CheckboxType::class, array(
                'label' => 'Ceci est une collaboration (le paiement ne se fait pas via note site)',
            ))
            ->add('collaborationLink', UrlType::class, [
                'constraints' => array(
                    new Regex("%^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu"),
                ),
                'label' => 'Url du partenaire où on peut s\'inscrire',
                'label_html' => true,
                'required' => false
            ])
            ->add('address', EntityType::class, [
                'class' => Address::class,
                'query_builder' => function (AddressRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.forEvents = 1');
                },
                'placeholder' => 'Lieu non défini',
                'required' => false,
                'choice_label' => 'name',
                'label' => 'Lieu',
                'label_html' => true,
            ])
            ->add('startDate', DateTimeType::class, [
                'years' => range(date('Y'), date('Y')+2),
                'constraints' => array(
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ]),
                ),
                'label' => 'Date de début<span class="text-danger"> *</span>',
                'label_html' => true
            ])
            ->add('endDate', DateTimeType::class, [
                'years' => range(date('Y'), date('Y')+2),
                'constraints' => array(
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ]),
                ),
                'label' => 'Date de fin <span class="text-danger"> *</span>',
                'label_html' => true
            ])
            ->add('landingPageUrl', UrlType::class, [
                'constraints' => array(
                    new Regex("%^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu"),
                ),
                'label' => 'Url de la landing page',
                'label_html' => true,
                'required' => false
            ])
            ->add('price', IntegerType::class, [
                'label' => 'Prix<span class="text-danger"> *</span>',
                'label_html' => true,
                'required' => true
            ])
            ->add('eventPricings', CollectionType::class, [
                'entry_type' => AdminEventPriceType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'label' => 'OU Ajouter une liste de différents tarifs (ex: early birdy, chambre de deux, etc)',
                 'label_attr'=> [
                    'class'=> 'foo'
                ]
            ])
            ->add('nbMinParticipant', IntegerType::class, [
                'label' => 'Nombre minimum de participantes<span class="text-danger"> *</span>',
                'label_html' => true,
            ])
            ->add('nbMaxParticipant', IntegerType::class, [
                'label' => 'Nombre maximum de participantes<span class="text-danger"> *</span>',
                'label_html' => true,
            ])
            ->add('allowFriend', ChoiceType::class, array(
                'choices'  => [
                    'Non' => 0,
                    'Oui' => 1,
                ],
                'label' => 'Autoriser la promotion si on vient avec un ami'
            ))
            ->add('allowAlready', ChoiceType::class, array(
                'choices'  => [
                    'Non' => 0,
                    'Oui' => 1,
                ],
                'label' => 'Autoriser la promotion si on a déjà participé'
            ))
            ->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (FormEvent $event) {
                    $identifier = $event->getData();
                    $element = $event->getForm();
                    if ($identifier) {
                        if (empty($event->getData()['price']) && empty($event->getData()['eventPricings'])) {
                                $element->addError(new FormError('Vous devez choisir au moins un tarif ou une liste de tarif.'));
                        }
                        $event->setData($identifier);
                    }
                }

            )

        ;
    }
}
