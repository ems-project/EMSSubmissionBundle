<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Metric;

use EMS\CommonBundle\Contracts\Metric\EMSMetricsCollectorInterface;
use EMS\SubmissionBundle\Repository\FormSubmissionRepository;
use Prometheus\CollectorRegistry;
use Prometheus\Exception\MetricNotFoundException;

class MetricCollector implements EMSMetricsCollectorInterface
{
    private FormSubmissionRepository $formSubmissionRepository;

    public function __construct(FormSubmissionRepository $formSubmissionRepository)
    {
        $this->formSubmissionRepository = $formSubmissionRepository;
    }

    /**
     * @throws MetricNotFoundException
     */
    public function collect(CollectorRegistry $registry): void
    {
        $totalSubmissionsLive = $this->formSubmissionRepository->countSubmissionsByInstance('live');
        $totalSubmissionsPreview = $this->formSubmissionRepository->countSubmissionsByInstance('preview');
        $countSubmission = $registry->getOrRegisterGauge(
            'emss',
            'submissions_counter',
            'The number of submissions',
            ['env']
        );
        $countSubmission->set(floatval($totalSubmissionsLive), ['live']);
        $countSubmission->set(floatval($totalSubmissionsPreview), ['preview']);

        $allSubmissions = $this->formSubmissionRepository->findAll();
        $allFormsName = [];
        foreach($allSubmissions as $submission){
            $name = $submission->getName();
            if(!array_key_exists($name, $allFormsName)){
                $allFormsName[$name] = 1;
            } else {
                $allFormsName[$name] += 1;
            }
        }
        $countByForm = $registry->getOrRegisterGauge(
            'emss',
            'submissions_counter_form',
            'The number of submissions by form. Do not count the forms without submission.',
            ['form']
        );
        foreach($allFormsName as $key=>$value){
            $countByForm->set(floatval($value), [$key]);
        }
    }
}