<?php

namespace Shopsys\FrameworkBundle\Model\Order;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNumberSequenceNotFoundException;

class OrderNumberSequenceRepository
{
    protected const ID = 1;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getOrderNumberSequenceRepository()
    {
        return $this->em->getRepository(OrderNumberSequence::class);
    }

    /**
     * @return int
     */
    public function getNextNumber()
    {
        try {
            $this->em->beginTransaction();

            $requestedNumber = time();

            /** @var \Shopsys\FrameworkBundle\Model\Order\OrderNumberSequence|null $orderNumberSequence */
            $orderNumberSequence = $this->getOrderNumberSequenceRepository()->find(
                static::ID,
                LockMode::PESSIMISTIC_WRITE
            );
            if ($orderNumberSequence === null) {
                throw new OrderNumberSequenceNotFoundException(
                    'Order number sequence ID ' . static::ID . ' not found.'
                );
            }

            $lastNumber = $orderNumberSequence->getNumber();

            if ($requestedNumber <= $lastNumber) {
                $requestedNumber = (int)$lastNumber + 1;
            }

            $orderNumberSequence->setNumber((string)$requestedNumber);

            $this->em->flush();
            $this->em->commit();
        } catch (Exception $e) {
            $this->em->rollback();
            throw $e;
        }

        return $requestedNumber;
    }
}
