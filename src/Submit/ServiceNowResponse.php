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
        if (\json_last_error() !== JSON_ERROR_NONE) {
            return '';
        }

        return $decodedData['result'][$property] ?? '';
    }

    private function deriveStatus(string $json): string
    {
        $data = \json_decode($json, true);

        if (\json_last_error() !== JSON_ERROR_NONE) {
            return self::STATUS_ERROR;
        }

        if (isset($data['status']) && $data['status'] === 'failure') {
            return self::STATUS_ERROR;
        }

        return self::STATUS_SUCCESS;
    }
}
