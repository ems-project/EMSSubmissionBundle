<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Command;

use EMS\SubmissionBundle\Repository\FormSubmissionRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;

final class DatabaseStatsCommand extends Command
{
    protected static $defaultName = 'emss:database:stats';

    /** @var \Swift_Mailer */
    private $mailer;
    /** @var FormSubmissionRepository */
    private $repository;
    /** @var Environment */
    private $twig;

    public function __construct(\Swift_Mailer $mailer, FormSubmissionRepository $repository, Environment $twig)
    {
        parent::__construct();
        $this->mailer = $mailer;
        $this->repository = $repository;
        $this->twig = $twig;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('formName', InputArgument::REQUIRED)
            ->addOption('instance', null, InputOption::VALUE_REQUIRED, 'instance')
            ->addOption('period', null, InputOption::VALUE_REQUIRED, 'period', '1 day')
            ->addOption('email-to', null, InputOption::VALUE_REQUIRED, 'to emails (comma separated)')
            ->addOption('email-subject', null, InputOption::VALUE_REQUIRED, 'subject', 'submission stats')
            ->addOption('email-from', null, InputOption::VALUE_REQUIRED, 'from email', 'noreply@elasticms.eu')
            ->addOption('email-from-name', null, InputOption::VALUE_REQUIRED, 'from name', 'elasticms form submissions')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $style->title('EMS Submission: database stats');

        /** @var string $formName */
        $formName = $input->getArgument('formName');
        /** @var string $instance */
        $instance = $input->getOption('instance');
        /** @var string $period */
        $period = $input->getOption('period');
        /** @var null|string $emailTo */
        $emailTo = $input->getOption('email-to');

        $counts = $this->repository->getCounts($formName, $period, $instance);
        $style->table(
            ['type', 'value'],
            \array_merge($counts->toArrayPeriod(), [new TableSeparator()], $counts->toArray())
        );

        if (null !== $emailTo) {
            $body = $this->twig->loadTemplate('@EMSSubmission/mail/stats.html.twig')->renderBlock('body', [
                'formName' => $formName,
                'count' => $counts,
                'from' => $input->getOption('email-from-name'),
            ]);

            $message = $this->createMessage($input);
            $message->setBody($body, 'text/html');

            $this->mailer->send($message);

            $style->success(\sprintf('Send stats email to: %s', $emailTo));
        }

        return 1;
    }

    private function createMessage(InputInterface $input): \Swift_Message
    {
        /** @var string $emailTo */
        $emailTo = $input->getOption('email-to');
        $toEmail = \explode(',', $emailTo);

        /** @var string $subject */
        $subject = $input->getOption('email-subject');
        /** @var string $fromEmail */
        $fromEmail = $input->getOption('email-from');
        /** @var string $fromName */
        $fromName = $input->getOption('email-from-name');

        $message = new \Swift_Message($subject);
        $message->setFrom($fromEmail, $fromName)->setTo($toEmail);

        return $message;
    }
}
