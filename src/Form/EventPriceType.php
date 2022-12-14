<?php

namespace App\Form;

use App\Entity\EventPricing;
use App\Entity\User;
use App\Repository\EventPricingRepository;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Form\AddressType;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\Valid;
use Rollerworks\Component\PasswordStrength\Validator\Constraints as RollerworksPassword;

class EventPriceType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('choice', EntityType::class, [
                // looks for choices from this entity
                'class' => EventPricing::class,
                'mapped' => false,
                'label' => 'Choisissez une offre',
                'query_builder' => function (EventPricingRepository $er) use ($options) {
                    return $er->createQueryBuilder('u')
                        ->where('u.event = :event')
                        ->andWhere('u.startValidityDate <= :now or u.endValidityDate >= :now')
                        ->setParameter('now', (new DateTime())->format('Y-m-d'))
                        ->setParameter('event', $options['event']);
                }
            ])
            ->add('promoCode', TextType::class, [
                'label' => 'Code promotionnel ou carte cadeau',
                'mapped' => false,
                'required' => false,
                'help' => "Le bon sera appliqu?? ?? l'??tape suivante s'il est valide"
            ]);
        if ($options['event']->getAllowFriend()) {
            $builder->add('friend', TextType::class, [
                'mapped' => false,
                'label' => 'Si vous venez avec une amie, son nom et pr??nom',
                'help' => 'Ceci vous fera b??n??ficier de 5% de r??duction. Soumis ?? v??rification ou redevable le jour de l\'??v??nement.',
                'required' => false
            ]);
        }
        if ($options['event']->getAllowAlready()) {
            $builder->add('already', CheckboxType::class, [
                'mapped' => false,
                'label' => 'J\'ai d??j?? particip?? ?? une retraite Chamade',
                'help' => 'Ceci vous fera b??n??ficier de 5% de r??duction. Soumis ?? v??rification ou redevable le jour de l\'??v??nement.',
                'required' => false
            ]);
        }
        if ($options['event']->getAllowPaiennes()) {
            $builder->add('paiennes', CheckboxType::class, [
                'mapped' => false,
                'label' => 'Je fais partie de la <a href="https://www.lespaiennes.com">communaut?? payante des Pa??ennes</a>',
                'label_html' => true,
                'help' => 'Ceci vous fera b??n??ficier de 5% de r??duction. Soumis ?? v??rification ou redevable le jour de l\'??v??nement.',
                'required' => false
            ]);
        }

        $builder->add('agreeTerms', CheckboxType::class, [
            'label' => 'J\'ai lu et j\'accepte les <a href="/conditions-particulieres-pour-les-evenements">conditions particuli??res pour les ??v??nements</a><span class="text-danger"> *</span>',
            'label_html' => true,
            'mapped' => false,
            'constraints' => [
                new IsTrue([
                    'message' => 'Vous devez accepter nos conditions particuli??res pour les ??v??nements.',
                ]),
            ],
        ])
            ->add('agreeCgv', CheckboxType::class, [
                'label' => 'J\'ai lu et j\'accepte les <a href="/conditions-generales-de-vente">conditions g??n??rales de vente</a><span class="text-danger"> *</span>',
                'label_html' => true,
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter nos conditions g??n??rales de ventes.',
                    ]),
                ],
            ])
            ->add('agreeNewsletter', CheckboxType::class, [
                'label' => 'J\'accepte de recevoir les actualit??s et prochains ??v??nements de Com??tes par e-mail.',
                'label_html' => true,
                'mapped' => false,
                'required' => false
            ])
            ->add('save', SubmitType::class, [
                'label' => 'M\'inscrire',
                'row_attr' => [
                    'class' => 'text-center'
                ],
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EventPricing::class,
            'event' => null
        ]);
    }
}
