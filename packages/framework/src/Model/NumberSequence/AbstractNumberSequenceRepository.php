<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\NumberSequence;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use Shopsys\FrameworkBundle\Model\NumberSequence\Exception\NumberSequenceNotFoundException;

abstract class AbstractNumberSequenceRepository
{
    protected const int ID = 1;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository<\Shopsys\FrameworkBundle\Model\NumberSequence\AbstractNumberSequence>
     */
    abstract protected function getNumberSequenceRepository(): EntityRepository;

    /**
     * @return string
     */
    public function getNextNumber(): string
    {
        try {
            $this->em->beginTransaction();

            $requestedNumber = time();

            $numberSequenceRepository = $this->getNumberSequenceRepository();
            /** @var \Shopsys\FrameworkBundle\Model\NumberSequence\AbstractNumberSequence|null $numberSequence */
            $numberSequence = $numberSequenceRepository->find(
                static::ID,
                LockMode::PESSIMISTIC_WRITE,
            );

            if ($numberSequence === null) {
                throw new NumberSequenceNotFoundException(
                    sprintf('Number sequence ID %d not found by %s', static::ID, $numberSequenceRepository::class),
                );
            }

            $lastNumber = $numberSequence->getNumber();

            if ($requestedNumber <= $lastNumber) {
                $requestedNumber = (int)$lastNumber + 1;
            }

            $numberSequence->setNumber((string)$requestedNumber);

            $this->em->flush();
            $this->em->commit();
        } catch (Exception $e) {
            $this->em->rollback();

            throw $e;
        }

        return $numberSequence->getNumber();
    }
}
