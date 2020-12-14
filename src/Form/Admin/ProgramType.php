<?php

namespace App\Form\Admin;

use App\Entity\Program;
use App\Entity\TypeProgram;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProgramType extends BaseType
{
    const NOTEMPTY_MESSAGE ="Ce champ ne peut pas être vide.";

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder

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
            ->add('type', EntityType::class, [
                'class' => TypeProgram::class,
                'choice_label' => 'slug',
                'expanded'     => true,
                'multiple'     => true,
                'label' => 'Où placer ce programme/formation<span class="text-danger"> *</span>',
                'label_html' => true,
                'choice_translation_domain' => 'messages'
            ])
            ->add('programButtons', CollectionType::class, [
                'entry_type' => ProgramButtonsType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'label' => 'Boutons',
                'label_attr'=> [
                    'class'=> 'foo'
                    ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Program::class,
        ]);
    }
}
