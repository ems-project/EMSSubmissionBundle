<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Request;

use EMS\CommonBundle\Service\Pdf\Pdf;
use EMS\CommonBundle\Service\Pdf\PdfInterface;
use EMS\SubmissionBundle\Config\Config;

final class PdfRequest
{
    /** @var string */
    private $filename;
    /** @var string */
    private $html;

    public function __construct(Config $config)
    {
        $endPoint = $config->getEndpoint();

        if ('' === $endPoint) {
            throw new \Exception(sprintf('Endpoint not defined.'));
        }

        $this->filename = $config->getEndpoint();
        $this->html = $config->getMessage();
    }

    public function getPdf(): PdfInterface
    {
        return new Pdf($this->filename, $this->html);
    }
}
