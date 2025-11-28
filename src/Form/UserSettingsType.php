<?php

namespace App\Form;

use App\Entity\Country;
use App\Entity\TimeZone;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

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
                'empty_data' => '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please enter a display name',
                    ]),
                    new Assert\NotNull([
                        'message' => 'Display name cannot be null',
                    ]),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 50,
                        'minMessage' => 'Display name should be at least {{ limit }} characters',
                        'maxMessage' => 'Display name should be at most {{ limit }} characters',
                    ]),
                ],
            ])

            // --- EMAIL ---
            ->add('email', EmailType::class, [
                'label' => false,
                'empty_data' => '',
                'attr' => [
                    'placeholder' => 'Email',
                    'class' => 'form-control form-control-lg ps-5',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please enter an email',
                    ]),
                    new Assert\NotNull([
                        'message' => 'Email cannot be null',
                    ]),
                    new Assert\Length([
                        'min' => 5,
                        'max' => 180,
                        'minMessage' => 'Email should be at least {{ limit }} characters',
                        'maxMessage' => 'Email should be at most {{ limit }} characters',
                    ]),
                    new Assert\Email([
                        'message' => 'Please enter a valid email address',
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
                    new Assert\File([
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

            // --- TIMEZONE ---
            ->add('timezone', EntityType::class, [
                'empty_data' => '',
                'class' => TimeZone::class,
                'choice_label' => 'name',
                'placeholder' => 'Time zone',
                'label' => 'Time zone',
                'attr' => [
                    'class' => 'form-control form-control-lg ps-5',
                ],

            ])

            // --- COUNTRY ---
            ->add('country', EntityType::class, [
                'empty_data' => '',
                'class' => Country::class,
                'choice_label' => 'nicename',
                'placeholder' => 'Country',
                'label' => 'Country',
                'attr' => [
                    'class' => 'form-control form-control-lg ps-5',
                ],

            ])

            // --- PUBLIC PROFILE ---
            ->add('isPublic', CheckboxType::class, [
                'label' => 'Public profile',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                ],
                'constraints' => [
                    new Assert\NotNull([
                        'message' => 'Public profile cannot be null',
                    ]),
                ],
            ])

            // --- DESCRIPTION ---
            ->add('description', TextareaType::class, [
                'empty_data' => '',
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Your description here',
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 350,
                        'maxMessage' => 'Description should be at most {{ limit }} characters',
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
