<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Config;

use EMS\FormBundle\Submission\HandleRequestInterface;
use Twig\Environment;

final class ConfigFactory
{
    /** @var Environment */
    protected $templating;

    public function __construct(Environment $templating)
    {
        $this->templating = $templating;
    }

    public function create(HandleRequestInterface $handleRequest): Config
    {
        $context = [
            'config' => $handleRequest->getFormConfig(),
            'data' => $handleRequest->getFormData(),
            'request' => $handleRequest,
        ];

        $endpointTemplate = $this->templating->createTemplate($handleRequest->getEndPoint());
        $endpoint = $endpointTemplate->render($context);

        $messageTemplate = $this->templating->createTemplate($handleRequest->getMessage());
        $message = $messageTemplate->render($context);

        return new Config($endpoint, $message);
    }
}
