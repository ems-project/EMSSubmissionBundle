<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Connection;

final class ServiceNowConnection
{
    /** @var string */
    private $user;
    /** @var string */
    private $password;

    /**
     * @param array{'connection': string, 'user': string, 'password': string} $connection
     */
    public function __construct(array $connection)
    {
        $this->user = $connection['user'] ?? '';
        $this->password = $connection['password'] ?? '';
    }

    public function callByKey(string $key): string
    {
        $method = sprintf('get%s', ucfirst($key));

        if (!method_exists($this, $method)) {
            return $key;
        }

        return $this->$method();
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
