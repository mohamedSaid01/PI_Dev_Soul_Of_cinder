<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType; // Import nécessaire


class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('desciption', TextareaType::class, [
                'label' => 'desciption',
                'required' => false, // ou true selon ton besoin
                'attr' => [
                    'placeholder' => 'Entrez une description...',
                    'class' => 'form-control', // Pour Bootstrap si nécessaire
                    'rows' => 5
                ]
            ])
                        ->add('price')
                       
                        ->add('image', FileType::class, [
                            'label' => 'Image',
                            'mapped' => false, // Ne pas lier directement à l'entité
                            'required' => true, // Modifier selon besoin
                            'attr' => [
                                'class' => 'form-control', // Bootstrap
                            ]
                        ])
                        
            ->add('quantity')
            ->add('Category')
            ->add('submit', SubmitType::class, [
                'label' => 'Valider',
                'attr' => [
                    'class' => 'btn btn-primary' // Bootstrap si utilisé
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}