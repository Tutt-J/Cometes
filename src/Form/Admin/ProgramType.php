<?php

namespace App\Form\Admin;

use App\Entity\Program;
use App\Entity\TypeProgram;
use App\Repository\TypeProgramRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ProgramType extends BaseType
{
    const NOTEMPTY_MESSAGE ="Ce champ ne peut pas être vide.";

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('teachable_url', UrlType::class, [
                'constraints' => array(
                    new Regex("%^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu"),
                ),
                'label' => 'Url de la landing page',
                'label_html' => true,
                'required' => false
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
            ->add('type', EntityType::class, [
                'class' => TypeProgram::class,
                'choice_label' => 'slug',
                'expanded'     => true,
                'multiple'     => true,
                'label' => 'Où placer ce programme/formation<span class="text-danger"> *</span>',
                'label_html' => true,
                'choice_translation_domain' => 'messages'
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
