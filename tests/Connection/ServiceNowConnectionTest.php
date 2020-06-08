<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Connection;

use EMS\SubmissionBundle\Connection\ServiceNowConnection;
use PHPUnit\Framework\TestCase;

final class ServiceNowConnectionTest extends TestCase
{
    public function testCreateConnection(): void
    {
        $conn = new ServiceNowConnection([
           'connection' => 'service-now-instance-a',
           'user' => 'david',
           'password' => 'itsSecret'
        ]);

        $this->assertEquals('david', $conn->getUser());
        $this->assertEquals('itsSecret', $conn->getPassword());
        $this->assertEquals('david', $conn->callByKey('user'));
        $this->assertEquals('david', $conn->callByKey('User'));
        $this->assertEquals('itsSecret', $conn->callByKey('password'));
        $this->assertEquals('pass', $conn->callByKey('pass'));
    }
}