<?php

declare(strict_types=1);

namespace App\Model\Transfer\Issue;

use App\Model\Transfer\Transfer;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TransferIssueFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \App\Model\Transfer\Issue\TransferIssueRepository
     */
    private $transferIssueRepository;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Transfer\Issue\TransferIssueRepository $transferIssueRepository
     */
    public function __construct(EntityManagerInterface $em, TransferIssueRepository $transferIssueRepository)
    {
        $this->em = $em;
        $this->transferIssueRepository = $transferIssueRepository;
    }

    /**
     * @param array $transferIssuesData
     * @param \App\Model\Transfer\Transfer $transfer
     */
    public function saveTransferIssues(array $transferIssuesData, Transfer $transfer): void
    {
        foreach ($transferIssuesData as $transferIssueData) {
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
}
