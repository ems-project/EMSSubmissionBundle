<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests;

use EMS\SubmissionBundle\FilesystemFactory;
use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\FilesystemInterface;
use PHPUnit\Framework\TestCase;

final class FilesystemFactoryTest extends TestCase
{
    /** @var FilesystemFactory */
    private $filesystemFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystemFactory = new FilesystemFactory();
    }

    public function testCreate()
    {
        $adapter = new NullAdapter();
        $filesystem = $this->filesystemFactory->create($adapter);
        $this->assertInstanceOf(FilesystemInterface::class, $filesystem);
    }
}
