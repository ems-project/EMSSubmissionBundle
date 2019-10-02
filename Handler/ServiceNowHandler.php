<?php

namespace EMS\SubmissionBundle\Handler;

use EMS\FormBundle\FormConfig\FormConfig;
use EMS\FormBundle\Handler\AbstractHandler;
use EMS\FormBundle\FormConfig\SubmissionConfig;
use EMS\FormBundle\Submit\AbstractResponse;
use EMS\FormBundle\Submit\FailedResponse;
use EMS\SubmissionBundle\FormConfig\ServiceNowConfig;
use EMS\SubmissionBundle\Service\SubmissionRenderer;
use EMS\SubmissionBundle\Submit\ServiceNowResponse;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpClient\HttpClient;

class ServiceNowHandler extends AbstractHandler
{
    /** @var SubmissionRenderer */
    private $renderer;
    /** @var int */
    private $timeout;

    public function __construct(SubmissionRenderer $renderer, int $timeout)
    {
        $this->renderer = $renderer;
        $this->timeout = $timeout;
    }

    public function handle(SubmissionConfig $submission, FormInterface $form, FormConfig $config, AbstractResponse $previousResponse = null): AbstractResponse
    {
        try {
            $renderedSubmission = $this->renderer->render($submission, $form, $config, $previousResponse);
            $snow = new ServiceNowConfig($renderedSubmission);

            $client = HttpClient::create();
            $response = $client->request('POST', $snow->getHost(), [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => $snow->getBasicAuth(),
                ],
                'body' => $snow->getFieldsJson(),
                'timeout' => $this->timeout
            ]);

            return new ServiceNowResponse($response->getContent());
        } catch (\Exception $exception) {
            return new FailedResponse(sprintf('Submission failed, contact your admin. %s', $exception->getMessage()));
        }
    }
}
