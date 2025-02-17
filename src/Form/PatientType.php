<?php

// src/Form/PatientType.php

namespace App\Form;

use App\Entity\User;
use App\Enum\Gender;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Email;


use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class PatientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer une adresse email.']),
                    new Email(['message' => 'L\'email "{{ value }}" n\'est pas valide.']),
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
            ->add('age', IntegerType::class, [
                'label' => 'Âge',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre âge.']),
                    new Range([
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