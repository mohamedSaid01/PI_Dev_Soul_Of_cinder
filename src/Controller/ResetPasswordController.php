<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPasswordRequestFormType;
use App\Form\ResetPasswordVerifyCodeFormType;
use App\Form\ResetPasswordChangeFormType;
use App\Service\ResetPasswordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; 
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class ResetPasswordController extends AbstractController
{
    private $resetPasswordService;
    private $entityManager;
    private $mailer;

    public function __construct(ResetPasswordService $resetPasswordService, EntityManagerInterface $entityManager, MailerInterface $mailer)
    {
        $this->resetPasswordService = $resetPasswordService;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    #[Route('/reset-password', name: 'app_reset_password_request')]
public function request(Request $request, ResetPasswordService $resetPasswordService): Response
{
    $form = $this->createForm(ResetPasswordRequestFormType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $email = $form->get('email')->getData();
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($user) {
            // Stocke l'ID de l'utilisateur dans la session
            $request->getSession()->set('reset_password_user_id', $user->getId());

            $resetRequest = $resetPasswordService->createResetPasswordRequest($user);

            // Send the code via email
$email = (new TemplatedEmail())
->from('mohamedsaidboubaker10@gmail.com')
->to($user->getEmail())
->subject('Your Verification Code - SAHATECK')
->htmlTemplate('emails/verification_code.html.twig') // Use the Twig template
->context([
    'user' => $user, // Pass the user to the template
    'resetRequest' => $resetRequest, // Pass the reset request to the template
]);

            $this->mailer->send($email);

            $this->addFlash('success', 'A verification code has been sent to your email.');
            return $this->redirectToRoute('app_reset_password_verify_code');
        }

        $this->addFlash('error', 'User not found.');
    }

    return $this->render('reset_password/request.html.twig', [
        'form' => $form->createView(),
    ]);
}

#[Route('/reset-password/verify-code', name: 'app_reset_password_verify_code')]
public function verifyCode(Request $request, ResetPasswordService $resetPasswordService): Response
{
    $form = $this->createForm(ResetPasswordVerifyCodeFormType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        if ($form->isValid()) {
            // Le formulaire est valide
            $code = $form->get('code')->getData();

            // Récupère l'ID de l'utilisateur depuis la session
            $userId = $request->getSession()->get('reset_password_user_id');
            if (!$userId) {
                $this->addFlash('error', 'Invalid request.');
                return $this->redirectToRoute('app_reset_password_request');
            }

            // Récupère l'utilisateur à partir de l'ID
            $user = $this->entityManager->getRepository(User::class)->find($userId);
            if (!$user) {
                $this->addFlash('error', 'User not found.');
                return $this->redirectToRoute('app_reset_password_request');
            }

            // Valide le code de vérification
            if ($resetPasswordService->validateResetPasswordRequest($user, $code)) {
                error_log('Code is valid. Redirecting to change password page.');
                return $this->redirectToRoute('app_reset_password_change');
            } else {
                error_log('Invalid or expired code.');
                $this->addFlash('error', 'Invalid or expired code.');
            }
        } else {
            // Le formulaire n'est pas valide
            $this->addFlash('error', 'Please correct the errors in the form.');
        }
    }

    return $this->render('reset_password/verify_code.html.twig', [
        'form' => $form->createView(),
    ]);
}

#[Route('/reset-password/change', name: 'app_reset_password_change')]
public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher): Response
{
    $form = $this->createForm(ResetPasswordChangeFormType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Récupère l'ID de l'utilisateur depuis la session
        $userId = $request->getSession()->get('reset_password_user_id');
        if (!$userId) {
            $this->addFlash('error', 'Invalid request.');
            return $this->redirectToRoute('app_reset_password_request');
        }

        // Récupère l'utilisateur à partir de l'ID
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('app_reset_password_request');
        }

        // Encode et sauvegarde le nouveau mot de passe
        $newPassword = $form->get('password')->getData(); // Utilise uniquement le champ "password"
        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
        $this->entityManager->flush();

        // Nettoie la session
        $request->getSession()->remove('reset_password_user_id');

        $this->addFlash('success', 'Your password has been changed.');
        return $this->redirectToRoute('app_login');
    }

    return $this->render('reset_password/change_password.html.twig', [
        'form' => $form->createView(),
    ]);
}
}