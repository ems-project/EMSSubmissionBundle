<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Request;

use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DatabaseRequest extends AbstractRequest
{
    /** @var string */
    private $formName;
    /** @var string */
    private $instance;
    /** @var string */
    private $locale;
    /** @var array<mixed> */
    private $data;
    /** @var array<int, array{filename: string, mimeType: string, base64: string, size: string, form_field: string}> */
    private $files;

    /**
     * @param array<string, mixed> $body
     */
    public function __construct(array $databaseRecord)
    {
        $record = $this->resolveDatabaseRecord($databaseRecord);

        $this->formName = $record['form_name'];
        $this->instance = $record['instance'];
        $this->locale = $record['locale'];
        $this->data = $record['data'];
        $this->files = $record['files'];
    }

    public function getFormName(): string
    {
        return $this->formName;
    }

    public function getInstance(): string
    {
        return $this->instance;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array<int, array{filename: string, mimeType: string, base64: string, size: string, form_field: string}>
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    protected function getEndpointOptionResolver(): OptionsResolver
    {
    }

    /**
     * @param array<mixed> $json
     *
     * @return array{form_name: string, instance: string, locale: string, data: array, files: array}
     */
    private function resolveDatabaseRecord(array $databaseRecord): array
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired(['form_name', 'locale', 'data', 'instance'])
            ->setDefault('files', [])
            ->setAllowedTypes('form_name', 'string')
            ->setAllowedTypes('locale', 'string')
            ->setAllowedTypes('data', 'array')
            ->setAllowedTypes('files', 'array')
        ;

        try {
            /** @var array{form_name: string, instance: string, locale: string, data: array, files: array} $json */
            $resolvedDatabaseRecord = $resolver->resolve($databaseRecord);

            $fileResolver = new OptionsResolver();
            $fileResolver->setRequired(['filename', 'mimeType', 'base64', 'size', 'form_field']);

            $resolvedDatabaseRecord['files'] = \array_map(function (array $file) use ($fileResolver) {
                return $fileResolver->resolve($file);
            }, $resolvedDatabaseRecord['files']);

            return $resolvedDatabaseRecord;
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException(\sprintf('Invalid database record: %s', $e->getMessage()));
        }
    }
}
