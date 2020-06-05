<?php

namespace EMS\SubmissionBundle\FormConfig;

abstract class AbstractConfig
{
    protected function sanitiseQuotes(string $string): ?string
    {
        return \preg_replace('/^&quot;|&quot;$/', '', $string);
    }

    /**
     * @param array<array> $attachments
     *
     * @return array<array-key, string>
     */
    protected function sanitiseAttachments(array $attachments): array
    {
        $recursiveSanitizer = function ($attachment) use (&$recursiveSanitizer) {
            return \is_array($attachment) ? \array_map($recursiveSanitizer, $attachment) : $this->sanitiseQuotes($attachment);
        };

        return \array_map($recursiveSanitizer, $attachments);
    }
}
