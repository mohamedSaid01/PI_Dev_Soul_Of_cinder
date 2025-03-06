<?php

// src/Service/SmsService.php

namespace App\Service;

use Twilio\Rest\Client;

class SmsService
{
    private $twilioSid;
    private $twilioAuthToken;
    private $twilioPhoneNumber;

    public function __construct(string $twilioSid, string $twilioAuthToken, string $twilioPhoneNumber)
    {
        $this->twilioSid = $twilioSid;
        $this->twilioAuthToken = $twilioAuthToken;
        $this->twilioPhoneNumber = $twilioPhoneNumber;
    }

    public function sendSms(string $to, string $message): void
    {
        // Validez le numéro au format E.164
        if (!preg_match('/^\+?[1-9]\d{1,14}$/', $to)) {
            throw new \InvalidArgumentException('Le numéro de téléphone doit être au format E.164 (ex: +216XXXXXXXX).');
        }
    
        $client = new Client($this->twilioSid, $this->twilioAuthToken);
    
        try {
            $client->messages->create(
                $to,
                [
                    'from' => $this->twilioPhoneNumber,
                    'body' => $message,
                ]
            );
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de l\'envoi du SMS : ' . $e->getMessage());
        }
    }
}