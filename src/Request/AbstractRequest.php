<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Request;

abstract class AbstractRequest
{
    protected function sanitiseQuotes(string $string): ?string
    {
        return \preg_replace('/^&quot;|&quot;$/', '', $string);
    }

    /**
     * @param array<array> $attachments
     *
     * @return array<array>
     */
    protected function sanitiseAttachments(array $attachments): array
    {
        $recursiveSanitizer = function ($attachment) use (&$recursiveSanitizer) {
            return \is_array($attachment) ? \array_map($recursiveSanitizer, $attachment) : $this->sanitiseQuotes($attachment);
        };

        return \array_map($recursiveSanitizer, $attachments);
    }
}
