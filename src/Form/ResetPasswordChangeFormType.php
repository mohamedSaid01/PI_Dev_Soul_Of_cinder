<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ResetPasswordChangeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', PasswordType::class, [
                'label' => 'New password',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096, // Maximum length allowed by Symfony for security reasons
                    ]),
                ],
            ])
            ->add('confirmPassword', PasswordType::class, [
                'label' => 'Confirm new password',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please confirm your password',
                    ]),
                    new Callback([$this, 'validatePasswordConfirmation']), // Validation personnalisée
                ],
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }

    /**
     * Validation personnalisée pour vérifier que les deux mots de passe correspondent.
     */
    public function validatePasswordConfirmation($value, ExecutionContextInterface $context)
    {
        $form = $context->getRoot(); // Récupère le formulaire
        $password = $form->get('password')->getData(); // Récupère la valeur du champ "password"
        $confirmPassword = $value; // Récupère la valeur du champ "confirmPassword"

        if ($password !== $confirmPassword) {
            $context->buildViolation('The passwords do not match.')
                ->atPath('confirmPassword') // Associe l'erreur au champ "confirmPassword"
                ->addViolation();
        }
    }
}