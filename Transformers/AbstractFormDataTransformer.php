<?php

namespace EMS\SubmissionBundle\Transformers;

use Symfony\Component\Form\FormInterface;

abstract class AbstractFormDataTransformer
{
    /** @var FormInterface */
    protected $form;

    public function __construct(FormInterface $form)
    {
        $this->form = $form;
    }

    protected function getFieldsByTypes(array $types): array
    {
        $typeFields = [];
        foreach ($this->getFields($this->form->all()) as $field)
        {
            if (in_array($this->getFieldType($field), $types)) {
                $typeFields[] = $field;
            }
        }

        return $typeFields;
    }

    protected function getFields(array $form): array
    {
        $fields = [];
        foreach ($form as $field)
        {
            $children = $field->all();

            if (!empty($children)) {
                $fields = array_merge($fields, $this->getFields($children));
            } else {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    protected function getFieldType(FormInterface $field): string
    {
        return get_class($field->getConfig()->getType()->getInnerType());
    }

    protected function arrayReplace(array $array, string $find, string $replace): array
    {
        if (is_array($array)) {
            foreach ($array as $k => $v) {
                if(is_array($array[$k])) {
                    $array[$k] = $this->arrayReplace($array[$k], $find, $replace);
                } else {
                    if($k === $find) {
                        $array[$k] = $replace;
                    }
                }
            }
        }

        return $array;
    }
}
