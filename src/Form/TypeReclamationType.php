<?php

namespace App\Form;

use App\Entity\TypeReclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TypeReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type_reclamation', ChoiceType::class, [
            'choices'  => [
                'Rendez-vous' => 'rendez_vous',
                'Produit' => 'produit',
                 'autre' => 'autre',
            ],
            'attr' => ['class' => 'form-control'],
            'label' => 'Type de Réclamation',
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TypeReclamation::class,
        ]);
    }
}