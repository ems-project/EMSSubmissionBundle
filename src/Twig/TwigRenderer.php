<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Twig;

use EMS\FormBundle\Submission\HandleRequestInterface;
use Twig\Environment;

final class TwigRenderer
{
    /** @var Environment */
    protected $templating;

    public function __construct(Environment $templating)
    {
        $this->templating = $templating;
    }

    public function renderEndpoint(HandleRequestInterface $handleRequest): string
    {
        return $this->renderTemplate($handleRequest->getEndPoint(), $this->getContext($handleRequest));
    }

    /**
     * @return array<string, mixed>
     */
    public function renderEndpointJSON(HandleRequestInterface $handleRequest): array
    {
        return \json_decode($this->renderEndpoint($handleRequest), true) ?? [];
    }

    public function renderMessage(HandleRequestInterface $handleRequest): string
    {
        return $this->renderTemplate($handleRequest->getMessage(), $this->getContext($handleRequest));
    }

    /**
     * @param array<string, mixed> $context
     */
    public function renderMessageBlock(HandleRequestInterface $handleRequest, string $blockName, array $context = []): ?string
    {
        $template = $this->templating->createTemplate($handleRequest->getMessage());

        if (!$template->hasBlock($blockName)) {
            return null;
        }

        $context = \array_merge($context, $this->getContext($handleRequest));

        return $template->renderBlock($blockName, $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function renderMessageBlockJSON(HandleRequestInterface $handleRequest, string $blockName, array $context = []): array
    {
        $json = $this->renderMessageBlock($handleRequest, $blockName, $context);

        return $json ? (\json_decode($json, true) ?? []) : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function renderMessageJSON(HandleRequestInterface $handleRequest): array
    {
        return \json_decode($this->renderMessage($handleRequest), true) ?? [];
    }

    /**
     * @param array<string, mixed> $context
     */
    private function renderTemplate(string $template, array $context): string
    {
        return $this->templating->createTemplate($template)->render($context);
    }

    /**
     * @return array<string, mixed>
     */
    private function getContext(HandleRequestInterface $handleRequest): array
    {
        return [
            'config' => $handleRequest->getFormConfig(),
            'data' => $handleRequest->getFormData()->raw(),
            'formData' => $handleRequest->getFormData(),
            'request' => $handleRequest,
        ];
    }
}
