<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Rollerworks\Component\PasswordStrength\Validator\Constraints\PasswordRequirements;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Merci de saisir un mot de passe',
                        ]),
                        new Length([
                            'normalizer'=> 'trim',
                            'max'=> 255,
                            'maxMessage'=> 'Le mot de passe ne doit pas dépasser {{limit}} caractères',
                            'allowEmptyString'=> false,
                        ]),
                        new NotCompromisedPassword([
                            'message' => 'Ce mot de passe ]a été divulgué lors d\'\'une violation de données, il ne doit pas être utilisé. Veuillez utiliser un autre mot de passe.'
                        ]),
                        new PasswordRequirements([
                            "minLength" => 8,
                            "tooShortMessage" => "Votre mot de passe doit contenir au moins {{length}} caractères.",
                            "requireLetters" => true,
                            "missingLettersMessage" => "Votre mot de passe doit comprendre au moins une lettre.",
                            "requireCaseDiff" => true,
                            "requireCaseDiffMessage" => "Votre mot de passe doit inclure des lettres majuscules et minuscules.",
                            "requireNumbers" => true,
                            "missingNumbersMessage" => "Votre mot de passe doit inclure au moins un chiffre.",
                            "requireSpecialCharacter" => true,
                            "missingSpecialCharacterMessage" => "Votre mot de passe doit contenir au moins un caractère spécial.",
                        ]),
                    ],
                    'label' => 'Nouveau mot de passe<span class="text-danger"> *</span>',
                    'label_html' => true,
                    'row_attr' => [
                        'class' => 'col-12 p-0'
                    ],
                ],
                'second_options' => [
                    'label' => 'Répétez le nouveau mot de passe<span class="text-danger"> *</span>',
                    'label_html' => true,
                    'row_attr' => [
                        'class' => 'col-12 p-0'
                    ],
                ],
                'invalid_message' => 'Les deux mots de passe doivent être identiques',
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => "Mettre à jour",
                'row_attr' => [
                    'class' => 'col-md-12 text-right p-0'
                ]
            ])

    ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
