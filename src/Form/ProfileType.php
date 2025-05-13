<?php

// src/Form/ProfileType.php
namespace App\Form;

use App\Entity\User;
use App\Enum\Gender;
use App\Enum\Specialite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Téléphone',
            ])
            ->add('adress', TextType::class, [
                'label' => 'Adresse',
            ])
            ->add('age', IntegerType::class, [
                'label' => 'Âge',
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'Genre',
                'choices' => [
                    'Homme' => Gender::MALE,
                    'Femme' => Gender::FEMALE,
                ],
                'choice_value' => fn(?Gender $gender) => $gender?->value, // Utilisez la valeur de l'enum
                'choice_label' => fn(Gender $gender) => match ($gender) {
                    Gender::MALE => 'Homme',
                    Gender::FEMALE => 'Femme',
                },
                'placeholder' => 'Sélectionner le genre',
                'attr' => ['class' => 'form-control'],
            ]);

        // Ajouter les champs spécifiques au médecin
        if (in_array('ROLE_MEDECIN', $options['user']->getRoles())) {
            $builder
            ->add('specialite', ChoiceType::class, [
                'label' => 'Spécialité',
                'choices' => Specialite::cases(),
                'choice_label' => function (Specialite $specialite) {
                    return $specialite->value;
                },
                'placeholder' => 'Sélectionner la spécialité',
                'required' => true,
            ])
                ->add('numeroLicence', TextType::class, [
                    'label' => 'Numéro de Licence',
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'user' => null, // Passer l'utilisateur connecté en option
        ]);
    }
}