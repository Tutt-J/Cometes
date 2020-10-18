<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Form\AddressType;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\Valid;
use Rollerworks\Component\PasswordStrength\Validator\Constraints as RollerworksPassword;

class ContactType extends AbstractType
{

    const NOTEMPTY_MESSAGE ="Ce champ ne peut pas être vide.";

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new Length([
                        'normalizer' => 'trim',
                        'min' => 2,
                        'max' => 25,
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
            ])
            ->add('afield', emailType::class, [
                'constraints' => [
                    new Email([
                        'message'=>'L\'adresse e-mail "{{ value }}" est invalide.'
                    ]),
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ])
                ],
                'label' => 'Email<span class="text-danger"> *</span>',
                'label_html' => true,
            ])
            ->add('object', TextType::class, [
                'constraints' => [
                    new Length([
                        'normalizer' => 'trim',
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'L\'objet doit au moins contenir {{ limit }} caractères',
                        'maxMessage' => 'L\'objet ne doit pas dépasser {{limit}} caractères',
                        'allowEmptyString' => false,
                    ]),
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ])
                ],
                'label' => 'Objet<span class="text-danger"> *</span>',
                'label_html' => true,
            ])
            ->add('message', TextareaType::class, [
                'constraints' => [
                    new Length([
                        'normalizer' => 'trim',
                        'min' => 2,
                        'max' => 500,
                        'minMessage' => 'Le message doit au moins contenir {{ limit }} caractères',
                        'maxMessage' => 'Le message de famille ne doit pas dépasser {{limit}} caractères',
                        'allowEmptyString' => false,
                    ]),
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ])
                ],
                'label' => 'Message<span class="text-danger"> *</span>',
                'label_html' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Envoyer',
                 'row_attr' => [
                    'class' => 'text-right'
                ]
            ])
        ;
    }
}
