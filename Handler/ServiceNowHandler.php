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
    /** @var HttpClient */
    private $client;

    public function __construct(SubmissionRenderer $renderer, int $timeout)
    {
        $this->renderer = $renderer;
        $this->timeout = $timeout;
        $this->client = HttpClient::create();
    }

    public function handle(SubmissionConfig $submission, FormInterface $form, FormConfig $config, AbstractResponse $previousResponse = null): AbstractResponse
    {
        try {
            $renderedSubmission = $this->renderer->render($submission, $form, $config, $previousResponse);
            $snow = new ServiceNowConfig($renderedSubmission);

            $response = $this->client->request('POST', $snow->getBodyEndpoint(), [
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

    private function addAttachments(ServiceNowResponse $response, ServiceNowConfig $config): void
    {
        foreach ($config->getAttachments() as $attachment) {
            $binary = $this->getBinaryFile($attachment['pathname']);

            if (!empty($binary)) {
                $this->postAttachment($response, $config, $attachment, $binary);
            }
        }
    }

    private function getBinaryFile(string $pathname): ?string
    {
        $file = \fopen($pathname, "r");
        $size = \filesize($pathname);

        if (!$file || !$size) {
            return null;
        }

        return \fread($file, $size);
    }

    private function postAttachment(ServiceNowResponse $response, ServiceNowConfig $config, array $attachment, string $binary)
    {
        try {
            $this->client->request('POST', $config->getAttachmentEndpoint(), [
                'query' => [
                    'file_name' => $attachment['originalName'],
                    'table_name' => $config->getTable(),
                    'table_sys_id' => $response->getResultProperty('sys_id'),
                ],
                'headers' => [
                    'Content-Type' => $attachment['mimeType'],
                    'Authorization' => $config->getBasicAuth(),
                ],
                'body' => $binary
            ]);
        } catch (\Exception $exception) {
            return new FailedResponse(\sprintf('Attachment submission failed, contact your admin. %s', $exception->getMessage()));
        }
    }
}
