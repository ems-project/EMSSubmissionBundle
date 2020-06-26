<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Handler;

use EMS\FormBundle\Submission\AbstractHandler;
use EMS\FormBundle\Submission\FailedHandleResponse;
use EMS\FormBundle\Submission\HandleRequestInterface;
use EMS\FormBundle\Submission\HandleResponseInterface;
use EMS\SubmissionBundle\Config\ConfigFactory;
use EMS\SubmissionBundle\Request\HttpRequest;
use EMS\SubmissionBundle\Response\HttpHandleResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpHandler extends AbstractHandler
{
    /** @var ConfigFactory */
    private $configFactory;
    /** @var HttpClientInterface */
    private $client;

    public function __construct(ConfigFactory $configFactory, HttpClientInterface $httpClient)
    {
        $this->configFactory = $configFactory;
        $this->client = $httpClient;
    }

    public function handle(HandleRequestInterface $handleRequest): HandleResponseInterface
    {
        try {
            $config = $this->configFactory->create($handleRequest);
            $httpRequest = new HttpRequest($config);

            $response = $this->client->request($httpRequest->getMethod(), $httpRequest->getUrl(), $httpRequest->getOptions());
            $responseContent = $response->getContent(true);

            return new HttpHandleResponse($response, $responseContent);
        } catch (\Exception $exception) {
            return new FailedHandleResponse(\sprintf('Submission failed, contact your admin. (%s)', $exception->getMessage()));
        }
    }
}
