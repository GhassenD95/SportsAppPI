<?php

namespace App\Service;

use Twilio\Rest\Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TwilioSmsService
{
    private Client $client;
    private string $twilioNumber;

    public function __construct(
        private ParameterBagInterface $params
    ) {
        $accountSid = $this->params->get('env(TWILIO_ACCOUNT_SID)');
        $authToken = $this->params->get('env(TWILIO_AUTH_TOKEN)');
        $this->twilioNumber = $this->params->get('env(TWILIO_PHONE_NUMBER)');

        $this->client = new Client($accountSid, $authToken);
    }

    /**
     * Send an SMS message
     *
     * @param string $to Recipient's phone number (E.164 format)
     * @param string $message Message content
     * @return bool Whether the message was sent successfully
     */
    public function sendSms(string $to, string $message): bool
    {
        try {
            $this->client->messages->create(
                $to,
                [
                    'from' => $this->twilioNumber,
                    'body' => $message
                ]
            );
            return true;
        } catch (\Exception $e) {
            // Log the error
            error_log('Twilio SMS Send Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a random verification code
     *
     * @param int $length
     * @return string
     */
    public function generateVerificationCode(int $length = 6): string
    {
        return str_pad(
            (string)random_int(0, pow(10, $length) - 1), 
            $length, 
            '0', 
            STR_PAD_LEFT
        );
    }

    /**
     * Send a verification code
     *
     * @param string $to Recipient's phone number
     * @param string $code Verification code
     * @return bool
     */
    public function sendVerificationCode(string $to, string $code): bool
    {
        $message = "Your verification code is: {$code}";
        return $this->sendSms($to, $message);
    }
}
