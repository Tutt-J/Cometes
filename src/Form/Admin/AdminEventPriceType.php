<?php

namespace App\Form\Admin;

use App\Entity\EventPricing;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdminEventPriceType extends AbstractType
{
    const NOTEMPTY_MESSAGE ="Ce champ ne peut pas être vide.";

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
                'label' => 'Dénomination',
                'label_html' => true,
                'required' => true
            ])
            ->add('price', IntegerType::class, [
                'label' => 'Prix<span class="text-danger"> *</span>',
                'label_html' => true,
            ])
            ->add('startValidityDate', TextType::class, [
                'constraints' => array(
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ]),
                ),
                'label' => 'Date de début de validité<span class="text-danger"> *</span>',
                'label_html' => true,
                'attr' => [
                    'class' => 'automatic_date',
                    'placeholder' => '01/01/2000',
                ],
                'mapped' => false,
            ])
            ->add('endValidityDate', TextType::class, [
                'constraints' => array(
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ]),
                ),
                'label' => 'Date de fin de validité <span class="text-danger"> *</span>',
                'label_html' => true,
                'attr' => [
                    'class' => 'automatic_date',
                    'placeholder' => '01/01/2000',
                ],
                'mapped' => false,
                'empty_data' => 'Default value'
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
