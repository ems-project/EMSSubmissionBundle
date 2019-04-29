<?php

namespace EMS\SubmissionBundle\Service;

use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequest;
use EMS\SubmissionBundle\Submission\SubmissionConfiguration;

//TODO add usefull logging.
class SubmissionClient
{
    /** @var ClientRequest */
    private $client;

    /** @var \Twig_Environment */
    private $templating;

    /** @var \Swift_Mailer */
    private $mailer;

    public function __construct(ClientRequest $client, \Twig_Environment $templating, \Swift_Mailer $mailer)
    {
        $this->client = $client;
        $this->templating = $templating;
        $this->mailer = $mailer;
    }

    public function submit(string $id, string $locale, array $data)
    {
        $result = ($this->client->get('form_submission', $id))['_source'];
        $configuration = new SubmissionConfiguration($result);

        $response = 'Submission failed, an admin should verify the configuration';
        $data['ems_submission_locale'] = $locale;
        switch ($configuration->getType()) {
            case 'email':
                $response = $this->sendByMail($configuration, $data);
                //TODO: update response with Failed / Success of mail service
                break;
        }

        return $response;
    }

    private function sendByMail(SubmissionConfiguration $configuration, array $data): string
    {
        try {
            $message = (new \Swift_Message($this->templating->render($configuration->getSubjectTemplate(), $data)))
                ->setFrom($this->templating->render($configuration->getOriginTemplate(), $data))
                ->setTo($this->templating->render($configuration->getTargetTemplate(), $data))
                ->setBody($this->templating->render($configuration->getDataTemplate(), $data));

        } catch (\Exception $exception) {
            return sprintf('Submission failed, contact your admin. %s', $exception->getMessage());
        }

        $failedRecipients = [];
        $this->mailer->send($message, $failedRecipients);

        if ($failedRecipients !== []) {
            return 'Submission failed. Conctact your admin.';
        }

        //TODO is this enough? or can we get more info from the mail deamon?
        return 'Submission send by mail.';
    }

}