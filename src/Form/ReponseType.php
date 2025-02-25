<?php

namespace App\Form;

use App\Entity\Reponse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class ReponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contenu')
            ->add('date_reponse', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de reclamation',
                'disabled' => true, // Empêche la modification de la date
            ])     
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reponse::class,
        ]);
    }
}


// <?php

// namespace App\Form;

// use Symfony\Component\Form\AbstractType;
// use Symfony\Component\Form\FormBuilderInterface;
// use Symfony\Component\OptionsResolver\OptionsResolver;
// use Symfony\Component\Form\Extension\Core\Type\SubmitType;

// class TypeReclamationType extends AbstractType
// {
//     public function buildForm(FormBuilderInterface $builder, array $options): void
//     {
//         $builder
//             ->add('rendez_vous', SubmitType::class, [
//                 'label' => 'Réclamation Rendez-vous',
//                 'attr' => [
//                     'class' => 'btn btn-primary',
//                 ]
//             ])
//             ->add('produit', SubmitType::class, [
//                 'label' => 'Réclamation Produit',
//                 'attr' => [
//                     'class' => 'btn btn-success',
//                 ]
//             ])
//             ->add('autre', SubmitType::class, [
//                 'label' => 'Autre Réclamation',
//                 'attr' => [
//                     'class' => 'btn btn-secondary',
//                 ]
//             ]);
//     }

//     public function configureOptions(OptionsResolver $resolver): void
//     {
//         $resolver->setDefaults([]);
//     }
// }

