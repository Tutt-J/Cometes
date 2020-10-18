<?php
// src/Form/UserType.php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\IsTrue;
use App\Form\AddressType;
use Symfony\Component\Validator\Constraints\NotBlank;


class RegistrationFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('roles')
            ->remove('save')
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'J\'ai lu et j\'accepte la <a href="/politique-de-confidentialite">politique de confidentialité</a><span class="text-danger"> *</span>' ,
                'label_html' => true,
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter notre politique de confidentialité',
                    ]),
                ],
                'row_attr' => [
                    'class' => 'col-md-12'
                ],
            ])
            ->add('agreeCGV', CheckboxType::class, [
                'label' => 'J\'ai lu et j\'accepte les <a href="/conditions-generales-de-vente">Conditions Générales de Vente</a><span class="text-danger"> *</span>',
                'label_html' => true,
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter nos Conditions Générales de vente',
                    ]),
                ],
                'row_attr' => [
                    'class' => 'col-md-12'
                ],
            ])
            ->add('subscribeNews', CheckboxType::class, array(
                'label' => 'J\'accepte de recevoir les dernières actualités et prochains évènements par e-mail.',
                'label_html' => true,
                'required' => false,
                'data' => false,
                'mapped' => false,
                'row_attr' => [
                    'class' => 'col-md-12'
                ],
            ))
            ->add('save', SubmitType::class, [
                'label' => 'Créer mon compte',
                'row_attr' => [
                    'class' => 'col-md-12 text-right'
                ],
            ]);

    }

    public function getParent()
    {
        return UserType::class;
    }
}
