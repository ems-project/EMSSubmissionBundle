<?php

namespace EMS\SubmissionBundle\Service;

use EMS\FormBundle\FormConfig\FormConfig;
use EMS\SubmissionBundle\FormConfig\SubmissionConfig;
use EMS\SubmissionBundle\Submission\RenderedSubmission;
use Symfony\Component\Form\FormInterface;

class SubmissionRenderer
{
    /** @var \Twig_Environment */
    protected $templating;

    public function __construct(\Twig_Environment $templating)
    {
        $this->templating = $templating;
    }

    public function render(SubmissionConfig $submission, FormInterface $form, FormConfig $config)
    {
        $endpointTemplate = $this->templating->createTemplate($submission->getEndpoint());
        $endpoint = $endpointTemplate->render(['config' => $config, 'data' => $form->getData()]);

        $messageTemplate = $this->templating->createTemplate($submission->getMessage());
        $message = $messageTemplate->render(['config' => $config, 'data' => $form->getData()]);

        return new RenderedSubmission($submission->getClass(), $endpoint, $message);
    }
}
