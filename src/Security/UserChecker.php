<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->getIsVerified()) {
            throw new CustomUserMessageAuthenticationException('Please verify your account before logging in.');
        }

        // Check the user status
        if ($user->getStatus() === 'non_verifie') {
            throw new CustomUserMessageAuthenticationException('Your account has not yet been accepted by an administrator. Please wait.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Ne rien faire ici
    }
}