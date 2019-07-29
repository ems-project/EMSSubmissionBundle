<?php

namespace EMS\SubmissionBundle\FormConfig;

use EMS\SubmissionBundle\Submission\RenderedSubmission;

class ServiceNowConfig
{
    /** @var string */
    private $host;
    /** @var string */
    private $username;
    /** @var string */
    private $password;

    /** @var string */
    private $fieldsJson;

    public function __construct(RenderedSubmission $submission)
    {
        $endpoint = \json_decode($submission->getEndpoint(), true);
        $this->fieldsJson = $submission->getMessage();

        $this->host = $endpoint['host'];
        $this->username = $endpoint['username'];
        $this->password = $endpoint['password'];
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getFieldsJson(): string
    {
        return $this->fieldsJson;
    }

    public function getBasicAuth(): string
    {
        $credentials = base64_encode(sprintf('%s:%s', $this->getUsername(), $this->getPassword()));
        return sprintf('Basic %s', $credentials);
    }
}