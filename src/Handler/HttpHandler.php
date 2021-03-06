<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Handler;

use EMS\FormBundle\Submission\AbstractHandler;
use EMS\FormBundle\Submission\FailedHandleResponse;
use EMS\FormBundle\Submission\HandleRequestInterface;
use EMS\FormBundle\Submission\HandleResponseInterface;
use EMS\SubmissionBundle\Request\HttpRequest;
use EMS\SubmissionBundle\Response\HttpHandleResponse;
use EMS\SubmissionBundle\Response\ResponseTransformer;
use EMS\SubmissionBundle\Twig\TwigRenderer;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpHandler extends AbstractHandler
{
    /** @var HttpClientInterface */
    private $client;
    /** @var TwigRenderer */
    private $twigRenderer;
    /** @var ResponseTransformer */
    private $responseTransformer;

    public function __construct(
        HttpClientInterface $client,
        TwigRenderer $twigRenderer,
        ResponseTransformer $responseTransformer
    ) {
        $this->client = $client;
        $this->twigRenderer = $twigRenderer;
        $this->responseTransformer = $responseTransformer;
    }

    public function handle(HandleRequestInterface $handleRequest): HandleResponseInterface
    {
        try {
            $endpoint = $this->twigRenderer->renderEndpointJSON($handleRequest);
            $body = $this->twigRenderer->renderMessageBlock($handleRequest, 'requestBody') ?? '';

            $httpRequest = new HttpRequest($endpoint, $body);
            $httpResponse = $this->client->request($httpRequest->getMethod(), $httpRequest->getUrl(), $httpRequest->getHttpOptions());
            $httpResponseContent = $httpResponse->getContent(true);

            $handleResponse = new HttpHandleResponse($httpResponse, $httpResponseContent);

            return $this->responseTransformer->transform($handleRequest, $handleResponse);
        } catch (\Exception $exception) {
            return new FailedHandleResponse(\sprintf('Submission failed, contact your admin. (%s)', $exception->getMessage()));
        }
    }
}
