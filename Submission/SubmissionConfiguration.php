<?php

namespace EMS\SubmissionBundle\Submission;

class SubmissionConfiguration
{
    /** @var string */
    private $type;

    /** @var string */
    private $targetTemplate;

    /** @var string */
    private $originTemplate;

    /** @var string */
    private $dataTemplate;

    /** @var string */
    private $subjectTemplate;

    public function __construct(array $definition)
    {
        $this->type = $definition['type'];
        $this->targetTemplate = $definition['target'];
        $this->originTemplate = $definition['origin'];
        $this->dataTemplate = $definition['data'];
        $this->subjectTemplate = $definition['subject'];
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTargetTemplate(): string
    {
        return sprintf('@EMSCH/%s', $this->targetTemplate);
    }

    public function getOriginTemplate(): string
    {
        return sprintf('@EMSCH/%s', $this->originTemplate);
    }

    public function getDataTemplate(): string
    {
        return sprintf('@EMSCH/%s', $this->dataTemplate);
    }

    public function getSubjectTemplate(): string
    {
        return sprintf('@EMSCH/%s', $this->subjectTemplate);
    }
}
