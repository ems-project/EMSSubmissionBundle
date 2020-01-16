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
            $response = $client->request('POST', $snow->getBodyEndpoint(), [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => $snow->getBasicAuth(),
                ],
                'body' => $snow->getBody(),
                'timeout' => $this->timeout
            ]);

            $serviceNowResponse = new ServiceNowResponse($response->getContent());
            $this->addAttachments($serviceNowResponse, $snow);

            return $serviceNowResponse;
        } catch (\Exception $exception) {
            return new FailedResponse(\sprintf('Submission failed, contact your admin. %s', $exception->getMessage()));
        }
    }

    private function addAttachments(ServiceNowResponse $response, ServiceNowConfig $snow)
    {
        $client = HttpClient::create();

        foreach ($snow->getAttachments() as $attachment) {
            $binary = $this->getBinaryFile($attachment['pathname']);

            try {
                $client->request('POST', $snow->getAttachmentEndpoint(), [
                    'query' => [
                        'file_name' => $attachment['originalName'],
                        'table_name' => $snow->getTable(),
                        'table_sys_id' => $response->getResultProperty('sys_id'),
                    ],
                    'headers' => [
                        'Content-Type' => $attachment['mimeType'],
                        'Authorization' => $snow->getBasicAuth(),
                    ],
                    'body' => $binary
                ]);
            } catch (\Exception $exception) {
                return new FailedResponse(\sprintf('Attachment submission failed, contact your admin. %s', $exception->getMessage()));
            }
        }
    }

    private function getBinaryFile($pathname)
    {
        if($file = \fopen($pathname, "r") && $size = \filesize($pathname)) {
            return \fread($file, $size);
        }
    }
}
