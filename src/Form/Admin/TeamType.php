<?php

namespace App\Form\Admin;

use App\Entity\Team;
use App\Form\ImageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class TeamType extends AbstractType
{
    const NOTEMPTY_MESSAGE ="Ce champ ne peut pas être vide.";

    public function setFieldForName($name){
        return [
            'constraints' => [
                new Length([
                    'normalizer' => 'trim',
                    'min' => 2,
                    'max' => 100,
                    'minMessage' => 'Le '.$name.' doit au moins contenir {{ limit }} caractères',
                    'maxMessage' => 'Le '.$name.' ne doit pas dépasser {{ limit }} caractères',
                    'allowEmptyString' => false,
                ]),
                new NotBlank([
                    'message' => SELF::NOTEMPTY_MESSAGE
                ])
            ],
            'label' => $name.'<span class="text-danger"> *</span>',
            'label_html' => true,
            'row_attr' => [
                'class' => 'col-md-6'
            ],
        ];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ])
                ],
                'label' => 'Portrait astro<span class="text-danger"> *</span>',
                'label_html' => true,
            ])
            ->add('lead', TextType::class, $this->setFieldForName("Rôle"))
            ->add('name', TextType::class, $this->setFieldForName("Nom"))
            ->add('link', UrlType::class, [
                'constraints' => array(
                    new Regex(
                        "%^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu",
                        "URL invalide"
                    ),
                    new NotBlank([
                        'message' => "Ce champ ne peut pas être vide."
                    ])
                ),
                'label' => 'Url du site<span class="text-danger"> *</span>',
                'label_html' => true,
            ])
            ->add('img', ImageType::class, array(
                'mapped' => false,
                'label' => 'Photo'
            ))
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'row_attr' => [
                    'class' => 'col-md-12 text-right'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
        ]);
    }
}
