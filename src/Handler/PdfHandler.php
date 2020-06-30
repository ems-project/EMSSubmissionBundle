<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Handler;

use EMS\CommonBundle\Service\Pdf\PdfPrinterInterface;
use EMS\FormBundle\Submission\AbstractHandler;
use EMS\FormBundle\Submission\FailedHandleResponse;
use EMS\FormBundle\Submission\HandleRequestInterface;
use EMS\FormBundle\Submission\HandleResponseInterface;
use EMS\SubmissionBundle\Request\PdfRequest;
use EMS\SubmissionBundle\Response\PdfHandleResponse;
use EMS\SubmissionBundle\Twig\TwigRenderer;

final class PdfHandler extends AbstractHandler
{
    /** @var PdfPrinterInterface */
    private $pdfPrinter;
    /** @var TwigRenderer */
    private $twigRenderer;

    public function __construct(PdfPrinterInterface $pdfPrinter, TwigRenderer $twigRenderer)
    {
        $this->pdfPrinter = $pdfPrinter;
        $this->twigRenderer = $twigRenderer;
    }

    public function handle(HandleRequestInterface $handleRequest): HandleResponseInterface
    {
        try {
            $endpoint = $this->twigRenderer->renderEndpoint($handleRequest);
            $message = $this->twigRenderer->renderMessage($handleRequest);

            $pdfRequest = new PdfRequest($endpoint, $message);
            $pdfOutput = $this->pdfPrinter->getPdfOutput($pdfRequest->getPdf());

            return new PdfHandleResponse($pdfRequest, $pdfOutput);
        } catch (\Exception $exception) {
            return new FailedHandleResponse(\sprintf('Submission failed, contact your admin. (%s)', $exception->getMessage()));
        }
    }
}
