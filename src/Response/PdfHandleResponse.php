<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Response;

use EMS\CommonBundle\Service\Pdf\PdfOutput;
use EMS\FormBundle\Submission\AbstractHandleResponse;
use EMS\SubmissionBundle\Request\PdfRequest;

final class PdfHandleResponse extends AbstractHandleResponse
{
    /** @var PdfRequest */
    private $pdfRequest;
    /** @var PdfOutput */
    private $pdfOutput;

    public function __construct(PdfRequest $pdfRequest, PdfOutput $pdfOutput)
    {
        parent::__construct(self::STATUS_SUCCESS, 'Pdf ready for next handler');

        $this->pdfRequest = $pdfRequest;
        $this->pdfOutput = $pdfOutput;
    }

    public function getFilename(): string
    {
        return $this->pdfRequest->getPdf()->getFilename();
    }

    public function getContent(bool $encode = true): string
    {
        $content = $this->pdfOutput->make();

        return $encode ? base64_encode($content) : $content;
    }
}
