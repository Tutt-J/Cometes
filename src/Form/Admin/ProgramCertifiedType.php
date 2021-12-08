<?php

namespace App\Form\Admin;


use App\Entity\ProgramCertified;
use App\Form\ImageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ProgramCertifiedType extends AbstractType
{
    const NOTEMPTY_MESSAGE ="Ce champ ne peut pas être vide.";
    const URL_REGEX = "%^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu";

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
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
            ->add('year', ChoiceType::class, [
                'constraints' => array(
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ]),
                ),
                'choices'  => [

                    date('Y', strtotime('-2 years')) =>date('Y', strtotime('-2 years')),
                    date('Y', strtotime('-1 years')) => date('Y', strtotime('-1 years')),
                    date('Y') =>  date('Y'),
                ],
                'label' => 'Année<span class="text-danger"> *</span>',
                'label_html' => true
            ])
            ->add('opinion', TextareaType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ])
                ],
                'label' => 'Avis<span class="text-danger"> *</span>',
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProgramCertified::class,
        ]);
    }
}
