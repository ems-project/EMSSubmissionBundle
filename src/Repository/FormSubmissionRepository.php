<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use EMS\SubmissionBundle\Dto\FormSubmissionsCountDto;
use EMS\SubmissionBundle\Entity\FormSubmission;

final class FormSubmissionRepository extends ServiceEntityRepository
{
    public function findById(string $id): ?FormSubmission
    {
        try {
            $qb = $this->createQueryBuilder('fs');
            $qb
                ->andWhere($qb->expr()->eq('fs.id', ':id'))
                ->setParameter('id', $id);

            return $qb->getQuery()->getOneOrNullResult();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getCounts(string $name, string $period, ?string $instance): FormSubmissionsCountDto
    {
        $qb = $this->createCountQueryBuilder($name, $instance);

        $countDto = new FormSubmissionsCountDto($period);
        $countDto->setWaiting($this->countWaiting(clone $qb));
        $countDto->setFailed($this->countFailed(clone $qb));
        $countDto->setProcessed($this->countProcessed(clone $qb));
        $countDto->setTotal($this->countTotal(clone $qb));

        $this->setPeriodCounts($countDto, clone $qb);

        return $countDto;
    }

    private function setPeriodCounts(FormSubmissionsCountDto $countDto, QueryBuilder $qb): void
    {
        $now = new \DateTime('now');
        $startDate = $now->modify(\sprintf('-%s', $countDto->period));
        $qb
            ->andWhere($qb->expr()->gte('fs.created', ':start_datetime'))
            ->setParameter('start_datetime', $startDate);

        $countDto->setPeriodWaiting($this->countWaiting(clone $qb));
        $countDto->setPeriodFailed($this->countFailed(clone $qb));
        $countDto->setPeriodProcessed($this->countProcessed(clone $qb));
        $countDto->setPeriodTotal($this->countTotal(clone $qb));
    }

    private function countFailed(QueryBuilder $qb): int
    {
        $qb->andWhere($qb->expr()->isNull('fs.processId'));
        $qb->andWhere($qb->expr()->gt('fs.processTryCounter', 0));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function countProcessed(QueryBuilder $qb): int
    {
        $qb->andWhere($qb->expr()->isNotNull('fs.processId'));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function countWaiting(QueryBuilder $qb): int
    {
        $qb
            ->andWhere($qb->expr()->isNull('fs.processId'))
            ->andWhere($qb->expr()->eq('fs.processTryCounter', 0));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function countTotal(QueryBuilder $qb): int
    {
        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function createCountQueryBuilder(string $name, ?string $instance): QueryBuilder
    {
        $qb = $this->createQueryBuilder('fs');
        $qb
            ->select('count(fs.id)')
            ->andWhere($qb->expr()->eq('fs.name', ':name'))
            ->setParameter('name', $name);

        if (null !== $instance) {
            $qb
                ->andWhere($qb->expr()->eq('fs.instance', ':instance'))
                ->setParameter('instance', $instance);
        }

        return $qb;
    }
}
