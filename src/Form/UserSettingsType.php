<?php

namespace App\Form;

use App\Entity\Country;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
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
            ->add('country', EntityType::class, [
                'class' => Country::class,
                'choice_label' => 'nicename',
                'placeholder' => 'Country',
                'label' => 'Country',
                'attr' => [
                    'class' => 'form-control form-control-lg ps-5',
                ],
            ])

            ->add('description', TextareaType::class, [
            'label' => 'Description',
            'attr' => [
                'placeholder' => 'Your description here',
            ],
            'constraints' => [
                new Length([
                    'min' => 0,
                    'max' => 350,
                    'minMessage' => 'Description should be at least {{ limit }} characters',
                    'maxMessage' => 'Description should be at least {{ limit }} characters',
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
