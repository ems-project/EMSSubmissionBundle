<?php

namespace EMS\SubmissionBundle\Submit;

use EMS\FormBundle\Submit\AbstractResponse;

class ServiceNowResponse extends AbstractResponse
{
    public function __construct(string $json)
    {
        parent::__construct($this->deriveStatus($json), $json);
    }

    public function getResultProperty(string $property): string
    {
        $decodedData = \json_decode($this->data, true);
        if (JSON_ERROR_NONE !== \json_last_error()) {
            return '';
        }

        return $decodedData['result'][$property] ?? '';
    }

    private function deriveStatus(string $json): string
    {
        $data = \json_decode($json, true);

        if (JSON_ERROR_NONE !== \json_last_error()) {
            return self::STATUS_ERROR;
        }

        if (isset($data['status']) && 'failure' === $data['status']) {
            return self::STATUS_ERROR;
        }

        return self::STATUS_SUCCESS;
    }
}
