<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GiftVoucher;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class GiftVoucherFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GiftVoucherRepository $giftVoucherRepository,
        protected readonly GiftVoucherFactory $giftVoucherFactory,
        protected readonly GiftVoucherCodeGenerator $giftVoucherCodeGenerator,
    ) {
    }

    public function getById(int $giftVoucherId): GiftVoucher
    {
        return $this->giftVoucherRepository->getById($giftVoucherId);
    }

    public function getByUuid(string $uuid): GiftVoucher
    {
        return $this->giftVoucherRepository->getByUuid($uuid);
    }

    public function findByCode(string $code): ?GiftVoucher
    {
        return $this->giftVoucherRepository->findByCode(
            $this->giftVoucherCodeGenerator->normalizeCode($code),
        );
    }

    public function create(GiftVoucherData $giftVoucherData): GiftVoucher
    {
        if ($giftVoucherData->code === null) {
            $giftVoucherData->code = $this->giftVoucherCodeGenerator->generateUniqueCode();
        }

        $giftVoucher = $this->giftVoucherFactory->create($giftVoucherData);
        $this->em->persist($giftVoucher);
        $this->em->flush();

        return $giftVoucher;
    }

    public function edit(int $giftVoucherId, GiftVoucherData $giftVoucherData): GiftVoucher
    {
        $giftVoucher = $this->getById($giftVoucherId);
        $giftVoucher->edit($giftVoucherData);
        $this->em->flush();

        return $giftVoucher;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher[] $giftVouchers
     */
    public function markEmailAsEnqueued(array $giftVouchers): void
    {
        $emailEnqueuedAt = new DateTimeImmutable();

        foreach ($giftVouchers as $giftVoucher) {
            $giftVoucher->markEmailAsEnqueued($emailEnqueuedAt);
        }

        $this->em->flush();
    }

    /**
     * @param int[] $giftVoucherIds
     */
    public function markEmailAsSentByIds(array $giftVoucherIds): void
    {
        $emailSentAt = new DateTimeImmutable();

        foreach ($this->giftVoucherRepository->getAllByIds($giftVoucherIds) as $giftVoucher) {
            $giftVoucher->markEmailAsSent($emailSentAt);
        }

        $this->em->flush();
    }
}
