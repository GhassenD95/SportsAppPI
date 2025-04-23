<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\AppExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('role_filter', [$this, 'formatRole']),
        ];
    }

    public function formatRole(array $roles): string
    {
        if (empty($roles)) {
            return 'User';
        }

        $role = str_replace('ROLE_', '', $roles[0]);
        return ucfirst(strtolower($role));
    }


}
