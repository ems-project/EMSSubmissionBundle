<?php

namespace EMS\SubmissionBundle\Handler;

use EMS\FormBundle\FormConfig\FormConfig;
use EMS\SubmissionBundle\FormConfig\SubmissionConfig;
use Symfony\Component\Form\FormInterface;

class ServiceNowHandler extends AbstractHandler
{
    public function handle(SubmissionConfig $submission, FormInterface $form, FormConfig $config): string
    {
        // TODO: Implement handle() method.
        return "not implemented, sorry.";
    }
}