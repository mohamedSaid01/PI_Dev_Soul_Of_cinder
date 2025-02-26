<?php

namespace App\Form;

use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Entity\Medecin;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
//use Symfony\Component\DomCrawler\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
// Ajoute cette ligne en haut de ton fichier (avant la déclaration de la classe) pour importer TextType

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ReclamationType extends AbstractType
{
    //public function buildForm(FormBuilderInterface $builder, array $options): void
    //{
    //    $builder
    //     /*->add('typereclamation', ChoiceType::class, [
    //         'choices' => [
    //             'Réclamation de rendez-vous' => 'rendez_vous',
    //             'Réclamation de produit' => 'produit',
    //             'Autre' => 'autre'
    //         ],
    //         'label' => 'Type de Réclamation'
    //     ])*/
    //         ->add('description')
    // ->add('addmedcein', CheckboxType::class, [
    //     'mapped' => false, // Ce champ ne sera pas stocké en base de données
    //     'required' => false,
    //     'label' => 'Médecin ?',
    // ])
    // ->add('medcein', TextType::class, [
    //     'label' => 'Nom du médecin',
    //      // Cacher ce champ par défaut
    // ])


    //         ->add('addPhoto', CheckboxType::class, [
    //             'mapped' => false, // Ce champ ne sera pas stocké en base de données
    //             'required' => false,
    //             'label' => 'Ajouter une photo ?',
    //         ])
    //         ->add('photo', FileType::class, [
    //             'label' => 'Photo',
    //             'mapped' => false,
    //             'required' => false,
    //             'constraints' => [
    //                 new Image([
    //                     'maxSize' => '1024k',
    //                     'mimeTypes' => ['image/*'],
    //                     'mimeTypesMessage' => 'Veuillez télécharger une image valide',
    //                 ])
    //             ],
    //             'attr' => ['class' => 'photo-input', 'style' => 'display: none;'], // Caché par défaut
    //         ])
    //         ->add('date_reclamation', DateTimeType::class, [
    //             'widget' => 'single_text',
    //             'label' => 'Date de reclamation',
    //             'disabled' => true, // Empêche la modification de la date
    //         ])
    //        /* ->add('id_reponse', EntityType::class, [
    //             'class' => Reponse::class,
    //             'choice_label' => 'id',
    //         ])*/
    //     ;
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Entrez la description de la réclamation',
                ],
            ])
            // Add other fields here
            ->add('date_reclamation', DateTimeType::class, [
                'label' => 'Date de réclamation',
            ])
            ->add('medecin', EntityType::class, [ // Use 'medecin' instead of 'medecin_id'
                'class' => Medecin::class,
                'choice_label' => 'nom',
                'placeholder' => 'Select a medecin',
                'required' => false,
            ])
            ->add('photo', FileType::class, [
                'label' => 'Photo',
                'required' => false,
            ]);
    }

    

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}
