<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Handler;

use EMS\CommonBundle\Service\Pdf\PdfGenerator;
use EMS\FormBundle\FormConfig\FormConfig;
use EMS\FormBundle\Handler\AbstractHandler;
use EMS\FormBundle\FormConfig\SubmissionConfig;
use EMS\FormBundle\Submit\AbstractResponse;
use EMS\FormBundle\Submit\FailedResponse;
use EMS\SubmissionBundle\FormConfig\PdfConfig;
use EMS\SubmissionBundle\Service\DirectoryManager;
use EMS\SubmissionBundle\Service\SubmissionRenderer;
use EMS\SubmissionBundle\Submit\PdfResponse;
use Symfony\Component\Form\FormInterface;

final class PdfHandler extends AbstractHandler
{
    /** @var SubmissionRenderer */
    private $renderer;
    /** @var PdfGenerator */
    private $pdfGenerator;

    public function __construct(SubmissionRenderer $renderer, PdfGenerator $pdfGenerator)
    {
        $this->renderer = $renderer;
        $this->pdfGenerator = $pdfGenerator;
    }

    public function handle(SubmissionConfig $submission, FormInterface $form, FormConfig $config, AbstractResponse $previousResponse = null): AbstractResponse
    {
        try {
            $renderedSubmission = $this->renderer->render($submission, $form, $config, $previousResponse);
            $pdfConfig = new PdfConfig($renderedSubmission);

            $stream = $this->pdfGenerator->getStreamedResponse($pdfConfig->getBody());
            dd($stream);
        } catch (\Exception $exception) {
            return new FailedResponse(sprintf('Submission failed, contact your admin. %s', $exception->getMessage()));
        }

        return new PdfResponse(AbstractResponse::STATUS_SUCCESS);
    }
}
