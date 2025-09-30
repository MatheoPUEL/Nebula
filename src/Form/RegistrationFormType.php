<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Username',
                    'class' => 'form-control form-control-lg ps-5',
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Please enter a username'
                    ),
                    new Length(
                        min: 3,
                        max: 50,
                        minMessage: 'Username should be at least {{ limit }} characters'
                    ),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => false,
                'attr' => [
                    'label' => false,
                    'placeholder' => 'Email',
                    'class' => 'form-control form-control-lg ps-5',
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Please enter a email'
                    ),
                    new Length(
                        min: 5,
                        max: 180,
                        minMessage: 'Email should be at least {{ limit }} characters'
                    ),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue(
                        message: 'You should agree to our terms.'
                    ),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => false,
                'mapped' => false,
                'attr' => ['placeholder'=> 'Password','autocomplete' => 'new-password', 'class' => 'form-control'],
                'constraints' => [
                    new NotBlank(
                        message: 'Please enter a password'
                    ),
                    new Length(
                        min: 6,
                        max: 4096,
                        minMessage: 'Your password should be at least {{ limit }} characters'
                    ),
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
