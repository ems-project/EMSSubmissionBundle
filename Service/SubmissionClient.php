<?php

namespace EMS\SubmissionBundle\Service;

use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequest;
use EMS\CommonBundle\Common\EMSLink;
use EMS\FormBundle\FormConfig\FormConfig;
use EMS\SubmissionBundle\FormConfig\SubmissionConfig;
use EMS\SubmissionBundle\FormConfig\SubmitResponse;
use EMS\SubmissionBundle\Handler\AbstractHandler;
use EMS\SubmissionBundle\Submission\SubmissionConfiguration;
use Symfony\Component\Form\FormInterface;

class SubmissionClient
{
    /** @var ClientRequest */
    private $client;

    /** @var \Traversable */
    private $handlers;

    public function __construct(ClientRequest $client, \Traversable $handlers)
    {
        $this->client = $client;
        $this->handlers = $handlers;
    }

    public function submit(FormInterface $form): SubmitResponse
    {
        /** @var FormConfig $config */
        $config = $form->getConfig()->getOption('config');
        $this->loadSubmissions($config);

        $response = new SubmitResponse();

        foreach ($config->getSubmissions() as $submission) {
            $this->handle($submission, $response, $form, $config);
        }

        return $response;
    }

    private function handle(SubmissionConfig $submission, SubmitResponse $response, FormInterface $form, FormConfig $config) : void
    {
        foreach ($this->handlers as $handler) {
            if (! $handler instanceof AbstractHandler) {
                continue;
            }
            if ($handler->canHandle($submission->getClass())) {
                $response->addResponse($handler->handle($submission, $form, $config));
            }
        }
    }

    private function loadSubmissions(FormConfig $config): void
    {
        $emsLinkSubmissions = $config->getSubmissions();
        $config->setSubmissions([]);

        foreach ($emsLinkSubmissions as $emsLinkSubmission) {
            $submission = $this->client->getByEmsKey($emsLinkSubmission, [])['_source'];
            $config->addSubmission(new SubmissionConfig($submission['type'], $submission['endpoint'], $submission['message']));
        }
    }
}
