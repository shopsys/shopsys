<?php

namespace Shopsys\FrameworkBundle\Model\Order;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Customer\User;
use Shopsys\FrameworkBundle\Model\Order\Listing\OrderListAdminRepository;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

class OrderRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Listing\OrderListAdminRepository
     */
    protected $orderListAdminRepository;

    public function __construct(
        EntityManagerInterface $em,
        OrderListAdminRepository $orderListAdminRepository
    ) {
        $this->em = $em;
        $this->orderListAdminRepository = $orderListAdminRepository;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getOrderRepository()
    {
        return $this->em->getRepository(Order::class);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createOrderQueryBuilder()
    {
        return $this->em->createQueryBuilder()
            ->select('o')
            ->from(Order::class, 'o')
            ->where('o.deleted = FALSE');
    }

    /**
     * @param int $userId
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getOrdersByUserId($userId)
    {
        return $this->createOrderQueryBuilder()
            ->andWhere('o.customer = :customer')->setParameter(':customer', $userId)
            ->getQuery()->getResult();
    }

    /**
     * @param int $userId
     * @return \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    public function findLastByUserId($userId)
    {
        return $this->createOrderQueryBuilder()
            ->andWhere('o.customer = :customer')->setParameter(':customer', $userId)
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    public function findById($id)
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
    public function getById($id)
    {
        $order = $this->findById($id);

        if ($order === null) {
            throw new \Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException('Order with ID ' . $id . ' not found.');
        }

        return $order;
    }

    /**
     * @return bool
     */
    public function isOrderStatusUsed(OrderStatus $orderStatus)
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
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrderListQueryBuilderByQuickSearchData(
        $locale,
        QuickSearchFormData $quickSearchData
    ) {
        $queryBuilder = $this->orderListAdminRepository->getOrderListQueryBuilder($locale);

        if ($quickSearchData->text !== null && $quickSearchData->text !== '') {
            $queryBuilder
                ->leftJoin(User::class, 'u', Join::WITH, 'o.customer = u.id')
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
     * @param \Shopsys\FrameworkBundle\Model\Customer\User
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getCustomerOrderList(User $user)
    {
        return $this->createOrderQueryBuilder()
            ->select('o, oi, os, ost, c')
            ->join('o.items', 'oi')
            ->join('o.status', 'os')
            ->join('os.translations', 'ost')
            ->join('o.currency', 'c')
            ->andWhere('o.customer = :customer')
            ->orderBy('o.createdAt', 'DESC')
            ->setParameter('customer', $user)
            ->getQuery()->execute();
    }

    /**
     * @param string $email
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getOrderListForEmailByDomainId($email, $domainId)
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
    public function getByUrlHashAndDomain($urlHash, $domainId)
    {
        $order = $this->createOrderQueryBuilder()
            ->andWhere('o.urlHash = :urlHash')->setParameter(':urlHash', $urlHash)
            ->andWhere('o.domainId = :domainId')->setParameter(':domainId', $domainId)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        if ($order === null) {
            throw new \Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException();
        }

        return $order;
    }

    /**
     * @param string $orderNumber
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getByOrderNumberAndUser($orderNumber, User $user)
    {
        $order = $this->createOrderQueryBuilder()
            ->andWhere('o.number = :number')->setParameter(':number', $orderNumber)
            ->andWhere('o.customer = :customer')->setParameter(':customer', $user)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        if ($order === null) {
            $message = 'Order with number "' . $orderNumber . '" and userId "' . $user->getId() . '" not found.';
            throw new \Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException($message);
        }

        return $order;
    }

    /**
     * @param string $urlHash
     * @return \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    public function findByUrlHashIncludingDeletedOrders($urlHash)
    {
        return $this->getOrderRepository()->findOneBy(['urlHash' => $urlHash]);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency[]
     */
    public function getCurrenciesUsedInOrders()
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
    public function getOrdersCountByEmailAndDomainId($email, $domainId)
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
    protected function getOrderListQueryBuilder()
    {
        return $this->em->createQueryBuilder()
            ->select('o, oi, os, ost, c')
            ->from(Order::class, 'o')
            ->join('o.items', 'oi')
            ->join('o.status', 'os')
            ->join('os.translations', 'ost')
            ->join('o.currency', 'c')
            ->leftJoin('o.customer', 'cu');
    }
}
