<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\Handler;

use EMS\FormBundle\Submission\AbstractHandler;
use EMS\SubmissionBundle\Tests\Functional\App\ResponseFactory;

final class HttpHandlerTest extends AbstractHandlerTest
{
    /** @var ResponseFactory */
    private $responseFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->responseFactory = $this->container->get(ResponseFactory::class);
    }

    protected function getHandler(): AbstractHandler
    {
        return $this->container->get('functional_test.emss.handler.http');
    }

    public function testSubmitFormData(): void
    {
        $endpoint = '';
        $message = '';

        $this->assertEquals(
            '{"status":"success","data":"Submission send by http."}',
            $this->handle($this->createForm(), $endpoint, $message)->getResponse()
        );
    }
}
