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
        $gauge = $registry->getOrRegisterGauge(
            'EMSS',
            'Form_counter',
            'The number of form',
            ['numberOfForm']
        );
        $gauge->set(($this->formSubmissionRepository->count([])), ['formCounter']);
    }
}