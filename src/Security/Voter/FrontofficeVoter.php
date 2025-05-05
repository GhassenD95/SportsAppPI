<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class FrontofficeVoter extends Voter
{
    public const FRONTOFFICE_ACCESS = 'FRONTOFFICE_ACCESS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::FRONTOFFICE_ACCESS;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return $this->canAccessFrontoffice($user);
    }

    private function canAccessFrontoffice(User $user): bool
    {
        return in_array('ROLE_COACH', $user->getRoles()) ||
               in_array('ROLE_MANAGER', $user->getRoles()) ||
               in_array('ROLE_ATHLETE', $user->getRoles());
    }
} 