<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class ConnectionExtension extends abstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('emss_connection', [ConnectionRuntime::class, 'transform'], ['is_safe' => ['html']]),
        ];
    }
}
