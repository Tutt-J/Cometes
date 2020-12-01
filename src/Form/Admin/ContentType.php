<?php

namespace App\Form\Admin;

use App\Entity\Content;
use App\Entity\Type;
use App\Form\ImageType;
use App\Repository\ContentRepository;
use App\Repository\TypeRepository;
use Doctrine\DBAL\Types\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContentType extends BaseType
{
    const NOTEMPTY_MESSAGE ="Ce champ ne peut pas être vide.";

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('type', EntityType::class, [
                'constraints' => array(
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ]),
                ),
                'class' => Type::class,
                'query_builder' => function (TypeRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.forContent = 1');
                },
                'choice_label' => 'slug',
                'label' => 'Type<span class="text-danger"> *</span>',
                'label_html' => true,
            ])
            ->add('price', IntegerType::class, [
                'constraints' => array(
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ]),
                ),
                'label' => 'Prix<span class="text-danger"> *</span>',
                'label_html' => true,
                'required' => true
            ])
            ->add('fidelityPrice', IntegerType::class, [
                'constraints' => array(
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ]),
                ),
                'label' => 'Prix fidélité<span class="text-danger"> *</span>',
                'label_html' => true,
                'required' => true

            ])
            ->add('eventDate', TextType::class, [
                'constraints' => array(
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ]),
                ),
                'label' => 'Date de début<span class="text-danger"> *</span>',
                'label_html' => true,
                'attr' => [
                    'class' => 'automatic_date',
                    'placeholder' => '01/01/2000',
                ],
                'mapped' => false,
                'required' => true,
                'help' => "Pour les rituels mettre la date du rituel en physique, pour le reste mettre la date de sortie."
            ])
            ->add('ref', TextType::class, [
                'constraints' => [
                    new Length([
                        'normalizer' => 'trim',
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'La référence doit au moins contenir {{ limit }} caractères',
                        'maxMessage' => 'La référence ne doit pas dépasser {{limit}} caractères',
                        'allowEmptyString' => false,
                    ]),
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ])
                ],
                'label' => 'Référence<span class="text-danger"> *</span>',
                'label_html' => true,
            ])
            ->add('onlineLink', TextType::class, [
                'label' => 'Url de la vidéo',
                'label_html' => true,
                'attr' => [
                    'placeholder' => 'Laisses vide, je le fais quand je mets la vidéo en ligne',
                ],
            ])
            ->add('isPack', CheckboxType::class, array(
                'label' => 'Correspond à un pack'
            ))
            ->add('neverPassed', CheckboxType::class, array(
                'label' => 'Proposer à la vente même si l\'évènement associé est passé'
            ))
            ->add('pack', EntityType::class, [
                'class' => Content::class,
                'query_builder' => function (ContentRepository $er) {
                    return $er->createQueryBuilder('u');
                },
                'placeholder' => 'Choisir le pack',
                'required' => false,
                'choice_label' => 'title',
                'label' => 'Si le produit appartient à un pack, choisir le pack',
                'label_html' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Content::class,
        ]);
    }
}
