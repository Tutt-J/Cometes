<?php

namespace App\Form\Admin;

use App\Entity\Content;
use App\Entity\Type;
use App\Entity\User;
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

class OfferContentType extends AbstractType
{
    const NOTEMPTY_MESSAGE ="Ce champ ne peut pas être vide.";

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('content', TextType::class, [
                'constraints' => [
                    new Length([
                        'normalizer' => 'trim',
                        'max' => 255,
                        'maxMessage' => 'Le motif ne doit pas dépasser {{limit}} caractères',
                        'allowEmptyString' => false,
                    ]),
                ],
                'label' => 'Motif<span class="text-danger"> *</span>',
                'label_html' => true,
                'required' => true
            ])
            ->add('user', EntityType::class, [
                // looks for choices from this entity
                'class' => User::class,
                'label' => 'Cliente<span class="text-danger"> *</span>',
                'label_html' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'row_attr' => [
                    'class' => 'col-md-12 text-right'
                ]
            ]);
    }
}
