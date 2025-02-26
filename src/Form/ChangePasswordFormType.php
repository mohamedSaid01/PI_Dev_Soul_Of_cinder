<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\Regex;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $passwordConstraints = [
            new NotBlank([
                'message' => 'Veuillez entrer un mot de passe.',
            ]),
            new Length([
                'min' => 8,
                'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères.',
                'max' => 4096, // Longueur maximale recommandée par Symfony
            ]),
            new Regex([
                'pattern' => '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/',
                'message' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.',
            ]),
            new NotCompromisedPassword([
                'message' => 'Ce mot de passe a été compromis dans une fuite de données. Veuillez en choisir un autre.',
            ]),
        ];

        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options' => [
                    'constraints' => $passwordConstraints,
                    'label' => 'Nouveau mot de passe',
                ],
                'second_options' => [
                    'constraints' => $passwordConstraints,
                    'label' => 'Répétez le mot de passe',
                ],
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
