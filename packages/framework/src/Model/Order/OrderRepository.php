<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Listing\OrderListAdminRepository;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

class OrderRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Order\Listing\OrderListAdminRepository $orderListAdminRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OrderListAdminRepository $orderListAdminRepository,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getOrderRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(Order::class);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createOrderQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('o')
            ->from(Order::class, 'o')
            ->where('o.deleted = FALSE');
    }

    /**
     * @param int $customerUserId
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getOrdersByCustomerUserId($customerUserId): array
    {
        return $this->createOrderQueryBuilder()
            ->andWhere('o.customerUser = :user')->setParameter(':user', $customerUserId)
            ->getQuery()->getResult();
    }

    /**
     * @param int $customerUserId
     * @return \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    public function findLastByCustomerUserId($customerUserId): ?\Shopsys\FrameworkBundle\Model\Order\Order
    {
        return $this->createOrderQueryBuilder()
            ->andWhere('o.customerUser = :user')->setParameter(':user', $customerUserId)
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    public function findById($id): ?\Shopsys\FrameworkBundle\Model\Order\Order
    {
        return $this->createOrderQueryBuilder()
            ->andWhere('o.id = :orderId')->setParameter(':orderId', $id)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getById($id): \Shopsys\FrameworkBundle\Model\Order\Order
    {
        $order = $this->findById($id);

        if ($order === null) {
            throw new OrderNotFoundException('Order with ID ' . $id . ' not found.');
        }

        return $order;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus $orderStatus
     * @return bool
     */
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

    /**
     * @param string $locale
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchData
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrderListQueryBuilderByQuickSearchData(
        $locale,
        QuickSearchFormData $quickSearchData,
    ): \Doctrine\ORM\QueryBuilder {
        $queryBuilder = $this->orderListAdminRepository->getOrderListQueryBuilder($locale);

        if ($quickSearchData->text !== null && $quickSearchData->text !== '') {
            $queryBuilder
                ->leftJoin(CustomerUser::class, 'u', Join::WITH, 'o.customerUser = u.id')
                ->andWhere('
                    (
                        o.number LIKE :text
                        OR
                        NORMALIZE(o.email) LIKE NORMALIZE(:text)
                        OR
                        NORMALIZE(o.lastName) LIKE NORMALIZE(:text)
                        OR
                        NORMALIZE(o.companyName) LIKE NORMALIZE(:text)
                        OR
                        NORMALIZE(u.email) LIKE NORMALIZE(:text)
                    )');
            $querySearchText = DatabaseSearching::getFullTextLikeSearchString($quickSearchData->text);
            $queryBuilder->setParameter('text', $querySearchText);
        }

        return $queryBuilder;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getCustomerUserOrderList(CustomerUser $customerUser): array
    {
        return $this->createOrderQueryBuilder()
            ->select('o, oi, os, ost, c')
            ->join('o.items', 'oi')
            ->join('o.status', 'os')
            ->join('os.translations', 'ost')
            ->join('o.currency', 'c')
            ->andWhere('o.customerUser = :customerUser')
            ->orderBy('o.createdAt', 'DESC')
            ->setParameter('customerUser', $customerUser)
            ->getQuery()->execute();
    }

    /**
     * @param string $email
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getOrderListForEmailByDomainId($email, $domainId): array
    {
        return $this->getOrderListQueryBuilder()
            ->andWhere('o.domainId = :domain')
            ->andWhere('o.email = :email OR cu.email = :email')
            ->orderBy('o.createdAt', 'DESC')
            ->setParameter('email', $email)
            ->setParameter('domain', $domainId)
            ->getQuery()->execute();
    }

    /**
     * @param string $urlHash
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getByUrlHashAndDomain($urlHash, $domainId): \Shopsys\FrameworkBundle\Model\Order\Order
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

    /**
     * @param string $orderNumber
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getByOrderNumberAndCustomerUser($orderNumber, CustomerUser $customerUser): \Shopsys\FrameworkBundle\Model\Order\Order
    {
        $order = $this->createOrderQueryBuilder()
            ->andWhere('o.number = :number')->setParameter(':number', $orderNumber)
            ->andWhere('o.customerUser = :customerUser')->setParameter(':customerUser', $customerUser)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        if ($order === null) {
            $message = 'Order with number "' . $orderNumber . '" and customerUserId "' . $customerUser->getId() . '" not found.';

            throw new OrderNotFoundException($message);
        }

        return $order;
    }

    /**
     * @param string $urlHash
     * @return \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    public function findByUrlHashIncludingDeletedOrders($urlHash): ?\Shopsys\FrameworkBundle\Model\Order\Order
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
            ->join(Order::class, 'o', Join::WITH, 'o.currency = c.id')
            ->groupBy('c')
            ->getQuery()->execute();
    }

    /**
     * @param string $email
     * @param int $domainId
     * @return int
     */
    public function getOrdersCountByEmailAndDomainId($email, $domainId): int
    {
        return $this->getOrderListQueryBuilder()
            ->select('count(o)')
            ->andWhere('o.domainId = :domain')
            ->andWhere('o.email = :email OR cu.email = :email')
            ->setParameter('email', $email)
            ->setParameter('domain', $domainId)
            ->getQuery()->getSingleScalarResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getOrderListQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('o, oi, os, ost, c')
            ->from(Order::class, 'o')
            ->join('o.items', 'oi')
            ->join('o.status', 'os')
            ->join('os.translations', 'ost')
            ->join('o.currency', 'c')
            ->leftJoin('o.customerUser', 'cu');
    }
}
