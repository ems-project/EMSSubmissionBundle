<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Handler;

use EMS\FormBundle\Submission\AbstractHandler;
use EMS\FormBundle\Submission\FailedHandleResponse;
use EMS\FormBundle\Submission\HandleRequestInterface;
use EMS\FormBundle\Submission\HandleResponseInterface;
use EMS\SubmissionBundle\Response\HttpHandleResponse;
use EMS\SubmissionBundle\Response\ResponseTransformer;
use EMS\SubmissionBundle\Twig\TwigRenderer;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class MultipartHandler extends AbstractHandler
{
    private HttpClientInterface $client;
    private TwigRenderer $twigRenderer;
    private ResponseTransformer $responseTransformer;

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
            $options = $this->resolveEndpointOption($endpoint);

            $formData = $handleRequest->getFormData();
            $formData->filesAsUUid();
            $json = $this->twigRenderer->renderMessageJSON($handleRequest);
            $formFields = [];
            foreach ($json as $key => $data) {
                if ($formData->isFileUuid($data)) {
                    $data = $this->getDataPart($formData->getFileFromUuid($data));
                }
                $formFields[$key] = $data;
            }

            $formData = new FormDataPart($formFields);
            $httpResponse = $this->client->request($options['method'], $options['url'], [
                'headers' => $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
                'timeout' => $options['timeout'],
            ]);
            $httpResponseContent = $httpResponse->getContent(true);
            $handleResponse = new HttpHandleResponse($httpResponse, $httpResponseContent, 'Submission send by multipart over http.');

            return $this->responseTransformer->transform($handleRequest, $handleResponse);
        } catch (\Exception $exception) {
            return new FailedHandleResponse(\sprintf('Submission failed, contact your admin. (%s)', $exception->getMessage()));
        }
    }

    /**
     * @param array<mixed> $options
     *
     * @return array{url: string, method: string, timeout: int}
     */
    protected function resolveEndpointOption(array $options): array
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setRequired(['url', 'method'])
            ->setDefaults([
                'method' => Request::METHOD_POST,
                'timeout' => 10,
            ]);
        /** @var array{url: string, method: string, timeout: int} $resolved */
        $resolved = $optionsResolver->resolve($options);

        return $resolved;
    }

    public function getDataPart(UploadedFile $file): DataPart
    {
        if (false === $handle = @\fopen($file->getPath(), 'r', false)) {
            throw new \RuntimeException(\sprintf('Unable to open path "%s".', $file->getPath()));
        }

        return new DataPart($handle, $file->getClientOriginalName(), $file->getClientMimeType());
    }
}
