<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\Handler;

use EMS\FormBundle\Submission\AbstractHandler;
use EMS\SubmissionBundle\Response\HttpHandleResponse;
use EMS\SubmissionBundle\Tests\Functional\App\ResponseFactory;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

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
        $endpoint = \json_encode([
            'url' => 'http://example.test/api/form',
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => 10,
            'query' => ['q' => 'test'],
        ]);
        $message = \json_encode(['test' => 'test']);

        $this->responseFactory->setCallback(function (string $method, string $url, array $options = []) {
            $this->assertEquals('POST', $method);
            $this->assertEquals('http://example.test/api/form?q=test', $url);
            $this->assertEquals(10.0, $options['timeout']);
            $this->assertEquals('{"test":"test"}', $options['body']);

            return new MockResponse('{"status": "submit", "uid": "PI-20200625-0000432"}', [
                'http_code' => 201,
            ]);
        });

        /** @var HttpHandleResponse $handleResponse */
        $handleResponse = $this->handle($this->createForm(), $endpoint, $message);

        $this->assertEquals('{"status": "submit", "uid": "PI-20200625-0000432"}', $handleResponse->getHttpResponseContent());
        $this->assertEquals(201, $handleResponse->getHttpResponse()->getStatusCode());
        $this->assertEquals('{"status":"success","message":"Submission send by http.","status_code":201}', $handleResponse->getResponse());
    }

    public function testAuthBasic()
    {
        $endpoint = \json_encode([
            'url' => 'http://example.test/api/form',
            'headers' => ['Content-Type' => 'application/json'],
            'auth_basic' => "{{'http-conn%.%user'|emss_connection}}:{{'http-conn%.%password'|emss_connection}}",
        ]);
        $message = \json_encode(['test' => 'test']);

        $this->responseFactory->setCallback(function (string $method, string $url, array $options = []) {
            $userPass = \base64_encode('userTest:testPass'); //see config.yml
            $this->assertEquals([
                'Content-Type: application/json',
                'Accept: */*',
                \sprintf('Authorization: Basic %s', $userPass),
            ], $options['headers']);

            return new MockResponse('{"status": "submit", "uid": "PI-20200625-0000432"}');
        });

        $this->assertEquals(
            '{"status":"success","message":"Submission send by http.","status_code":200}',
            $this->handle($this->createForm(), $endpoint, $message)->getResponse()
        );
    }

    public function testInvalidEndpoint(): void
    {
        $endpoint = \json_encode([
            'url' => 'http://example.test/api/form',
            'test' => 'test',
        ]);
        $message = \json_encode(['test' => 'test']);

        $this->assertEquals(
            '{"status":"error","data":"Submission failed, contact your admin. (Invalid endpoint configuration: The option \"test\" does not exist. Defined options are: \"auth_basic\", \"auth_bearer\", \"headers\", \"method\", \"query\", \"timeout\", \"url\".)"}',
            $this->handle($this->createForm(), $endpoint, $message)->getResponse()
        );
    }

    public function errorResponses(): array
    {
        return [
            '300_Response' => [new MockResponse('', ['http_code' => 400])],
            '400_Response' => [new MockResponse('', ['http_code' => 400])],
            '500_Response' => [new MockResponse('', ['http_code' => 500])],
        ];
    }

    /**
     * @dataProvider errorResponses
     */
    public function testErrorResponse(ResponseInterface $response): void
    {
        $endpoint = \json_encode(['url' => 'http://example.test/api/form']);

        $this->responseFactory->setCallback(function (string $method, string $url, array $options = []) use ($response) {
            return $response;
        });

        $expected = '{"status":"error","data":"Submission failed, contact your admin. (HTTP %d returned for \"http:\/\/example.test\/api\/form\".)"}';

        $this->assertEquals(
            \sprintf($expected, $response->getStatusCode()),
            $this->handle($this->createForm(), $endpoint, '')->getResponse()
        );
    }
}
