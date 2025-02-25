<?php

// src/Service/EmailValidatorService.php
namespace App\Service;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;

class EmailValidatorService
{
    public function isValid(string $email): bool
    {
        $validator = new EmailValidator();
        return $validator->isValid($email, new RFCValidation());
    }
}