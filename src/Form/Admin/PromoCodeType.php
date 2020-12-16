<?php

namespace App\Form\Admin;

use App\Entity\PromoCode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PromoCodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('deleteAmount', IntegerType::class, [
                'constraints' => array(
                    new NotBlank([
                        'message' => 'Ce champs ne peut pas être vide'
                    ]),
                ),
                'label' => 'Montant à supprimer<span class="text-danger"> *</span>',
                'label_html' => true,
                'required' => true,
                'mapped' => false
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'row_attr' => [
                    'class' => 'col-md-12 text-right'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PromoCode::class,
        ]);
    }
}
