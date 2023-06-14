<?php

declare(strict_types=1);

namespace App\Model\Transfer\Issue;

use App\Model\Transfer\Transfer;
use App\Model\Transfer\TransferRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TransferIssueFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Transfer\Issue\TransferIssueRepository $transferIssueRepository
     * @param \App\Model\Transfer\TransferRepository $transferRepository
     */
    public function __construct(
        private EntityManagerInterface $em,
        private TransferIssueRepository $transferIssueRepository,
        private TransferRepository $transferRepository,
    ) {
    }

    /**
     * @param array $transferIssuesData
     * @param string $serviceTransferIdentifier
     */
    public function saveTransferIssues(array $transferIssuesData, string $serviceTransferIdentifier): void
    {
        foreach ($transferIssuesData as $transferIssueData) {
            $transfer = $this->transferRepository->getTransferByIdentifier($serviceTransferIdentifier);
            $transferIssue = new TransferIssue($transfer, $transferIssueData);
            $this->em->persist($transferIssue);
        }
        $this->em->flush();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getTransferIssuesQueryBuilderForDataGrid(): QueryBuilder
    {
        $fromDateTime = new DateTime();
        $fromDateTime->modify('-7 days');

        return $this->transferIssueRepository->getTransferIssuesQueryBuilderForDataGrid($fromDateTime);
    }

    /**
     * @param \DateTime $fromDateTime
     * @return int
     */
    public function getTransferIssuesCountFrom(DateTime $fromDateTime): int
    {
        return $this->transferIssueRepository->getTransferIssuesCountFrom($fromDateTime);
    }

    /**
     * @param int $id
     */
    public function deleteById(int $id): void
    {
        $transferIssue = $this->transferIssueRepository->findById($id);

        if ($transferIssue === null) {
            throw new NotFoundHttpException('Transfer issue ' . $id . ' not found');
        }

        $transferIssue->setDeletedAt(new DateTime());
        $this->em->flush();
    }

    /**
     * @param \App\Model\Transfer\Issue\TransferIssueData $transferIssueData
     * @param \App\Model\Transfer\Transfer $transfer
     * @return \App\Model\Transfer\Issue\TransferIssue
     */
    public function create(TransferIssueData $transferIssueData, Transfer $transfer): TransferIssue
    {
        $transferIssue = new TransferIssue($transfer, $transferIssueData);
        $this->em->persist($transferIssue);
        $this->em->flush();

        return $transferIssue;
    }
}
