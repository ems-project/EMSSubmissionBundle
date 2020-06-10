<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Connection;

final class Transformer
{
    /**
     * @var array<array{'connection': string, 'user': string, 'password': string}>
     */
    private $connections;

    /**
     * @param array<array{'connection': string, 'user': string, 'password': string}> $connections
     */
    public function __construct(array $connections)
    {
        $this->connections = $connections;
    }

    /**
     * @param array<string> $path ['service-now-instance-a', 'password']
     */
    public function transform(array $path): string
    {
        if (empty($path)) {
            return '';
        }

        if (1 === count($path)) {
            return $path[0];
        }

        $conn = $this->getConnection($path[0]);

        return null === $conn ? $path[0] : $conn->callByKey($path[1]);
    }

    private function getConnection(string $name): ?ServiceNowConnection
    {
        foreach ($this->connections as $connection) {
            if (!isset($connection['connection']) || $connection['connection'] != $name) {
                continue;
            }

            return new ServiceNowConnection($connection);
        }

        return null;
    }
}
