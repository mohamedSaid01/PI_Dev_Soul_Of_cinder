<?php

// src/Form/MedecinRegistrationFormType.php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType; // Ajout du type IntegerType
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GroupSequence;
use App\Enum\Gender;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Enum\Specialite;

class MedecinRegistrationFormType extends AbstractType
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
                'placeholder' => 'Sélectionner le genre',
            ])
            ->add('adress', TextType::class)
            ->add('phoneNumber', TextType::class)
            ->add('numeroLicence', TextType::class, [
                'label' => 'Numéro de licence',
                'required' => true,
            ])
            ->add('specialite', ChoiceType::class, [
                'label' => 'Spécialité',
                'choices' => Specialite::cases(),
                'choice_label' => function (Specialite $specialite) {
                    return $specialite->value;
                },
                'placeholder' => 'Sélectionner la spécialité',
                'required' => true,
            ])
            ->add('age', IntegerType::class, [ // Ajout du champ age
                'label' => 'Âge',
                'required' => true,
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