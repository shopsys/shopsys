<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer\Issue;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Transfer\Transfer;
use Shopsys\FrameworkBundle\Model\Transfer\TransferRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TransferIssueFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueRepository $transferIssueRepository
     * @param \Shopsys\FrameworkBundle\Model\Transfer\TransferRepository $transferRepository
     * @param \Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueFactory $transferIssueFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly TransferIssueRepository $transferIssueRepository,
        protected readonly TransferRepository $transferRepository,
        protected readonly TransferIssueFactory $transferIssueFactory,
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
            $transferIssue = $this->transferIssueFactory->create($transfer, $transferIssueData);
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
     * @param \Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueData $transferIssueData
     * @param \Shopsys\FrameworkBundle\Model\Transfer\Transfer $transfer
     * @return \Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssue
     */
    public function create(TransferIssueData $transferIssueData, Transfer $transfer): TransferIssue
    {
        $transferIssue = $this->transferIssueFactory->create($transfer, $transferIssueData);
        $this->em->persist($transferIssue);
        $this->em->flush();

        return $transferIssue;
    }
}
