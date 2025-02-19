<?php

namespace App\Form;

use App\Entity\PostCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PostCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', TextType::class, [
            'label' => 'Category Name',
            'attr' => [
                'placeholder' => 'Enter category name',
            ],
            'empty_data' => '', // Ensure empty data is treated as an empty string
        ])
        ->add('description', TextareaType::class, [
            'label' => 'Description',
            'attr' => [
                'placeholder' => 'Enter description',
                'rows' => 4,
            ],
            'empty_data' => '', // Ensure empty data is treated as an empty string
        ]);
}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PostCategory::class,
        ]);
    }
}
