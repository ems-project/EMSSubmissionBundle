<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\Handler;

use EMS\FormBundle\FormConfig\SubmissionConfig;
use EMS\FormBundle\Submission\AbstractHandler;
use EMS\FormBundle\Submission\HandleRequest;
use EMS\FormBundle\Submission\HandleResponseCollector;
use EMS\SubmissionBundle\Handler\EmailHandler;
use EMS\SubmissionBundle\Response\EmailHandleResponse;
use EMS\SubmissionBundle\Response\PdfHandleResponse;

final class PdfHandlerTest extends AbstractHandlerTest
{
    protected function getHandler(): AbstractHandler
    {
        return $this->container->get('functional_test.emss.handler.pdf');
    }

    public function testSubmitFormData(): void
    {
        $message = file_get_contents(__DIR__.'/../fixtures/pdf/pdf.html');

        /** @var PdfHandleResponse $response */
        $response = $this->handle($this->createForm(['info' => 'test.pdf']), '{{ data.info }}', $message);

        $this->assertEquals(
            '{"status":"success","data":"Pdf ready for next handler"}',
            $response->getResponse()
        );
        $this->assertEquals('test.pdf', $response->getFilename());
        $this->assertNotEmpty($response->getContent());
    }

    public function testEmptyEndpoint(): void
    {
        $message = file_get_contents(__DIR__.'/../fixtures/pdf/pdf.html');
        $response = $this->handle($this->createForm(['info' => 'test.pdf']), '', $message);

        $this->assertEquals(
            '{"status":"error","data":"Submission failed, contact your admin. (Endpoint not defined.)"}',
            $response->getResponse()
        );
    }

    public function testPdfEmail(): void
    {
        $message = file_get_contents(__DIR__.'/../fixtures/pdf/pdf.html');
        $form = $this->createForm();

        /** @var PdfHandleResponse $response */
        $pdfResponse = $this->handle($form, 'test.pdf', $message);

        /** @var EmailHandler $emailHandler */
        $emailHandler = $this->container->get('functional_test.emss.handler.email');
        $emailMessage = file_get_contents(__DIR__.'/../fixtures/twig/message_pdf_email.twig');
        $emailSubmission = new SubmissionConfig(get_class($emailHandler), 'user@example.test', $emailMessage);

        $responseCollector = new HandleResponseCollector();
        $responseCollector->addResponse($pdfResponse);

        $emailHandleRequest = new HandleRequest($form, $this->formConfig, $responseCollector, $emailSubmission);
        /** @var EmailHandleResponse $emailResponse */
        $emailResponse = $emailHandler->handle($emailHandleRequest);
        /** @var \Swift_Attachment[] $attachments */
        $attachments = $emailResponse->getMessage()->getChildren();

        $this->assertEquals('{"status":"success","data":"Submission send by mail."}', $emailResponse->getResponse());
        $this->assertEquals('test.pdf', $attachments[0]->getFilename());
        $this->assertEquals($pdfResponse->getContentRaw(), $attachments[0]->getBody());
    }
}
