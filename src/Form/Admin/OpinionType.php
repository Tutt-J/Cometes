<?php

namespace App\Form\Admin;

use App\Entity\Opinion;
use App\Entity\Type;
use App\Repository\TypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class OpinionType extends BaseType
{
    const NOTEMPTY_MESSAGE ="Ce champ ne peut pas être vide.";

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->remove('title')
            ->remove('img')
            ->add('client', TextType::class, [
                'constraints' => [
                    new Length([
                        'normalizer' => 'trim',
                        'min' => 2,
                        'max' => 25,
                        'minMessage' => 'Le client doit au moins contenir {{ limit }} caractères',
                        'maxMessage' => 'Le client ne doit pas dépasser {{limit}} caractères',
                        'allowEmptyString' => false,
                    ]),
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ])
                ],
                'label' => 'Prénom de la cliente<span class="text-danger"> *</span>',
                'label_html' => true,
                'row_attr' => [
                    'class' => 'col-md-6'
                ],
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
                        ->where('u.forOpinion = 1');
                },
                'choice_label' => 'slug',
                'label' => 'Type<span class="text-danger"> *</span>',
                'label_html' => true,
                'choice_translation_domain' => 'messages'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Opinion::class,
        ]);
    }
}
