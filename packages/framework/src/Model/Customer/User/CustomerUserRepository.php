<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use DateTimeInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddress;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundByEmailAndDomainException;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

class CustomerUserRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly DatabaseSearchingHelper $databaseSearchingHelper,
    ) {
    }

    protected function getCustomerUserRepository(): EntityRepository
    {
        return $this->em->getRepository(CustomerUser::class);
    }

    public function findCustomerUserByEmailAndDomain(string $email, int $domainId): ?CustomerUser
    {
        return $this->getCustomerUserRepository()->findOneBy([
            'email' => mb_strtolower($email),
            'domainId' => $domainId,
        ]);
    }

    public function getCustomerUserByEmailAndDomain(string $email, int $domainId): ?CustomerUser
    {
        $customerUser = $this->findCustomerUserByEmailAndDomain($email, $domainId);

        if ($customerUser === null) {
            throw new CustomerUserNotFoundByEmailAndDomainException(
                $email,
                $domainId,
            );
        }

        return $customerUser;
    }

    public function getCustomerUserById(int $id): CustomerUser
    {
        $customerUser = $this->findById($id);

        if ($customerUser === null) {
            throw new CustomerUserNotFoundException((string)$id);
        }

        return $customerUser;
    }

    public function findById(int $id): ?CustomerUser
    {
        return $this->getCustomerUserRepository()->find($id);
    }

    public function findByIdAndLoginToken(int $id, string $loginToken): ?CustomerUser
    {
        return $this->getCustomerUserRepository()->findOneBy([
            'id' => $id,
            'loginToken' => $loginToken,
        ]);
    }

    public function getCustomerUserListQueryBuilderByQuickSearchData(
        int $domainId,
        QuickSearchFormData $quickSearchData,
    ): QueryBuilder {
        $ordersCountSubquery = $this->em->createQueryBuilder()
            ->select('COUNT(o1.id)')
            ->from(Order::class, 'o1')
            ->where('o1.customer = c.id AND o1.deleted = false');

        $ordersSumSubquery = $this->em->createQueryBuilder()
            ->select('COALESCE(SUM(o2.totalPriceWithVat), 0)')
            ->from(Order::class, 'o2')
            ->where('o2.customer = c.id AND o2.deleted = false');

        $lastOrderSubquery = $this->em->createQueryBuilder()
            ->select('MAX(o3.createdAt)')
            ->from(Order::class, 'o3')
            ->where('o3.customer = c.id AND o3.deleted = false');

        $queryBuilder = $this->em->createQueryBuilder()
            ->select('
                ba.id AS billingAddressId,
                c.id AS customerId,
                cu.id AS id,
                CASE WHEN ba.companyCustomer = true THEN \'\' ELSE cu.email END as email,
                CASE WHEN ba.companyCustomer = true THEN \'\' ELSE cu.telephone END as telephone,
                ba.companyCustomer AS isCompanyCustomer,
                cu.domainId as domainId,
                CASE WHEN ba.companyCustomer = true THEN \'\' ELSE pg.name END as pricingGroup,
                ba.city as city,
                CASE WHEN ba.companyCustomer = true THEN ba.companyName ELSE CONCAT(cu.lastName, \' \', cu.firstName) END AS name,
                ba.activated as isActivated,
                (' . $ordersCountSubquery->getDQL() . ') as ordersCount,
                (' . $ordersSumSubquery->getDQL() . ') as ordersSumPrice,
                (' . $lastOrderSubquery->getDQL() . ') as lastOrderAt')
            ->from(Customer::class, 'c')
            ->join(CustomerUser::class, 'cu', Join::WITH, 'cu.customer = c.id AND cu.domainId = :selectedDomainId AND NOT EXISTS (
                SELECT 1 FROM ' . CustomerUser::class . ' cu2 
                WHERE cu2.customer = c.id AND cu2.domainId = :selectedDomainId AND cu2.id < cu.id
            )')
            ->leftJoin('cu.pricingGroup', 'pg')
            ->leftJoin(BillingAddress::class, 'ba', Join::WITH, 'ba.customer = c.id AND NOT EXISTS (
                SELECT 1 FROM ' . BillingAddress::class . ' ba2 
                WHERE ba2.customer = c.id AND ba2.id < ba.id
            )')
            ->where('cu.id IS NOT NULL')
            ->setParameter('selectedDomainId', $domainId);

        if ($quickSearchData->text !== null && $quickSearchData->text !== '') {
            $uuidCondition = '';

            if (Uuid::isValid($quickSearchData->text)) {
                $uuidCondition = 'cu.uuid = :exactText OR ';
                $queryBuilder->setParameter('exactText', $quickSearchData->text);
            }

            $queryBuilder
                ->andWhere('
                    (
                        ' . $uuidCondition . '
                        NORMALIZED(cu.lastName) LIKE NORMALIZED(:text)
                        OR
                        NORMALIZED(cu.email) LIKE NORMALIZED(:text)
                        OR
                        NORMALIZED(ba.companyName) LIKE NORMALIZED(:text)
                        OR
                        NORMALIZED(cu.telephone) LIKE :text
                    )');
            $querySearchText = $this->databaseSearchingHelper->getFullTextLikeSearchString($quickSearchData->text);
            $queryBuilder->setParameter('text', $querySearchText);
        }

        return $queryBuilder;
    }

    public function replaceCustomerUsersPricingGroup(
        PricingGroup $oldPricingGroup,
        PricingGroup $newPricingGroup,
    ): void {
        $this->em->createQueryBuilder()
            ->update(CustomerUser::class, 'u')
            ->set('u.pricingGroup', ':newPricingGroup')->setParameter('newPricingGroup', $newPricingGroup)
            ->where('u.pricingGroup = :oldPricingGroup')->setParameter('oldPricingGroup', $oldPricingGroup)
            ->getQuery()->execute();
    }

    public function getOneByUuid(string $uuid): CustomerUser
    {
        /** @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->getCustomerUserRepository()->findOneBy(['uuid' => $uuid]);

        if ($customerUser === null) {
            throw new CustomerUserNotFoundException('Customer with UUID ' . $uuid . ' does not exist.');
        }

        return $customerUser;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser[]
     */
    public function getAll(): array
    {
        return $this->getCustomerUserRepository()->findAll();
    }

    public function isLastSecurityChangeOlderThan(string $customerUserUuid, DateTimeInterface $referenceDateTime): bool
    {
        $lastSecurityChange = $this->em->createQueryBuilder()
            ->select('u.lastSecurityChange')
            ->from(CustomerUser::class, 'u')
            ->where('u.uuid = :uuid')
            ->setParameter('uuid', $customerUserUuid)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        if ($lastSecurityChange === null) {
            return false;
        }

        $lastSecurityChangeDateTime = $lastSecurityChange['lastSecurityChange'];

        if ($lastSecurityChangeDateTime === null) {
            return true;
        }

        return $lastSecurityChangeDateTime < $referenceDateTime;
    }

    /**
     * @return string[]
     */
    public function findEmailsOfCustomerUsersUsingSalesRepresentative(int $salesRepresentativeId): array
    {
        $customers = $this->getCustomerUserRepository()
            ->createQueryBuilder('c')
            ->select('c')
            ->where('c.salesRepresentative = :salesRepresentativeId')
            ->setParameter('salesRepresentativeId', $salesRepresentativeId)
            ->getQuery()
            ->getArrayResult();

        return array_map(function ($item) {
            return $item['email'];
        }, $customers);
    }
}
