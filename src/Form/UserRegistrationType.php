<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<User>
 */
class UserRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'L\'adresse mail ne peut pas être vide.',
                    ]),
                ],
                'required' => true,
                'label' => 'Adresse mail',
            ])
            ->add('password', PasswordType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le mot de passe ne peut pas être vide.',
                    ]),
                    new Assert\Length([
                        'min' => 8,
                        'minMessage' => 'Le mot de passe doit contenir au moins 8 caractères.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+{}\[\]:;<>,.?~\\/-])/',
                        'message' => 'Le mot de passe doit contenir au moins une lettre majuscule, un chiffre et un caractère spécial.',
                    ]),
                ],
                'required' => true,
                'label' => 'Mot de passe',
            ])
            ->add('firstName', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le prénom ne peut pas être vide.',
                    ]),
                    new Assert\Length([
                        'max' => 100,
                        'maxMessage' => 'Le prénom ne peut pas dépasser 100 caractères.',
                    ]),
                ],
                'required' => true,
                'label' => 'Prénom',
            ])
            ->add('lastName', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nom ne peut pas être vide.',
                    ]),
                    new Assert\Length([
                        'max' => 100,
                        'maxMessage' => 'Le nom ne peut pas dépasser 100 caractères.',
                    ]),
                ],
                'required' => true,
                'label' => 'Nom',
            ])
            ->add('save', SubmitType::class, [
                'label' => "S'inscrire",
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
