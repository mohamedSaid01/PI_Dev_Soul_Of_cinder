<?php
// src/Form/RegistrationFormType.php

namespace App\Form;

use App\Entity\User;
use App\Enum\Gender;
use App\Enum\Specialite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\Range;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'Homme' => Gender::MALE,
                    'Femme' => Gender::FEMALE,
                ],
                'choice_value' => fn(?Gender $gender) => $gender?->value,
                'choice_label' => fn(Gender $gender) => ucfirst($gender->value),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => 'Sélectionnez le genre',
            ])
            ->add('adress', TextType::class)
            ->add('phoneNumber', TextType::class)
            ->add('age', IntegerType::class, [
                'label' => 'Âge',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre âge.',
                    ]),
                    new Range([
                        'min' => 1,
                        'max' => 120,
                        'notInRangeMessage' => 'L\'âge doit être compris entre {{ min }} et {{ max }}.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un mot de passe.']),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                        'max' => 4096,
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\d\s]).{8,}$/',
                        'message' => 'Le mot de passe doit contenir au moins une lettre minuscule, une lettre majuscule, un chiffre et un caractère spécial.',
                    ]),
                    new NotCompromisedPassword([
                        'message' => 'Ce mot de passe a été compromis dans une fuite de données. Veuillez en choisir un autre.',
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions.',
                    ]),
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Patient' => 'ROLE_PATIENT',
                    'Médecin' => 'ROLE_MEDECIN',
                ],
                'expanded' => false,
                'multiple' => false,
                'attr' => ['class' => 'form-control', 'id' => 'role-select'],
                'label' => 'Rôle',
                'mapped' => false, // Ne pas mapper directement au champ "roles" de l'entité
            ])
            ->add('numeroLicence', TextType::class, [
                'label' => 'Numéro de licence',
                'required' => false, // Facultatif, selon votre logique
                'attr' => ['class' => 'form-control medecin-field'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre numéro de licence.',
                        'groups' => ['RegistrationMedecin'],
                    ]),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'Le numéro de licence ne peut pas dépasser {{ limit }} caractères.',
                        'groups' => ['RegistrationMedecin'],
                    ]),
                    new Regex([
                        'pattern' => '/^[A-Z]{3}\d{5}$/',
                        'message' => 'Le numéro de licence doit être au format ABC12345 (3 lettres suivies de 5 chiffres).',
                        'groups' => ['RegistrationMedecin'],
                    ]),
                ],
            ])
            ->add('specialite', ChoiceType::class, [
                'label' => 'Spécialité',
                'required' => false, // Facultatif, selon votre logique
                'choices' => [
                    'Cardiologie' => Specialite::CARDIOLOGIE,
                    'Dermatologie' => Specialite::DERMATOLOGIE,
                    // Ajoutez d'autres spécialités ici
                ],
                'attr' => ['class' => 'form-control medecin-field'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner une spécialité.',
                        'groups' => ['RegistrationMedecin'],
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => ['RegistrationUser'],
        ]);
    }
}