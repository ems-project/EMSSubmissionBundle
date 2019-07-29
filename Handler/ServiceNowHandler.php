<?php

namespace EMS\SubmissionBundle\Handler;

use EMS\FormBundle\FormConfig\FormConfig;
use EMS\SubmissionBundle\FormConfig\ServiceNowConfig;
use EMS\SubmissionBundle\FormConfig\SubmissionConfig;
use EMS\SubmissionBundle\Service\SubmissionRenderer;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpClient\HttpClient;

class ServiceNowHandler extends AbstractHandler
{
    /** @var SubmissionRenderer */
    private $renderer;

    public function __construct(SubmissionRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function handle(SubmissionConfig $submission, FormInterface $form, FormConfig $config): string
    {
        try {
            $renderedSubmission = $this->renderer->render($submission, $form, $config);
            $snow = new ServiceNowConfig($renderedSubmission);

            $client = HttpClient::create();
            //Send to SNOW
            $response = $client->request('POST', $snow->getHost(), [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => $snow->getBasicAuth(),
                ],
                'body' => $snow->getFieldsJson(),
            ]);

            dump($snow, $response);die;
            //Get SNOW ticketNumber

            //Return snow ticketNumber
        } catch(\Exception $exception) {
            return sprintf('Submission failed, contact your admin. %s', $exception->getMessage());
        }
        dump($config);die;


    }
}