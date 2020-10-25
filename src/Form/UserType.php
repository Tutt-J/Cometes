<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\Valid;
use Rollerworks\Component\PasswordStrength\Validator\Constraints as RollerworksPassword;

class UserType extends AbstractType
{
    const NOTEMPTY_MESSAGE ="Ce champ ne peut pas être vide.";

    public function setFieldForName($name){
        return [
            'constraints' => [
                new Length([
                    'normalizer' => 'trim',
                    'min' => 2,
                    'max' => 25,
                    'minMessage' => 'Le '.$name.' doit au moins contenir {{ limit }} caractères',
                    'maxMessage' => 'Le '.$name.' ne doit pas dépasser {{limit}} caractères',
                    'allowEmptyString' => false,
                ]),
                new NotBlank([
                    'message' => SELF::NOTEMPTY_MESSAGE
                ])
            ],
            'label' => $name.'<span class="text-danger"> *</span>',
            'label_html' => true,
            'row_attr' => [
                'class' => 'col-md-6'
            ],
        ];
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', emailType::class, [
                'constraints' => [
                    new Email([
                        'message'=>'L\'adresse e-mail "{{ value }}" est invalide.'
                    ]),
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ])
                ],
                'label' => 'Adresse E-mail<span class="text-danger"> *</span>',
                'label_html' => true,
                'row_attr' => [
                    'class' => 'col-md-6'
                ],
            ])
            ->add('username', TextType::class, $this->setFieldForName("Pseudo"))
            ->add('firstname', TextType::class, $this->setFieldForName("Prénom"))
            ->add('lastname', TextType::class,  $this->setFieldForName("Nom"))
            ->add('plainPassword', RepeatedType::class, array(
                'constraints' => [
                    new Length([
                        'normalizer' => 'trim',
                        'max' => 255,
                        'maxMessage' => 'Le mot de passe ne doit pas dépasser {{limit}} caractères',
                        'allowEmptyString' => false,
                    ]),
                    new NotBlank([
                        'message' => SELF::NOTEMPTY_MESSAGE
                    ]),
                    new NotCompromisedPassword([
                        'message' => 'Ce mot de passe a été divulgué lors d\'\'une violation de données, il ne doit pas être utilisé. Veuillez utiliser un autre mot de passe.'
                    ]),
                    new RollerworksPassword\PasswordRequirements([
                        'minLength' => 8,
                        'tooShortMessage' => 'Le mot de passe doit contenir au moins {{length}} caractères.',
                        'requireLetters' => true,
                        'missingLettersMessage' => 'Le mot de passe doit comprendre au moins une lettre.',
                        'requireCaseDiff' => true,
                        'requireCaseDiffMessage' => 'Le mot de passe doit inclure des lettres majuscules et minuscules.',
                        'requireNumbers' => true,
                        'missingNumbersMessage' => 'Le mot de passe doit inclure au moins un chiffre.',
                        'requireSpecialCharacter' => true,
                        'missingSpecialCharacterMessage' => 'Le mot de passe doit contenir au moins un caractère spécial.',
                    ])
                ],
                'invalid_message' => 'Les mots de passe doivent être identiques',
                'type' => PasswordType::class,
                'first_options'  => array(
                    'label' => 'Mot de passe<span class="text-danger"> *</span>',
                    'label_html' => true,
                    'help' => 'Le mot de passe doit être de 8 caractères minimum et contenir au moins 1 chiffre, 1 lettre, 1 majuscule et 1 caractère spécial',
                    'row_attr' => [
                        'class' => 'col-md-6'
                    ],
                ),
                'second_options' => array(
                    'label' => 'Répétez le mot de passe<span class="text-danger"> *</span>',
                    'label_html' => true,
                    'row_attr' => [
                        'class' => 'col-md-6'
                    ],
                ),

            ))
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN'
                ],
                'expanded' => true,
                'multiple' => true,
                'label' => 'Rôle(s)<span class="text-danger"> *</span>',
                'label_html' => true,
                'help' => 'Attention ! Le role administrateur permet de modifier toutes les données du site',
                'row_attr' => [
                    'class' => 'col-md-6'
                ],
            ])
            ->add('address', AddressType::class, [
                'constraints' => [
                    new Valid(),
                ],
                'label' => '<h2 class="col-12 px-0">Adresse</h2>',
                'label_html' => true,
            ])
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
            'data_class' => User::class,
                'constraints'     => array(
                    new UniqueEntity(array(
                        'fields' => array('email'),
                        'message' => 'Cette adresse e-mail est déjà associée à un compte'
                    )),
                    new UniqueEntity(array(
                        'fields' => array('username'),
                        'message' => 'Ce pseudo est est déjà associé à un compte'
                    ))
                )
        ]);
    }


}
