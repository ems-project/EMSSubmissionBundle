<?php

namespace EMS\SubmissionBundle\Controller;

use EMS\SubmissionBundle\Service\SubmissionClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SubmissionController extends AbstractController
{
    /** @var SubmissionClient */
    private $submissionClient;

    public function __construct(SubmissionClient $submissionClient)
    {
        $this->submissionClient = $submissionClient;
    }

    public function submit(string $submissionId, string $locale, array $data = [])
    {
        return $this->render('@EMSForm/form-api/submitted.html.twig', [
            'data' => $this->submissionClient->submit($submissionId, $locale, $data),
        ]);
    }
}
