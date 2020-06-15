<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Handler;

use EMS\CommonBundle\Service\Pdf\PdfPrinterInterface;
use EMS\FormBundle\Submission\AbstractHandler;
use EMS\FormBundle\Submission\FailedHandleResponse;
use EMS\FormBundle\Submission\HandleRequestInterface;
use EMS\FormBundle\Submission\HandleResponseInterface;
use EMS\SubmissionBundle\Config\ConfigFactory;
use EMS\SubmissionBundle\Request\PdfRequest;
use EMS\SubmissionBundle\Response\PdfHandleResponse;

final class PdfHandler extends AbstractHandler
{
    /** @var ConfigFactory */
    private $configFactory;
    /** @var PdfPrinterInterface */
    private $pdfPrinter;

    public function __construct(ConfigFactory $configFactory, PdfPrinterInterface $pdfGenerator)
    {
        $this->configFactory = $configFactory;
        $this->pdfPrinter = $pdfGenerator;
    }

    public function handle(HandleRequestInterface $handleRequest): HandleResponseInterface
    {
        try {
            $config = $this->configFactory->create($handleRequest);

            $pdfRequest = new PdfRequest($config);
            $pdfOutput = $this->pdfPrinter->getPdfOutput($pdfRequest->getPdf());

            return new PdfHandleResponse($pdfRequest, $pdfOutput);
        } catch (\Exception $exception) {
            return new FailedHandleResponse(\sprintf('Submission failed, contact your admin. (%s)', $exception->getMessage()));
        }
    }
}
