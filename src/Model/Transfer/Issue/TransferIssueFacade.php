<?php

declare(strict_types=1);

namespace App\Model\Transfer\Issue;

use App\Model\Transfer\Transfer;
use Doctrine\ORM\EntityManagerInterface;

class TransferIssueFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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

    /**
     * @param array $transferIssuesData
     * @param \App\Model\Transfer\Transfer $transfer
     */
    public function saveTransferIssues(array $transferIssuesData, Transfer $transfer): void
    {
        foreach ($transferIssuesData as $transferIssueData) {
            $this->create($transferIssueData, $transfer);
        }
    }
}
