<?php

namespace App\Service;

use App\Entity\Phone;
use App\Entity\User;
use App\Repository\PhoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class PhoneVerificationService
{
    private FilesystemAdapter $cache;

    public function __construct(
        private TwilioSmsService $twilioService,
        private PhoneRepository $phoneRepository,
        private EntityManagerInterface $entityManager
    ) {
        $this->cache = new FilesystemAdapter();
    }

    /**
     * Initiate phone verification
     *
     * @param User $user
     * @param string $phoneNumber
     * @return string Verification code
     */
    public function initiateVerification(User $user, string $phoneNumber): string
    {
        // Normalize phone number (you might want to add more robust validation)
        $phoneNumber = $this->normalizePhoneNumber($phoneNumber);

        // Generate verification code
        $verificationCode = $this->twilioService->generateVerificationCode();

        // Store verification code in cache
        $cacheItem = $this->cache->getItem("phone_verification_{$user->getId()}_{$phoneNumber}");
        $cacheItem->set([
            'code' => $verificationCode,
            'attempts' => 0
        ]);
        $cacheItem->expiresAfter(15 * 60); // 15 minutes
        $this->cache->save($cacheItem);

        // Send verification code via SMS
        $this->twilioService->sendVerificationCode($phoneNumber, $verificationCode);

        return $verificationCode;
    }

    /**
     * Verify phone number
     *
     * @param User $user
     * @param string $phoneNumber
     * @param string $providedCode
     * @return bool
     */
    public function verifyPhone(User $user, string $phoneNumber, string $providedCode): bool
    {
        $phoneNumber = $this->normalizePhoneNumber($phoneNumber);

        $cacheItem = $this->cache->getItem("phone_verification_{$user->getId()}_{$phoneNumber}");

        if (!$cacheItem->isHit()) {
            return false;
        }

        $verificationData = $cacheItem->get();

        // Check verification code
        if ($verificationData['code'] !== $providedCode) {
            // Increment attempts
            $verificationData['attempts']++;
            $cacheItem->set($verificationData);
            $this->cache->save($cacheItem);

            return false;
        }

        // Create and persist Phone entity
        $phone = new Phone();
        $phone->setNumber($phoneNumber);
        $phone->setUser($user);
        $phone->setVerified(true);
        $phone->setType('mobile'); // Default type

        $user->addPhone($phone);

        $this->entityManager->persist($phone);
        $this->entityManager->flush();

        // Clear verification cache
        $this->cache->deleteItem($cacheItem->getKey());

        return true;
    }

    /**
     * Normalize phone number (basic implementation)
     *
     * @param string $phoneNumber
     * @return string
     */
    private function normalizePhoneNumber(string $phoneNumber): string
    {
        // Remove non-digit characters
        $phoneNumber = preg_replace('/\D/', '', $phoneNumber);

        // If the number doesn't start with country code, assume Tunisian (+216)
        if (!preg_match('/^(\+?216|216)/', $phoneNumber)) {
            $phoneNumber = '216' . $phoneNumber;
        }

        // Remove any leading '216' or '+216'
        $phoneNumber = preg_replace('/^(\+?216)/', '', $phoneNumber);

        // Ensure the final number is in international format
        return '+216' . $phoneNumber;
    }
}
