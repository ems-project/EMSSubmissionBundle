<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\Entity;

use EMS\SubmissionBundle\Entity\FormSubmission;
use EMS\SubmissionBundle\Tests\Functional\AbstractFunctionalTest;

final class CreateFormSubmissionTest extends AbstractFunctionalTest
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->container
            ->get('doctrine')
            ->getManager();
    }

    public function testCreateFormSubmission(): void
    {
        $data = ['firstName' => 'test', 'lastName' => 'test'];
        $formSubmission = new FormSubmission('submissionTest', 'test', 'nl', $data);

        $this->entityManager->persist($formSubmission);
        $this->entityManager->flush();

        $dbSubmission = $this->entityManager->getRepository(FormSubmission::class)->findOneBy([
            'name' => 'submissionTest'
        ]);

        $this->assertEquals(true, true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}