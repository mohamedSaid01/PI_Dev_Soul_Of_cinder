<?php

// src/Form/MedecinRegistrationFormType.php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GroupSequence;
use App\Enum\Gender;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Enum\Specialite;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\Range; // <-- Ajout de l'importation manquante

class MedecinRegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer une adresse email.']),
                ],
            ])
            ->add('firstName', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre prénom.']),
                ],
            ])
            ->add('lastName', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre nom.']),
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
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'Homme' => Gender::MALE,
                    'Femme' => Gender::FEMALE,
                ],
                'choice_value' => fn(?Gender $gender) => $gender?->value,
                'choice_label' => fn(Gender $gender) => ucfirst($gender->value),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => 'Sélectionner le genre',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner votre genre.']),
                ],
            ])
            ->add('adress', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre adresse.']),
                ],
            ])
            ->add('phoneNumber', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre numéro de téléphone.']),
                    new Regex([
                        'pattern' => '/^\+?\d{8,15}$/',
                        'message' => 'Le numéro de téléphone doit être valide.',
                    ]),
                ],
            ])
            ->add('numeroLicence', TextType::class, [
                'label' => 'Numéro de licence',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre numéro de licence.']),
                ],
            ])
            ->add('specialite', ChoiceType::class, [
                'label' => 'Spécialité',
                'choices' => Specialite::cases(),
                'choice_label' => function (Specialite $specialite) {
                    return $specialite->value;
                },
                'placeholder' => 'Sélectionner la spécialité',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner votre spécialité.']),
                ],
            ])
            ->add('age', IntegerType::class, [
                'label' => 'Âge',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre âge.']),
                    new Range([ // <-- Utilisation de la classe Range
                        'min' => 1,
                        'max' => 120,
                        'notInRangeMessage' => 'L\'âge doit être compris entre {{ min }} et {{ max }}.',
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'Accepter les conditions',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions.',
                    ]),
                ],
            ]);

        // Ajouter un événement pour formater le numéro de licence avant la soumission
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $user = $event->getData();
            $numeroLicence = $user->getNumeroLicence();

            // Convertit en majuscules et supprime les espaces
            if ($numeroLicence) {
                $user->setNumeroLicence(strtoupper(str_replace(' ', '', $numeroLicence)));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => new GroupSequence(['RegistrationMedecin']),
        ]);
    }
}