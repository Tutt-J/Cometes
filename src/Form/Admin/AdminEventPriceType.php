<?php

namespace App\Form\Admin;

use App\Entity\EventPricing;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdminEventPriceType extends AbstractType
{
    const NOTEMPTY_MESSAGE ="Ce champ ne peut pas être vide.";
    const YEAR_RANGE ="Ce champ ne peut pas être vide.";

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', TextType::class, [
                'constraints' => [
                    new Length([
                        'normalizer' => 'trim',
                        'max' => 255,
                        'maxMessage' => 'Le titre ne doit pas dépasser {{limit}} caractères',
                        'allowEmptyString' => false,
                    ]),
                ],
                'label' => 'Dénomination<span class="text-danger"> *</span>',
                'label_html' => true,
                'required' => true
            ])
            ->add('price', IntegerType::class, [
                'label' => 'Prix<span class="text-danger"> *</span>',
                'label_html' => true,
            ])
            ->add('startValidityDate', DateType::class, [
                'years' => range(date('Y'), date('Y')+2),
                'label' => 'Date de début de validité',
                'label_html' => true,
                "required"=>true,
                'help' => "La date choisie est comprise.",
                'placeholder' => [
                    'year' => 'Année',
                    'month' => 'Mois',
                    'day' => 'Jour',
                ]
                ])
            ->add('endValidityDate', DateType::class, [
                'years' => range(date('Y'), date('Y')+2),
                'label' => 'Date de fin de validité',
                'label_html' => true,
                'attr' => [
                    'class' => 'automatic_date',
                    'placeholder' => '01/01/2000',
                ],
                "required"=>true,
                'help' => "La date choisie est comprise.",
                'placeholder' => [
                    'year' => 'Année',
                    'month' => 'Mois',
                    'day' => 'Jour',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EventPricing::class,
        ]);
    }
}
