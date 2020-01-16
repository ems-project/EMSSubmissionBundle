<?php

namespace EMS\SubmissionBundle\FormConfig;

abstract class AbstractConfig
{
    protected function sanitiseQuotes(string $string)
    {
        return \preg_replace('/^&quot;|&quot;$/', '', $string);
    }

    protected function sanitiseAttachments(array $attachments): array
    {
        $recursiveSanitizer = function ($attachment) use (&$recursiveSanitizer) {
            return \is_array($attachment) ? \array_map($recursiveSanitizer, $attachment) : $this->sanitiseQuotes($attachment);
        };

        return \array_map($recursiveSanitizer, $attachments);
    }
}
