<?php

namespace App\Service;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Repository\ResetPasswordRequestRepository;
use Doctrine\ORM\EntityManagerInterface;

class ResetPasswordService
{
    private $entityManager;
    private $repository;

    public function __construct(EntityManagerInterface $entityManager, ResetPasswordRequestRepository $repository)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function generateVerificationCode(int $length = 4): string
    {
        $characters = '0123456789';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $code;
    }

    public function createResetPasswordRequest(User $user): ResetPasswordRequest
    {
        $code = $this->generateVerificationCode();
        $expiresAt = new \DateTimeImmutable('+1 hour'); // Utilise DateTimeImmutable

        // Crée une nouvelle demande de réinitialisation
        $resetRequest = new ResetPasswordRequest($user, $expiresAt, $code, bin2hex(random_bytes(20)));
        $this->entityManager->persist($resetRequest);
        $this->entityManager->flush();

        return $resetRequest;
    }


   public function validateResetPasswordRequest(User $user, string $code): bool
{
    // Récupère la demande de réinitialisation pour cet utilisateur
    $resetRequest = $this->repository->findOneBy(['user' => $user]);

    if (!$resetRequest) {
        error_log('No reset request found for user: ' . $user->getId());
        return false;
    }

    if ($resetRequest->getCode() !== $code) {
        error_log('Invalid code for user: ' . $user->getId());
        return false;
    }

    if ($resetRequest->getExpiresAt() < new \DateTimeImmutable()) {
        error_log('Expired code for user: ' . $user->getId());
        return false;
    }

    return true;
}
}