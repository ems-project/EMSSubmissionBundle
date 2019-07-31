<?php

namespace EMS\SubmissionBundle\Connection;

class Transformer
{
    /** @var array */
    private $connections;

    public function __construct(array $connections)
    {
        $this->connections = $connections;
    }

    public function transform(array $path): string
    {
        if (empty($path)) {
            return '';
        }

        if (count($path) === 1) {
            return $path[0];
        }

        $conn = $this->getConnection($path[0]);
        return $conn->callByKey($path[1]);
    }

    private function getConnection(string $name): ServiceNowConnection
    {
        foreach ($this->connections as $connection) {
            if (! isset($connection['connection']) || $connection['connection'] != $name) {
                continue;
            }

            return new ServiceNowConnection($connection);
        }
    }
}
