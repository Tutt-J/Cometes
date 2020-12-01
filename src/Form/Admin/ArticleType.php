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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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

class ArticleType extends BaseType
{
    const NOTEMPTY_MESSAGE ="Ce champ ne peut pas être vide.";

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('author', EntityType::class, [
                'constraints' => array(
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ]),
                ),
                'class' => Author::class,
                'query_builder' => function (AuthorRepository $er) {
                    return $er->createQueryBuilder('u');
                },
                'choice_label' => 'name',
                'label' => 'Auteur<span class="text-danger"> *</span>',
                'label_html' => true,
            ])
            ->add('category', EntityType::class, [
                'constraints' => array(
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ]),
                ),
                'class' => Category::class,
                'query_builder' => function (CategoryRepository $er) {
                    return $er->createQueryBuilder('u');
                },
                'choice_label' => 'wording',
                'label' => 'Catégorie<span class="text-danger"> *</span>',
                'label_html' => true,
            ])
            ->add('categoryNew', TextType::class, [
                'label' => 'OU utiliser une nouvelle catégorie',
                'label_html' => true,
                'mapped' => false
            ])
            ->add('keywords', TextType::class, [
                'label' => 'Hashtags séparés par une virgule<span class="text-danger"> *</span>',
                'label_html' => true,
                'mapped' => false
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            "allow_extra_fields" => true
        ]);
    }
}
