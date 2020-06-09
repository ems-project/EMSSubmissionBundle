<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\Handler;

use EMS\FormBundle\Handler\AbstractHandler;
use EMS\SubmissionBundle\Tests\Functional\App\ResponseFactory;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ServiceNowHandlerTest extends AbstractHandlerTest
{
    /** @var string */
    private $credentials;
    /** @var ResponseFactory */
    private $responseFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->credentials = \base64_encode(\sprintf('%s:%s', 'userA', 'passB')); //see config.yml
        $this->responseFactory = $this->container->get(ResponseFactory::class);
    }

    protected function getHandler(): AbstractHandler
    {
        return $this->container->get('functional_test.emss.servicenowhandler');
    }

    public function testSubmitFormData(): void
    {
        $endpoint = json_encode([
            'host' => 'https://example.service-now.com',
            'table' => 'table_name',
            'bodyEndpoint' => '/api/now/v1/table',
            'username' => "{{'service-now-instance-a%.%user'|emss_connection}}",
            'password' => "{{'service-now-instance-a%.%password'|emss_connection}}",
        ]);
        $message = json_encode([
           'body' => [
               'title' => 'Test serviceNow',
               'name' => '{{ data.first_name }}',
           ],
        ]);

        $this->responseFactory->setCallback(function (string $method, string $url, array $options = []) {
            if ('POST' === $method && 'https://example.service-now.com/api/now/v1/table/table_name' === $url) {
                $this->assertEquals('{"title":"Test serviceNow","name":"testFirstName"}', $options['body']);
                $this->assertEquals('19', $options['timeout']); //see config.yml

                $this->assertSame([
                    'Accept: application/json',
                    'Content-Type: application/json',
                    \sprintf('Authorization: Basic %s', $this->credentials),
                ], $options['headers']);

                return new MockResponse('{"message": "example"}');
            }

            throw new \Exception(sprintf('response not mocked for %s', $url));
        });

        $this->assertEquals(
            '{"status":"success","data":"{\"message\": \"example\"}"}',
            $this->handle($this->createForm(), $endpoint, $message)->getResponse()
        );
    }

    public function testSubmitMultipleFiles(): void
    {
        $endpoint = json_encode([
            'host' => 'https://example.service-now.com',
            'table' => 'table_name',
            'bodyEndpoint' => '/api/now/v1/table',
            'attachmentEndpoint' => '/api/now/v1/attachment/file',
            'username' => "{{'service-now-instance-a%.%user'|emss_connection}}",
            'password' => "{{'service-now-instance-a%.%password'|emss_connection}}",
        ]);
        $message = json_encode([
            'body' => [
                'title' => 'Test serviceNow',
                'info' => '{{ data.info }}',
            ],
            'attachments' => [
                'file1' => [
                    'pathname' => '{{ data.files.0.getPathname()|json_encode }}',
                    'originalName' => '{{ data.files.0.getClientOriginalName() }}',
                    'mimeType' => '{{ data.files.0.getClientMimeType() }}',
                ],
                'file2' => [
                    'pathname' => '{{ data.files.1.getPathname()|json_encode }}',
                    'originalName' => '{{ data.files.1.getClientOriginalName() }}',
                    'mimeType' => '{{ data.files.1.getClientMimeType() }}',
                ],
            ],
        ]);

        $attachmentUrl = 'https://example.service-now.com/api/now/v1/attachment/file';
        $sysId = 98765;
        $attachmentUrls = [
            $attachmentUrl.'?file_name=attachment.txt&table_name=table_name&table_sys_id='.$sysId,
            $attachmentUrl.'?file_name=attachment2.txt&table_name=table_name&table_sys_id='.$sysId,
        ];

        $this->responseFactory->setCallback(
            function (string $method, string $url, array $options = []) use ($attachmentUrls, $sysId) {
                if ('https://example.service-now.com/api/now/v1/table/table_name' === $url) {
                    $this->assertEquals('{"title":"Test serviceNow","info":"Uploaded 2 files"}', $options['body']);

                    return new MockResponse(\json_encode(['result' => ['sys_id' => $sysId]]));
                }

                if (in_array($url, $attachmentUrls)) {
                    $this->assertSame([
                        'Content-Type: text/plain',
                        \sprintf('Authorization: Basic %s', $this->credentials),
                        'Accept: */*',
                    ], $options['headers']);

                    return new MockResponse('{}');
                }

                throw new \Exception(sprintf('response not mocked for %s', $url));
            }
        );

        $this->assertEquals(
            '{"status":"success","data":"{\"result\":{\"sys_id\":98765}}"}',
            $this->handle($this->createFormUploadFiles(), $endpoint, $message)->getResponse()
        );
    }

    public function testPostAttachmentFails()
    {
        $endpoint = json_encode([
            'host' => 'https://example.service-now.com',
            'table' => 'table_name',
            'bodyEndpoint' => '/api/now/v1/table',
            'attachmentEndpoint' => '/api/now/v1/attachment/file',
            'username' => "{{'service-now-instance-a%.%user'|emss_connection}}",
            'password' => "{{'service-now-instance-a%.%password'|emss_connection}}",
        ]);
        $message = json_encode([
            'body' => '',
            'attachments' => [
                'file1' => [
                    'pathname' => '{{ data.files.0.getPathname()|json_encode }}',
                    'originalName' => '{{ data.files.0.getClientOriginalName() }}',
                    'mimeType' => '{{ data.files.0.getClientMimeType() }}',
                ],
            ],
        ]);

        $this->responseFactory->setCallback(function (string $method, string $url, array $options = []) {
            if ('https://example.service-now.com/api/now/v1/table/table_name' === $url) {
                return new MockResponse('{"message": "upload success"}');
            }

            return new MockResponse('{}', ['http_code' => 404]);
        });

        $this->assertEquals(
            '{"status":"error","data":"Submission failed, contact your admin. (Attachment submission failed: HTTP 404 returned for \"https:\/\/example.service-now.com\/api\/now\/v1\/attachment\/file?file_name=attachment.txt&table_name=table_name&table_sys_id=\".)"}',
            $this->handle($this->createFormUploadFiles(), $endpoint, $message)->getResponse()
        );
    }
}
