<?php
// src/Form/UserType.php
namespace App\Form;

use App\Entity\Image;
use App\Entity\User;
use App\Repository\ImageRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use App\Form\AddressType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ImageType
 * @package App\Form
 */
class ImageType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', FileType::class, array(
                'data_class' => null,
                'label' => 'Choisissez un fichier',
                'constraints' => [
                    new File([
                        'maxSize' => '10240000k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'L\'image doit être au format jpg ou png',
                    ])
                ]
            ))
            ->add('alt', TextType::class, array(
                'label' => 'Décrivez l\'image',
                'attr' => array('maxlength' => 255)
            ))
            ->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (FormEvent $event) {
                    if (isset(($event->getData())['url'])) {
                        $form = $event->getForm();
                        $form->add('alt', TextType::class, array(
                            'required' => true,
                            'label' => 'Décrivez l\'image',
                            'attr' => array('maxlength' => 255),
                            'constraints' => [
                                new NotBlank([
                                    'message' => "Ce champ est obligatoire si vous choisissez une image."
                                ])
                            ]
                        ));
                    }
                }
            )

        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                                   'data_class' => Image::class,
                                   'cascade_validation' => true,
                               ));
    }
}
