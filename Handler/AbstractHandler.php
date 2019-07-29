<?php

namespace EMS\SubmissionBundle\Handler;

use EMS\FormBundle\FormConfig\FormConfig;
use EMS\SubmissionBundle\FormConfig\SubmissionConfig;
use Symfony\Component\Form\FormInterface;

abstract class AbstractHandler
{
    /** @var \Twig_Environment */
    protected $templating;

    public function setTemplating(\Twig_Environment $templating)
    {
        $this->templating = $templating;
    }

    public function canHandle(string $class): bool
    {
        return $class === get_called_class();
    }

    abstract public function handle(SubmissionConfig $submission, FormInterface $form, FormConfig $config): string;
}