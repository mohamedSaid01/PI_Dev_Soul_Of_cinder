<?php

// src/Form/PatientType.php

namespace App\Form;

use App\Entity\User;
use App\Enum\Gender; // Importez l'enum Gender
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GroupSequence;

class PatientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez l\'email'],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le prénom'],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le nom'],
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
            ])
            ->add('adress', TextType::class, [
                'label' => 'Adresse',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez l\'adresse'],
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Téléphone',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le numéro de téléphone'],
            ])
            ->add('age', IntegerType::class, [
                'label' => 'Âge',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez l\'âge'],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotBlank([
                        'message' => 'Veuillez entrer votre âge.',
                    ]),
                    new \Symfony\Component\Validator\Constraints\Range([
                        'min' => 1,
                        'max' => 120,
                        'notInRangeMessage' => 'L\'âge doit être compris entre {{ min }} et {{ max }}.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => new GroupSequence(['RegistrationPatient']),
        ]);
    }
}