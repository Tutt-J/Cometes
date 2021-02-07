<?php
// src/Form/UserType.php
namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Country;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class AddressType
 * @package App\Form
 */
class AddressType extends AbstractType
{
    const NOTEMPTY_MESSAGE ="Ce champ ne peut pas être vide.";
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'constraints' => [
                    new Length([
                        'normalizer' => 'trim',
                        'min' => 2,
                        'max' => 25,
                        'minMessage' => 'Le nom de l\'adresse doit au moins contenir {{ limit }} caractères',
                        'maxMessage' => 'Le nom de l\'adresse ne doit pas dépasser {{ limit }} caractères',
                        'allowEmptyString' => false,
                    ]),
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ])
                ],
                'label' => 'Nom de l\'adresse<span class="text-danger"> *</span>',
                'label_html' => true,
                'required' => false,
                'help' => 'Par exemple : A la maison',
                'row_attr' => [
                    'class' => 'col-md-6'
                ],
            ))
            ->add('street', TextType::class, array(
                'constraints' => [
                    new Length([
                        'normalizer' => 'trim',
                        'min' => 4,
                        'max' => 255,
                        'minMessage' => 'La rue doit au moins contenir {{ limit }} caractères.',
                        'maxMessage' => 'La rue ne doit pas dépasser {{limit}} caractères.',
                        'allowEmptyString' => false,
                    ]),
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ])
                ],
                'label' => 'Numéro et nom de rue<span class="text-danger"> *</span>',
                'label_html' => true,
                'required' => true,
                'row_attr' => [
                    'class' => 'col-md-6'
                ],
            ))
            ->add('postalCode', TextType::class, array(
                'constraints' => [
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ])
                ],
                'label' => 'Code postal<span class="text-danger"> *</span>',
                'label_html' => true,
                'required' => true,
                'row_attr' => [
                    'class' => 'col-md-6'
                ],
            ))
            ->add('city', TextType::class, array(
                'constraints' => [
                    new Length([
                        'normalizer' => 'trim',
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'La ville doit au moins contenir {{ limit }} caractères.',
                        'maxMessage' => 'La ville ne doit pas dépasser {{limit}} caractères.',
                        'allowEmptyString' => false,
                    ]),
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ])
                ],
                'label' => 'Ville<span class="text-danger"> *</span>',
                'label_html' => true,
                'required' => true,
                'row_attr' => [
                    'class' => 'col-md-6'
                ],
            ))
            ->add('country', CountryType::class, array(
                'constraints' => [
                    new Length([
                        'normalizer' => 'trim',
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Le pays doit au moins contenir {{ limit }} caractères.',
                        'maxMessage' => 'La pays ne doit pas dépasser {{limit}} caractères.',
                        'allowEmptyString' => false,
                    ]),
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ]),
                    new Country([
                        'message'=>'Le pays est invalide.'
                    ])
                ],
                'label' => 'Pays<span class="text-danger"> *</span>',
                'label_html' => true,
                'preferred_choices' => array('FR'),
                'choice_translation_locale' => 'fr',
                'row_attr' => [
                    'class' => 'col-md-6'
                ],
            ))
            ->add('othersInformations', TextType::class, array(
                'constraints' => [
                    new Length([
                        'normalizer' => 'trim',
                        'max' => 255,
                        'maxMessage' => 'Ce champs ne doit pas dépasser {{limit}} caractères',
                        'allowEmptyString' => false,
                    ]),

                ],
                'label' => 'Informations complémentaires',
                'required' => false,
                'row_attr' => [
                    'class' => 'col-md-6'
                ],
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Address::class,
        ));
    }
}
