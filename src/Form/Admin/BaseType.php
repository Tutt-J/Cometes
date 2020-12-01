<?php

namespace App\Form\Admin;

use App\Entity\Article;
use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Keyword;
use App\Form\ImageType;
use App\Form\KeywordType;
use App\Repository\AuthorRepository;
use App\Repository\CategoryRepository;
use App\Repository\KeywordRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class BaseType extends AbstractType
{
    const NOTEMPTY_MESSAGE ="Ce champ ne peut pas être vide.";

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [
                    new Length([
                        'normalizer' => 'trim',
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Le titre doit au moins contenir {{ limit }} caractères',
                        'maxMessage' => 'Le titre ne doit pas dépasser {{limit}} caractères',
                        'allowEmptyString' => false,
                    ]),
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ])
                ],
                'label' => 'Titre<span class="text-danger"> *</span>',
                'label_html' => true,
            ])
            ->add('content', TextareaType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ])
                ],
                'label' => 'Contenu<span class="text-danger"> *</span>',
                'label_html' => true,
            ])
            ->add('img', ImageType::class, array(
                'mapped' => false,
                'label' => 'Image de couverture (mise en avant)'
            ))
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'row_attr' => [
                    'class' => 'col-md-12 text-right'
                ]
            ])
            ->add('isOnline', ChoiceType::class, array(
                'choices'  => [
                    'Brouillon' => 0,
                    'Publié' => 1,
                ],
                'label' => 'Afficher sur le site'
            ))
        ;
    }
}
