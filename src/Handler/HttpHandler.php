<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Handler;

use EMS\FormBundle\Submission\AbstractHandler;
use EMS\FormBundle\Submission\FailedHandleResponse;
use EMS\FormBundle\Submission\HandleRequestInterface;
use EMS\FormBundle\Submission\HandleResponseInterface;
use EMS\SubmissionBundle\Request\HttpRequest;
use EMS\SubmissionBundle\Response\HttpHandleResponse;
use EMS\SubmissionBundle\Twig\TwigRenderer;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpHandler extends AbstractHandler
{
    /** @var HttpClientInterface */
    private $client;
    /** @var TwigRenderer */
    private $twigRenderer;

    public function __construct(HttpClientInterface $client, TwigRenderer $twigRenderer)
    {
        $this->client = $client;
        $this->twigRenderer = $twigRenderer;
    }

    public function handle(HandleRequestInterface $handleRequest): HandleResponseInterface
    {
        try {
            $endpoint = $this->twigRenderer->renderEndpointJSON($handleRequest);
            $body = $this->twigRenderer->renderMessageBlock($handleRequest, 'requestBody') ?? '';

            $httpRequest = new HttpRequest($endpoint, $body);
            $httpResponse = $this->client->request($httpRequest->getMethod(), $httpRequest->getUrl(), $httpRequest->getOptions());
            $httpResponseContent = $httpResponse->getContent(true);

            $handleResponse = new HttpHandleResponse($httpResponse, $httpResponseContent);
            $extraData = $this->twigRenderer->renderMessageBlockJSON($handleRequest, 'handleResponseExtra', [
                'response' => $handleResponse,
            ]);
            $handleResponse->setExtraData($extraData);

            return $handleResponse;
        } catch (\Exception $exception) {
            return new FailedHandleResponse(\sprintf('Submission failed, contact your admin. (%s)', $exception->getMessage()));
        }
    }
}
