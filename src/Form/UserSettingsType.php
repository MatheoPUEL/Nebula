<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class UserSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // --- DISPLAY NAME ---
            ->add('display_name', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Display name',
                    'class' => 'form-control form-control-lg ps-5',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a display name',
                    ]),
                    new Length([
                        'min' => 3,
                        'max' => 50,
                        'minMessage' => 'Display name should be at least {{ limit }} characters',
                    ]),
                ],
            ])

            // --- EMAIL ---
            ->add('email', EmailType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Email',
                    'class' => 'form-control form-control-lg ps-5',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter an email',
                    ]),
                    new Length([
                        'min' => 5,
                        'max' => 180,
                        'minMessage' => 'Email should be at least {{ limit }} characters',
                    ]),
                ],
            ])

            // --- AVATAR ---
            ->add('avatar', FileType::class, [
                'label' => 'Avatar (PNG, JPG, JPEG, GIF)',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control form-control-lg ps-5',
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                            'image/jpg',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file (PNG, JPG, JPEG, or GIF)',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
