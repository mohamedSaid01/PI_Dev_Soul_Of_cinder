<?php
// src/Form/RendezVousType.php
namespace App\Form;

use App\Entity\EtatRendezVous;
use App\Entity\RendezVous;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

class RendezVousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('date', DateType::class, [
            'label' => 'Date',
            'widget' => 'single_text',
            'html5' => true,
            'attr' => [
                'class' => 'form-control',
                'min' => (new \DateTime())->format('Y-m-d'),
                'max' => (new \DateTime('+1 month'))->format('Y-m-d'),
            ],
        ])
        ->add('heure', ChoiceType::class, [
            'label' => 'Heure',
            'choices' => $this->generateTimeSlots(8, 20, 30),
            'attr' => ['class' => 'form-select'],
        ])
        
        
            ->add('etat', EntityType::class, [
                'class' => EtatRendezVous::class,
                'choice_label' => 'libelle',
                'label' => 'Type de rendez-vous',
                'attr' => ['class' => 'form-select'],
                'choices' => $options['etat_choices'], // Utiliser les états filtrés
            ])
        
        ;
    }
    private function generateTimeSlots(int $startHour, int $endHour, int $interval)
    {
        $times = [];
        for ($hour = $startHour; $hour < $endHour; $hour++) {
            for ($minute = 0; $minute < 60; $minute += $interval) {
                $dateTime = new \DateTime(sprintf('%02d:%02d', $hour, $minute));
                $formattedTime = $dateTime->format('H:i');
                $times[$formattedTime] = $dateTime; // Stocker DateTime mais afficher H:i
            }
        }
        return $times;
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RendezVous::class,
            'etat_choices' => [], // Option pour passer les états filtrés
        ]);

        // Assurez-vous que 'etat_choices' est un tableau
        $resolver->setAllowedTypes('etat_choices', ['array']);
        $resolver->setRequired('medecin');
        $resolver->setAllowedTypes('medecin', ['null', 'App\Entity\Medecin']);
    }
}