<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Metric;

use EMS\CommonBundle\Common\Metric\MetricCollectorInterface;
use EMS\CommonBundle\Common\Standard\DateTime;
use EMS\SubmissionBundle\Repository\FormSubmissionRepository;
use Prometheus\CollectorRegistry;

final class SubmissionMetricCollector implements MetricCollectorInterface
{
    private FormSubmissionRepository $formSubmissionRepository;

    private const VALID_UNTIL = '+5 minutes';
    private const GAUGES = [
        'total' => 'Total form submissions',
        'unprocessed_total' => 'Total unprocessed submissions',
        'errors_total' => 'Total count error submissions',
    ];

    public function __construct(FormSubmissionRepository $formSubmissionRepository)
    {
        $this->formSubmissionRepository = $formSubmissionRepository;
    }

    public function getName(): string
    {
        return 'emss_submissions';
    }

    public function validUntil(): int
    {
        return DateTime::create(self::VALID_UNTIL)->getTimestamp();
    }

    public function collect(CollectorRegistry $collectorRegistry): void
    {
        $metrics = $this->formSubmissionRepository->getMetrics();
        $namespace = $this->getName();

        foreach (self::GAUGES as $gaugeName => $gaugeHelp) {
            $gauge = $collectorRegistry->getOrRegisterGauge(
                $namespace,
                $gaugeName,
                $gaugeHelp,
                ['form_instance', 'form_name']
            );

            foreach ($metrics as $data) {
                $gauge->set($data[$gaugeName], [$data['instance'], $data['name']]);
            }
        }
    }
}
