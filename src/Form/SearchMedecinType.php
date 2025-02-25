<?php

// src/Form/SearchMedecinType.php
namespace App\Form;

use App\Enum\Specialite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchMedecinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('specialite', ChoiceType::class, [
                'label' => 'Spécialité',
                'choices' => array_combine(Specialite::getChoices(), Specialite::getChoices()),
                'placeholder' => 'Choisir une spécialité',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}