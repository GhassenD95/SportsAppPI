<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\PasswordResetToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Twilio\Rest\Client;

class PasswordResetService
{
    private $entityManager;
    private $requestStack;
    private $twilioClient;
    private $tokenGenerator;

    public function __construct(
        EntityManagerInterface $entityManager, 
        TokenGeneratorInterface $tokenGenerator,
        RequestStack $requestStack
    ) {
        $this->entityManager = $entityManager;
        $this->tokenGenerator = $tokenGenerator;
        $this->requestStack = $requestStack;
        $this->twilioClient = new Client(
            $_ENV['TWILIO_ACCOUNT_SID'], 
            $_ENV['TWILIO_AUTH_TOKEN']
        );
    }

    public function initiatePasswordReset(User $user): bool
    {
        // Extensive logging for debugging
        $logFile = '/tmp/password_reset_debug.log';
        $this->logToFile($logFile, '--- Starting Password Reset Process ---');
        $this->logToFile($logFile, 'User Email: ' . $user->getEmail());

        // Store email in session for verification
        $session = $this->requestStack->getCurrentRequest()->getSession();
        $session->set('reset_email', $user->getEmail());
        
        // Generate a reset token (6 digits for SMS)
        $resetToken = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->logToFile($logFile, 'Generated Reset Token: ' . $resetToken);
        
        // Find user's verified phone number
        $verifiedPhones = $user->getPhones()->filter(function($phone) {
            return $phone->isVerified() === true;
        });

        $this->logToFile($logFile, 'Total Verified Phones: ' . $verifiedPhones->count());

        if ($verifiedPhones->isEmpty()) {
            // Log that no verified phone was found
            $this->logToFile($logFile, 'No verified phone number found for user');
            error_log('No verified phone number found for user: ' . $user->getEmail());
            return false;
        }

        // Take the first verified phone
        $phone = $verifiedPhones->first();
        $this->logToFile($logFile, 'Selected Phone Number: ' . $phone->getNumber());
        $this->logToFile($logFile, 'Phone Verified Status: ' . ($phone->isVerified() ? 'Yes' : 'No'));

        // Normalize phone number
        $phoneNumber = preg_replace('/\D/', '', $phone->getNumber());
        $this->logToFile($logFile, 'Phone Number After Digit Removal: ' . $phoneNumber);

        // Ensure the phone number starts with country code
        if (substr($phoneNumber, 0, 3) !== '216') {
            $phoneNumber = '216' . $phoneNumber;
        }
        
        // Trim to ensure we don't have extra digits
        $phoneNumber = substr($phoneNumber, 0, 12);
        $phoneNumber = '+' . $phoneNumber;
        
        $this->logToFile($logFile, 'Final Normalized Phone Number: ' . $phoneNumber);

        // Create or update password reset token
        $passwordResetToken = $user->getPasswordResetToken() ?? new PasswordResetToken($user);
        $passwordResetToken->setToken($resetToken);
        $passwordResetToken->setExpiresAt(new \DateTimeImmutable('+15 minutes'));
        
        $user->setPasswordResetToken($passwordResetToken);

        // Send SMS with reset token
        try {
            // Validate phone number format
            if (!preg_match('/^\+?[1-9]\d{1,14}$/', $phoneNumber)) {
                throw new \InvalidArgumentException('Invalid phone number format: ' . $phoneNumber);
            }

            // Log Twilio configuration details
            $this->logToFile($logFile, 'Twilio Account SID: ' . $_ENV['TWILIO_ACCOUNT_SID']);
            $this->logToFile($logFile, 'Twilio Phone Number: ' . $_ENV['TWILIO_PHONE_NUMBER']);

            // Attempt to send SMS
            $message = $this->twilioClient->messages->create(
                $phoneNumber,
                [
                    'from' => $_ENV['TWILIO_PHONE_NUMBER'],
                    'body' => "Your password reset code is: $resetToken. This code will expire in 15 minutes."
                ]
            );

            // Log successful SMS sending
            $this->logToFile($logFile, 'SMS Sent Successfully');
            $this->logToFile($logFile, 'SMS SID: ' . $message->sid);
            $this->logToFile($logFile, 'Sent To: ' . $phoneNumber);

            $this->entityManager->persist($passwordResetToken);
            $this->entityManager->flush();

            return true;
        } catch (\Twilio\Exceptions\RestException $twilioError) {
            // Specific Twilio error handling
            $this->logToFile($logFile, 'Twilio API Error');
            $this->logToFile($logFile, 'Error Code: ' . $twilioError->getCode());
            $this->logToFile($logFile, 'Error Message: ' . $twilioError->getMessage());
            $this->logToFile($logFile, 'Phone Number: ' . $phoneNumber);

            error_log('Twilio API Error: ' . $twilioError->getCode());
            error_log('Twilio Error Message: ' . $twilioError->getMessage());
            error_log('Phone Number: ' . $phoneNumber);
            return false;
        } catch (\Exception $e) {
            // Catch-all for other exceptions
            $this->logToFile($logFile, 'CRITICAL: Failed to send SMS');
            $this->logToFile($logFile, 'Error Type: ' . get_class($e));
            $this->logToFile($logFile, 'Error Message: ' . $e->getMessage());
            $this->logToFile($logFile, 'Trace: ' . $e->getTraceAsString());
            $this->logToFile($logFile, 'Phone Number: ' . $phoneNumber);

            error_log('CRITICAL: Failed to send SMS for password reset');
            error_log('Error Type: ' . get_class($e));
            error_log('Error Message: ' . $e->getMessage());
            error_log('Trace: ' . $e->getTraceAsString());
            error_log('Phone Number: ' . $phoneNumber);
            error_log('Twilio Phone Number: ' . $_ENV['TWILIO_PHONE_NUMBER']);
            
            return false;
        }
    }

    /**
     * Helper method to log messages to a file
     */
    private function logToFile(string $filePath, string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($filePath, "[$timestamp] $message\n", FILE_APPEND);
        error_log($message);
    }

    public function validateResetToken(User $user, string $token): bool
    {
        $resetToken = $user->getPasswordResetToken();
        
        if (!$resetToken) {
            return false;
        }

        // Check token validity (15 minutes expiry)
        $expiresAt = $resetToken->getExpiresAt();
        $now = new \DateTimeImmutable();

        return $resetToken->getToken() === $token && 
               $now < $expiresAt; // Not expired
    }
}
