<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GiftVoucher;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\GiftVoucher\Exception\GiftVoucherNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Order;

class GiftVoucherRepository
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    protected function getGiftVoucherRepository(): EntityRepository
    {
        return $this->em->getRepository(GiftVoucher::class);
    }

    public function getById(int $giftVoucherId): GiftVoucher
    {
        $giftVoucher = $this->getGiftVoucherRepository()->find($giftVoucherId);

        if ($giftVoucher === null) {
            throw new GiftVoucherNotFoundException('Gift voucher with ID ' . $giftVoucherId . ' not found.');
        }

        return $giftVoucher;
    }

    public function getByUuid(string $uuid): GiftVoucher
    {
        $giftVoucher = $this->getGiftVoucherRepository()->findOneBy(['uuid' => $uuid]);

        if ($giftVoucher === null) {
            throw new GiftVoucherNotFoundException('Gift voucher with UUID ' . $uuid . ' not found.');
        }

        return $giftVoucher;
    }

    public function findByCode(string $code): ?GiftVoucher
    {
        return $this->getGiftVoucherRepository()->findOneBy(['code' => $code]);
    }

    public function existsByCode(string $code): bool
    {
        $count = $this->getGiftVoucherRepository()->createQueryBuilder('gv')
            ->select('COUNT(gv.id)')
            ->where('gv.code = :code')->setParameter('code', $code)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher[]
     */
    public function getAllCreatedOnOrder(Order $order): array
    {
        return $this->getGiftVoucherRepository()->findBy(['createdOnOrder' => $order], ['id' => 'ASC']);
    }

    /**
     * @param int[] $giftVoucherIds
     * @return \Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher[]
     */
    public function getAllByIds(array $giftVoucherIds): array
    {
        return $this->getGiftVoucherRepository()->findBy(['id' => $giftVoucherIds]);
    }

    public function getQueryBuilderByDomainIdAndSearchText(
        int $domainId,
        ?string $searchText,
        ?string $normalizedCodeSearchText = null,
    ): QueryBuilder {
        $queryBuilder = $this->getGiftVoucherRepository()->createQueryBuilder('gv')
            ->where('gv.domainId = :domainId')->setParameter('domainId', $domainId)
            ->orderBy('gv.id', 'DESC');

        if ($searchText !== null && $searchText !== '') {
            $queryBuilder
                ->andWhere('LOWER(gv.code) LIKE LOWER(:codeSearchText) OR LOWER(gv.customerEmail) LIKE LOWER(:searchText)')
                ->setParameter('codeSearchText', '%' . ($normalizedCodeSearchText ?? $searchText) . '%')
                ->setParameter('searchText', '%' . $searchText . '%');
        }

        return $queryBuilder;
    }
}
