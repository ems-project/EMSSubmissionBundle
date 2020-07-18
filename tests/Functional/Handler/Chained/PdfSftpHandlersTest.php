<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\Handler\Chained;

use EMS\SubmissionBundle\Handler\PdfHandler;
use EMS\SubmissionBundle\Handler\SftpHandler;
use EMS\SubmissionBundle\Response\PdfHandleResponse;
use EMS\SubmissionBundle\Response\SftpHandleResponse;
use EMS\SubmissionBundle\Tests\Functional\App\FilesystemFactory;

final class PdfSftpHandlersTest extends AbstractChainedTest
{
    /** @var FilesystemFactory */
    private $filesystemFactory;
    /** @var PdfHandler */
    private $pdfHandler;
    /** @var SftpHandler */
    private $sftpHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystemFactory = $this->container->get('emss.filesystem.factory');
        $this->pdfHandler = $this->container->get('functional_test.emss.handler.pdf');
        $this->sftpHandler = $this->container->get('functional_test.emss.handler.sftp');
    }

    public function testPdfSftpChain(): void
    {
        $pdfEndpoint = \json_encode(['filename' => 'form.pdf']);
        $pdfMessage = \file_get_contents(__DIR__.'/../../fixtures/twig/chainedPdfSftp/message_pdf.twig');
        $pdfHandleRequest = $this->createRequest(PdfHandler::class, $pdfEndpoint, $pdfMessage);
        /** @var PdfHandleResponse $pdfHandleResponse */
        $pdfHandleResponse = $this->pdfHandler->handle($pdfHandleRequest);

        $this->responseCollector->addResponse($pdfHandleResponse);

        $sftpEndpointJson = \json_encode(['host' => '127.0.0.1']);
        $sftpMessage = \file_get_contents(__DIR__.'/../../fixtures/twig/chainedPdfSftp/message_sftp.twig');
        $sftpHandleRequest = $this->createRequest(SftpHandler::class, $sftpEndpointJson, $sftpMessage);
        /** @var SftpHandleResponse $sftpHandleResponse */
        $sftpHandleResponse = $this->sftpHandler->handle($sftpHandleRequest);
        
        $this->assertCount(3, $sftpHandleResponse->getTransportedFiles());
        $this->assertEquals('test/attachment.txt', $sftpHandleResponse->getTransportedFiles()[0]['path']);

        $binaryPdf = $pdfHandleResponse->getContentRaw();
        $this->assertEquals($binaryPdf, $sftpHandleResponse->getTransportedFiles()[2]['contents']);
    }
}
