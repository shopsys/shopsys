<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer\Issue;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Transfer\Transfer;
use Shopsys\FrameworkBundle\Model\Transfer\TransferRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TransferIssueFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly TransferIssueRepository $transferIssueRepository,
        protected readonly TransferRepository $transferRepository,
        protected readonly TransferIssueFactory $transferIssueFactory,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function saveTransferIssues(array $transferIssuesData, string $serviceTransferIdentifier): void
    {
        foreach ($transferIssuesData as $transferIssueData) {
            $transfer = $this->transferRepository->getTransferByIdentifier($serviceTransferIdentifier);
            $transferIssue = $this->transferIssueFactory->create($transfer, $transferIssueData);
            $this->em->persist($transferIssue);
        }
        $this->em->flush();
    }

    public function getTransferIssuesQueryBuilderForDataGrid(): QueryBuilder
    {
        $fromDateTime = $this->clock->now()->modify('-7 days');

        return $this->transferIssueRepository->getTransferIssuesQueryBuilderForDataGrid($fromDateTime);
    }

    public function getTransferIssuesCountFrom(DateTimeImmutable $fromDateTime): int
    {
        return $this->transferIssueRepository->getTransferIssuesCountFrom($fromDateTime);
    }

    public function deleteById(int $id): void
    {
        $transferIssue = $this->transferIssueRepository->findById($id);

        if ($transferIssue === null) {
            throw new NotFoundHttpException('Transfer issue ' . $id . ' not found');
        }

        $transferIssue->setDeletedAt($this->clock->now());
        $this->em->flush();
    }

    public function create(TransferIssueData $transferIssueData, Transfer $transfer): TransferIssue
    {
        $transferIssue = $this->transferIssueFactory->create($transfer, $transferIssueData);
        $this->em->persist($transferIssue);
        $this->em->flush();

        return $transferIssue;
    }
}
