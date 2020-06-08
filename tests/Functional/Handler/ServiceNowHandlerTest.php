<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\Handler;

use EMS\FormBundle\FormConfig\FormConfig;
use EMS\FormBundle\FormConfig\SubmissionConfig;
use EMS\FormBundle\Submit\AbstractResponse;
use EMS\SubmissionBundle\Handler\EmailHandler;
use EMS\SubmissionBundle\Handler\ServiceNowHandler;
use EMS\SubmissionBundle\Tests\Functional\AbstractFunctionalTest;
use EMS\SubmissionBundle\Tests\Functional\App\ResponseFactory;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ServiceNowHandlerTest extends AbstractFunctionalTest
{
    /** @var string */
    private $credentials;
    /** @var ServiceNowHandler */
    private $serviceNowHandler;
    /** @var ResponseFactory */
    private $responseFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->credentials = \base64_encode(\sprintf('%s:%s', 'userA', 'passB')); //see config.yml
        $this->serviceNowHandler = $this->container->get('functional_test.emss.servicenowhandler');
        $this->responseFactory = $this->container->get(ResponseFactory::class);
    }

    public function testSubmitFormData(): void
    {
        $data = ['name' => 'David', 'email' => 'user1@test.test'];
        $form = $this->formFactory->createBuilder(FormType::class, $data, [])
            ->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->getForm();

        $endpoint = json_encode([
            'host' => 'https://example.service-now.com',
            'table' => 'table_name',
            'bodyEndpoint' => '/api/now/v1/table',
            'username' => "{{'service-now-instance-a%.%user'|emss_connection}}",
            'password' => "{{'service-now-instance-a%.%password'|emss_connection}}"
        ]);

        $message = json_encode([
           'body' => [
               'title' => 'Test serviceNow',
               'name' => '{{ data.name }}'
           ]
        ]);

        $this->responseFactory->setCallback(function (string $method, string $url, array $options = []) {
            if ($method === 'POST' && $url === 'https://example.service-now.com/api/now/v1/table/table_name') {
                $this->assertEquals('{"title":"Test serviceNow","name":"David"}', $options['body']);
                $this->assertEquals('19', $options['timeout']); //see config.yml

                $this->assertSame([
                    'Accept: application/json',
                    'Content-Type: application/json',
                    \sprintf('Authorization: Basic %s', $this->credentials)
                ], $options['headers']);

                return new MockResponse('{"message": "example"}');
            }

            throw new \Exception(sprintf('response not mocked for %s', $url));
        });

        $this->assertEquals(
            '{"status":"success","data":"{\"message\": \"example\"}"}',
            $this->handle($form, $endpoint, $message)->getResponse()
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
            'password' => "{{'service-now-instance-a%.%password'|emss_connection}}"
        ]);

        $message = json_encode([
            'body' => [
                'title' => 'Test serviceNow',
                'name' => '{{ data.name }}'
            ],
            'attachments' => [
                'file1' => [
                    'pathname' => '{{ data.files.0.getPathname()|json_encode }}',
                    'originalName' => '{{ data.files.0.getClientOriginalName() }}',
                    'mimeType' => '{{ data.files.0.getClientMimeType() }}'
                ],
                'file2' => [
                    'pathname' => '{{ data.files.1.getPathname()|json_encode }}',
                    'originalName' => '{{ data.files.1.getClientOriginalName() }}',
                    'mimeType' => '{{ data.files.1.getClientMimeType() }}'
                ]
            ]
        ]);

        $attachmentUrl = 'https://example.service-now.com/api/now/v1/attachment/file';
        $sysId = 98765;
        $attachmentUrls = [
            $attachmentUrl . '?file_name=attachment.txt&table_name=table_name&table_sys_id=' . $sysId,
            $attachmentUrl . '?file_name=attachment2.txt&table_name=table_name&table_sys_id=' . $sysId,
        ];

        $this->responseFactory->setCallback(
            function (string $method, string $url, array $options = []) use ($attachmentUrls, $sysId) {
                if ($url === 'https://example.service-now.com/api/now/v1/table/table_name') {
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
            $this->handle($this->createUploadFilesForm(), $endpoint, $message)->getResponse()
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
            'password' => "{{'service-now-instance-a%.%password'|emss_connection}}"
        ]);
        $message = json_encode([
            'body' => '',
            'attachments' => [
                'file1' => [
                    'pathname' => '{{ data.files.0.getPathname()|json_encode }}',
                    'originalName' => '{{ data.files.0.getClientOriginalName() }}',
                    'mimeType' => '{{ data.files.0.getClientMimeType() }}'
                ]
            ]
        ]);

        $this->responseFactory->setCallback(function (string $method, string $url, array $options = []) {
            if ($url === 'https://example.service-now.com/api/now/v1/table/table_name') {
                return new MockResponse('{"message": "upload success"}');
            }

            return new MockResponse('{}', ['http_code' => 404]);
        });

        $this->assertEquals(
            '{"status":"error","data":"Submission failed, contact your admin. (Attachment submission failed: HTTP 404 returned for \"https:\/\/example.service-now.com\/api\/now\/v1\/attachment\/file?file_name=attachment.txt&table_name=table_name&table_sys_id=\".)"}',
            $this->handle($this->createUploadFilesForm(), $endpoint, $message)->getResponse()
        );
    }

    private function handle(FormInterface $form, string $endpoint, string $message): AbstractResponse
    {
        $submission = new SubmissionConfig(EmailHandler::class, $endpoint, $message);
        $formConfig = new FormConfig('1', 'nl', 'nl');

        return $this->serviceNowHandler->handle($submission, $form, $formConfig);
    }
}
