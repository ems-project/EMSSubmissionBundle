<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Request;

use EMS\CommonBundle\Service\Pdf\Pdf;
use EMS\CommonBundle\Service\Pdf\PdfInterface;

final class PdfRequest
{
    /** @var string */
    private $filename;
    /** @var string */
    private $html;

    public function __construct(string $endpoint, string $message)
    {
        if ('' === $endpoint) {
            throw new \Exception(\sprintf('Endpoint not defined.'));
        }

        $this->filename = $endpoint;
        $this->html = $message;
    }

    public function getPdf(): PdfInterface
    {
        return new Pdf($this->filename, $this->html);
    }
}
