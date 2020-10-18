<?php

namespace App\Form\Admin;

use App\Entity\Author;
use App\Form\ImageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AuthorType extends BaseType
{
    const NOTEMPTY_MESSAGE ="Ce champ ne peut pas être vide.";

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->remove('title')
            ->add('name', TextType::class, [
                'constraints' => [
                    new Length([
                        'normalizer' => 'trim',
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Le nom doit au moins contenir {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne doit pas dépasser {{limit}} caractères',
                        'allowEmptyString' => false,
                    ]),
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ])
                ],
                'label' => 'Nom<span class="text-danger"> *</span>',
                'label_html' => true,
                'row_attr' => [
                    'class' => 'col-md-6'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Author::class,
        ]);
    }
}
