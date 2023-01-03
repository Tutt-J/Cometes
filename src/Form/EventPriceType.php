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
                'help' => "Le bon sera appliqué à l'étape suivante s'il est valide"
            ]);
        if ($options['event']->getAllowFriend()) {
            $builder->add('friend', TextType::class, [
                'mapped' => false,
                'label' => 'Si vous venez avec une amie, son nom et prénom',
                'help' => 'Ceci vous fera bénéficier de 5% de réduction. Soumis à vérification ou redevable le jour de l\'évènement.',
                'required' => false
            ]);
        }
        if ($options['event']->getAllowAlready()) {
            $builder->add('already', CheckboxType::class, [
                'mapped' => false,
                'label' => 'J\'ai déjà participé à une retraite Chamade',
                'help' => 'Ceci vous fera bénéficier de 5% de réduction. Soumis à vérification ou redevable le jour de l\'évènement.',
                'required' => false
            ]);
        }
        if ($options['event']->getAllowPaiennes()) {
            $builder->add('paiennes', CheckboxType::class, [
                'mapped' => false,
                'label' => 'Je fais partie de la <a href="https://www.lespaiennes.com">communauté payante des Païennes</a>',
                'label_html' => true,
                'help' => 'Ceci vous fera bénéficier de 5% de réduction. Soumis à vérification ou redevable le jour de l\'évènement.',
                'required' => false
            ]);
        }

        $builder->add('agreeTerms', CheckboxType::class, [
            'label' => 'J\'ai lu et j\'accepte les <a href="/conditions-particulieres-pour-les-evenements">conditions particulières pour les évènements</a><span class="text-danger"> *</span>',
            'label_html' => true,
            'mapped' => false,
            'constraints' => [
                new IsTrue([
                    'message' => 'Vous devez accepter nos conditions particulières pour les évènements.',
                ]),
            ],
        ])
            ->add('agreeCgv', CheckboxType::class, [
                'label' => 'J\'ai lu et j\'accepte les <a href="/conditions-generales-de-vente">conditions générales de vente</a><span class="text-danger"> *</span>',
                'label_html' => true,
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter nos conditions générales de ventes.',
                    ]),
                ],
            ])
            ->add('agreeNewsletter', CheckboxType::class, [
                'label' => 'J\'accepte de recevoir les actualités et prochains évènements de Comètes par e-mail.',
                'label_html' => true,
                'mapped' => false,
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
