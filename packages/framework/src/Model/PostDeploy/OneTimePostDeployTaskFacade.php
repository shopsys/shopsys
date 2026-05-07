<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PostDeploy;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class OneTimePostDeployTaskFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OneTimePostDeployTaskRecordFactory $oneTimePostDeployTaskRecordFactory,
        protected readonly OneTimePostDeployTaskRepository $oneTimePostDeployTaskRepository,
    ) {
    }

    /**
     * @return string[]
     */
    public function getAllNames(): array
    {
        return $this->oneTimePostDeployTaskRepository->getAllNames();
    }

    public function markExecuted(string $name): void
    {
        $record = $this->oneTimePostDeployTaskRecordFactory->create($name, new DateTimeImmutable());
        $this->em->persist($record);
        $this->em->flush();
    }
}
