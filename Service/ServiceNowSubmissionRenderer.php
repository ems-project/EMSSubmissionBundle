<?php

namespace EMS\SubmissionBundle\Service;

use EMS\FormBundle\FormConfig\FormConfig;
use EMS\FormBundle\FormConfig\SubmissionConfig;
use EMS\FormBundle\Submit\AbstractResponse;
use EMS\SubmissionBundle\Submit\RenderedSubmission;
use EMS\SubmissionBundle\Transformers\ServiceNowFormDataTransformer;
use Symfony\Component\Form\FormInterface;

class ServiceNowSubmissionRenderer extends SubmissionRenderer
{
    public function render(SubmissionConfig $submission, FormInterface $form, FormConfig $config, AbstractResponse $response = null)
    {
        $transformer = new ServiceNowFormDataTransformer($form);
        $formData = $transformer->transform();

        $endpointTemplate = $this->templating->createTemplate($submission->getEndpoint());
        $endpoint = $endpointTemplate->render(['config' => $config, 'data' => $formData]);

        $messageTemplate = $this->templating->createTemplate($submission->getMessage());
        $message = $messageTemplate->render(['config' => $config, 'data' => $formData, 'previous' => $response]);

        return new RenderedSubmission($submission->getClass(), $endpoint, $message);
    }
}
