<?php

namespace EMS\SubmissionBundle\Service;

use EMS\FormBundle\FormConfig\FormConfig;
use EMS\FormBundle\FormConfig\SubmissionConfig;
use EMS\FormBundle\Submit\AbstractResponse;
use EMS\SubmissionBundle\Submit\RenderedSubmission;
use Symfony\Component\Form\FormInterface;

class SubmissionRenderer
{
    /** @var \Twig_Environment */
    protected $templating;

    public function __construct(\Twig_Environment $templating)
    {
        $this->templating = $templating;
    }

    /**
     * @param FormInterface<FormInterface> $form
     */
    public function render(SubmissionConfig $submission, FormInterface $form, FormConfig $config, AbstractResponse $response = null): RenderedSubmission
    {
        $endpointTemplate = $this->templating->createTemplate($submission->getEndpoint());
        $endpoint = $endpointTemplate->render(['config' => $config, 'data' => $form->getData()]);

        $messageTemplate = $this->templating->createTemplate($submission->getMessage());
        $message = $messageTemplate->render(['config' => $config, 'data' => $form->getData(), 'previous' => $response]);

        return new RenderedSubmission($submission->getClass(), $endpoint, $message);
    }
}
