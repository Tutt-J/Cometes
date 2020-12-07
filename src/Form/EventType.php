<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\IsTrue;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('promoCode', TextType::class, [
                'label' => 'Code promotionnel ou carte cadeau',
                'mapped' => false,
                'required' => false
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'J\'ai lu et j\'accepte les <a href="/conditions-particulieres-pour-les-evenements-et-sejours">conditions particulières pour les évènements et séjours</a><span class="text-danger"> *</span>' ,
                'label_html' => true,
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter nos conditions particulières pour les évènements et séjours.',
                    ]),
                ],
                'row_attr' => [
                    'class' => 'text-center'
                ],
            ])
            ->add('agreeCgv', CheckboxType::class, [
                'label' => 'J\'ai lu et j\'accepte les <a href="conditions-generales-de-ventes">conditions générales de vente</a><span class="text-danger"> *</span>' ,
                'label_html' => true,
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter nos conditions générales de ventes.',
                    ]),
                ],
                'row_attr' => [
                    'class' => 'text-center'
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'M\'inscrire',
                'row_attr' => [
                    'class' => 'text-center'
                ],
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
        ;
    }
}
