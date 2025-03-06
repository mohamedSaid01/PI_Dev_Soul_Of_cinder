<?php

namespace App\Form;

use App\Entity\Medecin;
use App\Entity\Reclamation;
use App\Entity\TypeReclamation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class Reclamation1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description')
            ->add('date_reclamation', DateTimeType::class, [
                            'widget' => 'single_text',
                            'label' => 'Date de reclamation',
                            'disabled' => true, // Empêche la modification de la date
                        ])
                        ->add('addidmedcein', CheckboxType::class, [
                            'mapped' => false, // Ce champ ne sera pas stocké en base de données
                            'required' => false,
                            'label' => 'Médecin ?',
                            'attr' => ['id' => 'addMedecinCheckbox'], // Ajouter un ID pour le JavaScript
                        ])
                        ->add('medecin', EntityType::class, [ // Use 'medecin' instead of 'medecin_id'
                            'class' => Medecin::class,
                            // 'choice_label' => 'nom',
                            'placeholder' => 'Select a medecin',
                            'required' => false,
                        ])
            //->add('photo')
            ->add('typeReclamation', EntityType::class, [
                'class' => TypeReclamation::class,
                'choice_label' => 'type_reclamation',
                'placeholder' => 'Sélectionnez un type de réclamation',
                'required' => false,
            ])

            
            ->add('addPhoto', CheckboxType::class, [
                'mapped' => false, // Ce champ ne sera pas stocké en base de données
                'required' => false,
                'label' => 'Ajouter une photo ?',
                'attr' => ['id' => 'addPhotoCheckbox'], // Ajouter un ID pour le JavaScript
            ])
            
            ->add('addPhoto', CheckboxType::class, [
                            'mapped' => false, // Ce champ ne sera pas stocké en base de données
                            'required' => false,
                            'label' => 'Ajouter une photo ?',
                        ])
                        ->add('photo', FileType::class, [
                            'label' => 'Photo',
                            'mapped' => false,
                            'required' => false,
                            'constraints' => [
                                new Image([
                                    'maxSize' => '1024k',
                                    'mimeTypes' => ['image/*'],
                                    'mimeTypesMessage' => 'Veuillez télécharger une image valide',
                                ])
                            ],
                            //'attr' => ['class' => 'photo-input', 'style' => 'display: none;'], // Caché par défaut
                        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}