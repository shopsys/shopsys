<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Listing\OrderListAdminRepository;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

class OrderRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OrderListAdminRepository $orderListAdminRepository,
        protected readonly DatabaseSearchingHelper $databaseSearchingHelper,
    ) {
    }

    protected function getOrderRepository(): EntityRepository
    {
        return $this->em->getRepository(Order::class);
    }

    public function createOrderQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('o')
            ->from(Order::class, 'o')
            ->where('o.deleted = FALSE');
    }

    public function findById(int $id): ?Order
    {
        return $this->createOrderQueryBuilder()
            ->andWhere('o.id = :orderId')->setParameter(':orderId', $id)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    public function getById(int $id): Order
    {
        $order = $this->findById($id);

        if ($order === null) {
            throw new OrderNotFoundException('Order with ID ' . $id . ' not found.');
        }

        return $order;
    }

    /**
     * @param int[] $ids
     * @return array<int, \Shopsys\FrameworkBundle\Model\Order\Order>
     */
    public function findByIds(array $ids): array
    {
        return $this->getOrderRepository()
            ->createQueryBuilder('o', 'o.id')
            ->where('o.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getByUuid(string $uuid): Order
    {
        $order = $this->getOrderRepository()->findOneBy(['uuid' => $uuid]);

        if ($order === null) {
            throw new OrderNotFoundException('Order with UUID "' . $uuid . '" not found.');
        }

        return $order;
    }

    public function isOrderStatusUsed(OrderStatus $orderStatus): bool
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('o.id')
            ->from(Order::class, 'o')
            ->setMaxResults(1)
            ->where('o.status = :status')
            ->setParameter('status', $orderStatus->getId());

        return $queryBuilder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR) !== null;
    }

    public function getOrderListQueryBuilderByQuickSearchData(
        string $locale,
        QuickSearchFormData $quickSearchData,
    ): QueryBuilder {
        $queryBuilder = $this->orderListAdminRepository->getOrderListQueryBuilder($locale);

        if ($quickSearchData->text !== null && $quickSearchData->text !== '') {
            $queryBuilder
                ->leftJoin(CustomerUser::class, 'u', Join::WITH, 'o.customerUser = u.id');

            $uuidCondition = '';

            if (Uuid::isValid($quickSearchData->text)) {
                $uuidCondition = 'o.uuid = :exactText OR ';
                $queryBuilder->setParameter('exactText', $quickSearchData->text);
            }

            $queryBuilder->andWhere('
                    (
                        ' . $uuidCondition . '
                        o.number LIKE :text
                        OR
                        NORMALIZED(o.email) LIKE NORMALIZED(:text)
                        OR
                        NORMALIZED(o.lastName) LIKE NORMALIZED(:text)
                        OR
                        NORMALIZED(o.companyName) LIKE NORMALIZED(:text)
                        OR
                        NORMALIZED(u.email) LIKE NORMALIZED(:text)
                    )');
            $querySearchText = $this->databaseSearchingHelper->getFullTextLikeSearchString($quickSearchData->text);
            $queryBuilder->setParameter('text', $querySearchText);
        }

        return $queryBuilder;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getCustomerUserOrderList(CustomerUser $customerUser): array
    {
        return $this->createOrderQueryBuilder()
            ->select('o, oi, os, ost')
            ->join('o.items', 'oi')
            ->join('o.status', 'os')
            ->join('os.translations', 'ost')
            ->andWhere('o.customerUser = :customerUser')
            ->orderBy('o.createdAt', 'DESC')
            ->setParameter('customerUser', $customerUser)
            ->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getLastCustomerOrdersByLimit(Customer $customer, int $limit, string $locale): array
    {
        return $this->createOrderQueryBuilder()
            ->select('o, os, ost')
            ->join('o.status', 'os')
            ->join('os.translations', 'ost', Join::WITH, 'ost.locale = :locale')
            ->andWhere('o.customer = :customer')
            ->orderBy('o.createdAt', 'DESC')
            ->setParameter('customer', $customer)
            ->setParameter('locale', $locale)
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getOrderListForEmailByDomainId(string $email, int $domainId): array
    {
        return $this->getOrderListQueryBuilder()
            ->andWhere('o.domainId = :domain')
            ->andWhere('o.email = :email OR cu.email = :email')
            ->orderBy('o.createdAt', 'DESC')
            ->setParameter('email', $email)
            ->setParameter('domain', $domainId)
            ->getQuery()->getResult();
    }

    public function getByUrlHashAndDomain(string $urlHash, int $domainId): Order
    {
        $order = $this->createOrderQueryBuilder()
            ->andWhere('o.urlHash = :urlHash')->setParameter(':urlHash', $urlHash)
            ->andWhere('o.domainId = :domainId')->setParameter(':domainId', $domainId)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        if ($order === null) {
            throw new OrderNotFoundException(sprintf(
                'Order with urlHash "%s" was not found.',
                $urlHash,
            ));
        }

        return $order;
    }

    public function findByUrlHashIncludingDeletedOrders(string $urlHash): ?Order
    {
        return $this->getOrderRepository()->findOneBy(['urlHash' => $urlHash]);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency[]
     */
    public function getCurrenciesUsedInOrders(): array
    {
        return $this->em->createQueryBuilder()
            ->select('c')
            ->from(Currency::class, 'c')
            ->join(Order::class, 'o', Join::WITH, 'o.currencyCode = c.code')
            ->groupBy('c.id')
            ->getQuery()->getResult();
    }

    protected function getOrderListQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('o, oi, os, ost')
            ->from(Order::class, 'o')
            ->join('o.items', 'oi')
            ->join('o.status', 'os')
            ->join('os.translations', 'ost')
            ->leftJoin('o.customerUser', 'cu');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getAllWithoutTrackingNumberByTransportType(string $transportType): array
    {
        $queryBuilder = $this->createOrderQueryBuilder()
            ->join('o.items', 'oi', Join::WITH, 'oi.transport IS NOT NULL')
            ->join('oi.transport', 't')
            ->andWhere('o.trackingNumber IS NULL')
            ->andWhere('t.type = :transportType')
            ->setParameter('transportType', $transportType);

        return $queryBuilder->getQuery()->getResult();
    }
}
