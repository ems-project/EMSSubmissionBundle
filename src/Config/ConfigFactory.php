<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Config;

use EMS\FormBundle\FormConfig\FormConfig;
use EMS\FormBundle\FormConfig\SubmissionConfig;
use EMS\FormBundle\Submit\AbstractResponse;
use Symfony\Component\Form\FormInterface;
use Twig\Environment;

final class ConfigFactory
{
    /** @var Environment */
    protected $templating;

    public function __construct(Environment $templating)
    {
        $this->templating = $templating;
    }

    /**
     * @param FormInterface<FormInterface> $form
     */
    public function create(SubmissionConfig $submission, FormInterface $form, FormConfig $config, AbstractResponse $response = null): Config
    {
        $endpointTemplate = $this->templating->createTemplate($submission->getEndpoint());
        $endpoint = $endpointTemplate->render(['config' => $config, 'data' => $form->getData()]);

        $messageTemplate = $this->templating->createTemplate($submission->getMessage());
        $message = $messageTemplate->render(['config' => $config, 'data' => $form->getData(), 'previous' => $response]);

        return new Config($submission->getClass(), $endpoint, $message);
    }
}
