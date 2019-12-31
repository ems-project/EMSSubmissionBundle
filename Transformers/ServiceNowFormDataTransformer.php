<?php

namespace EMS\SubmissionBundle\Transformers;

class ServiceNowFormDataTransformer extends AbstractFormDataTransformer
{
    public function transform(): array
    {
        $data = $this->form->getData();

        return $this->mapNewFieldTimeFormat($data);
    }

    private function mapNewFieldTimeFormat(array $data): array
    {
        $newFields = $this->getNewFieldTimeFormatValues();

        foreach ($newFields as $k => $v) {
            $data = $this->arrayReplace($data, $k, $v);
        }

        return $data;
    }

    private function getNewFieldTimeFormatValues(): array
    {
        $newFields = [];

        $types = [
            'Symfony\Component\Form\Extension\Core\Type\TimeType'
        ];

        foreach ($this->getFieldsByTypes($types) as $field) {
            $newFields[$field->getName()] = $field->getNormData()->format('Y-m-d H:i:s');
        }

        return $newFields;
    }
}
