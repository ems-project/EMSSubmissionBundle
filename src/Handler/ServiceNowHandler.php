<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Handler;

use EMS\FormBundle\FormConfig\FormConfig;
use EMS\FormBundle\FormConfig\SubmissionConfig;
use EMS\FormBundle\Handler\AbstractHandler;
use EMS\FormBundle\Submit\AbstractResponse;
use EMS\FormBundle\Submit\FailedResponse;
use EMS\SubmissionBundle\Config\ConfigFactory;
use EMS\SubmissionBundle\Request\ServiceNowRequest;
use EMS\SubmissionBundle\Response\ServiceNowResponse;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ServiceNowHandler extends AbstractHandler
{
    /** @var ConfigFactory */
    private $configFactory;
    /** @var int */
    private $timeout;
    /** @var HttpClientInterface */
    private $client;

    public function __construct(ConfigFactory $configFactory, HttpClientInterface $httpClient, int $timeout)
    {
        $this->configFactory = $configFactory;
        $this->timeout = $timeout;
        $this->client = $httpClient;
    }

    /**
     * @param FormInterface<FormInterface> $form
     */
    public function handle(SubmissionConfig $submission, FormInterface $form, FormConfig $config, AbstractResponse $previousResponse = null): AbstractResponse
    {
        try {
            $config = $this->configFactory->create($submission, $form, $config, $previousResponse);
            $snow = new ServiceNowRequest($config);

            $response = $this->client->request('POST', $snow->getBodyEndpoint(), [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => $snow->getBasicAuth(),
                ],
                'body' => $snow->getBody(),
                'timeout' => $this->timeout,
            ]);

            $serviceNowResponse = new ServiceNowResponse($response->getContent());
            $this->addAttachments($serviceNowResponse, $snow);

            return $serviceNowResponse;
        } catch (\Exception $exception) {
            return new FailedResponse(\sprintf('Submission failed, contact your admin. (%s)', $exception->getMessage()));
        }
    }

    private function addAttachments(ServiceNowResponse $response, ServiceNowRequest $request): void
    {
        foreach ($request->getAttachments() as $attachment) {
            $binary = $this->getBinaryFile($attachment['pathname']);

            if (!empty($binary)) {
                $this->postAttachment($response, $request, $attachment, $binary);
            }
        }
    }

    private function getBinaryFile(string $pathname): ?string
    {
        $file = \fopen($pathname, 'r');
        $size = \filesize($pathname);

        if (!$file || !$size) {
            return null;
        }

        $binary = \fread($file, $size);

        if (!$binary) {
            return null;
        }

        return $binary;
    }

    /**
     * @param array<array> $attachment
     */
    private function postAttachment(ServiceNowResponse $response, ServiceNowRequest $request, array $attachment, string $binary): void
    {
        try {
            $this->client->request('POST', $request->getAttachmentEndpoint(), [
                'query' => [
                    'file_name' => $attachment['originalName'],
                    'table_name' => $request->getTable(),
                    'table_sys_id' => $response->getResultProperty('sys_id'),
                ],
                'headers' => [
                    'Content-Type' => $attachment['mimeType'],
                    'Authorization' => $request->getBasicAuth(),
                ],
                'body' => $binary,
            ]);
        } catch (\Exception $exception) {
            throw new \Exception(\sprintf('Attachment submission failed: %s', $exception->getMessage()));
        }
    }
}
