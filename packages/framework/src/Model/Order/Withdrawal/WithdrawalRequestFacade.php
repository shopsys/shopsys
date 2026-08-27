<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalAlreadyRequestedException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalException;

class WithdrawalRequestFacade
{
    protected const int CONFIRMATION_HASH_LENGTH = 64;
    protected const string CONFIRMATION_VALIDITY_MODIFIER = '-24 hours';

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly WithdrawalRequestFactory $withdrawalRequestFactory,
        protected readonly WithdrawalChecker $withdrawalChecker,
        protected readonly WithdrawalRequestRepository $withdrawalRequestRepository,
        protected readonly HashGenerator $hashGenerator,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function createOnly(Order $order, WithdrawalRequestData $withdrawalRequestData): WithdrawalRequest
    {
        $withdrawalRequest = $this->withdrawalRequestFactory->create($order, $withdrawalRequestData);

        $this->em->persist($withdrawalRequest);
        $this->em->flush();

        return $withdrawalRequest;
    }

    public function edit(int $withdrawalRequestId, WithdrawalRequestData $withdrawalRequestData): WithdrawalRequest
    {
        $withdrawalRequest = $this->withdrawalRequestRepository->getById($withdrawalRequestId);

        $withdrawalRequest->edit($withdrawalRequestData);

        $this->em->flush();

        return $withdrawalRequest;
    }

    public function createUnconfirmed(
        Order $order,
        WithdrawalRequestData $withdrawalRequestData,
    ): WithdrawalRequest {
        $existingWithdrawalRequest = $this->withdrawalRequestRepository->findIncludingUnconfirmedByOrder($order);

        if ($existingWithdrawalRequest !== null) {
            if ($existingWithdrawalRequest->isConfirmed() || $this->isConfirmationValid($existingWithdrawalRequest)) {
                throw new WithdrawalAlreadyRequestedException('Withdrawal has already been requested for this order');
            }

            $this->em->remove($existingWithdrawalRequest);
            $this->em->flush();
        }

        $withdrawalRequestData->confirmed = false;
        $withdrawalRequestData->confirmationHash = $this->getUniqueConfirmationHash();

        return $this->createOnly($order, $withdrawalRequestData);
    }

    public function confirm(WithdrawalRequest $withdrawalRequest): void
    {
        $withdrawalRequest->confirm();

        $this->em->flush();
    }

    public function findValidUnconfirmedByConfirmationHash(string $confirmationHash): ?WithdrawalRequest
    {
        return $this->withdrawalRequestRepository->findUnconfirmedByConfirmationHashAndRequestedAfter(
            $confirmationHash,
            $this->clock->now()->modify(static::CONFIRMATION_VALIDITY_MODIFIER),
        );
    }

    public function findConfirmedByOrder(Order $order): ?WithdrawalRequest
    {
        return $this->withdrawalRequestRepository->findConfirmedByOrder($order);
    }

    public function findIncludingUnconfirmedByOrder(Order $order): ?WithdrawalRequest
    {
        return $this->withdrawalRequestRepository->findIncludingUnconfirmedByOrder($order);
    }

    public function getConfirmedByOrder(Order $order): WithdrawalRequest
    {
        return $this->withdrawalRequestRepository->getConfirmedByOrder($order);
    }

    public function getById(int $id): WithdrawalRequest
    {
        return $this->withdrawalRequestRepository->getById($id);
    }

    protected function isConfirmationValid(WithdrawalRequest $withdrawalRequest): bool
    {
        return $withdrawalRequest->getConfirmationHash() !== null
            && $withdrawalRequest->getRequestedAt() > $this->clock->now()->modify(static::CONFIRMATION_VALIDITY_MODIFIER);
    }

    protected function getUniqueConfirmationHash(): string
    {
        do {
            $confirmationHash = $this->hashGenerator->generateHash(static::CONFIRMATION_HASH_LENGTH);
        } while ($this->withdrawalRequestRepository->existsByConfirmationHash($confirmationHash));

        return $confirmationHash;
    }

    public function canRequestWithdrawal(Order $order): bool
    {
        try {
            $this->withdrawalChecker->checkOrderWithdrawal($order);

            return true;
        } catch (WithdrawalException) {
            return false;
        }
    }
}
