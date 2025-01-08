<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use DateTimeInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddress;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundByEmailAndDomainException;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

class CustomerUserRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper $databaseSearchingHelper
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly DatabaseSearchingHelper $databaseSearchingHelper,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getCustomerUserRepository(): EntityRepository
    {
        return $this->em->getRepository(CustomerUser::class);
    }

    /**
     * @param string $email
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    public function findCustomerUserByEmailAndDomain(string $email, int $domainId): ?CustomerUser
    {
        return $this->getCustomerUserRepository()->findOneBy([
            'email' => mb_strtolower($email),
            'domainId' => $domainId,
        ]);
    }

    /**
     * @param string $email
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
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

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function getCustomerUserById(int $id): CustomerUser
    {
        $customerUser = $this->findById($id);

        if ($customerUser === null) {
            throw new CustomerUserNotFoundException((string)$id);
        }

        return $customerUser;
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    public function findById(int $id): ?CustomerUser
    {
        return $this->getCustomerUserRepository()->find($id);
    }

    /**
     * @param int $id
     * @param string $loginToken
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    public function findByIdAndLoginToken(int $id, string $loginToken): ?CustomerUser
    {
        return $this->getCustomerUserRepository()->findOneBy([
            'id' => $id,
            'loginToken' => $loginToken,
        ]);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchData
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCustomerUserListQueryBuilderByQuickSearchData(
        int $domainId,
        QuickSearchFormData $quickSearchData,
    ): QueryBuilder {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('
                MAX(ba.id) AS billingAddressId,
                MAX(c.id) AS customerId,
                MAX(u.id) AS id,
                MAX(CASE WHEN ba.companyCustomer = true
                        THEN \'\'
                        ELSE u.email
                    END) email,
                MAX(CASE WHEN ba.companyCustomer = true
                        THEN \'\'
                        ELSE u.telephone
                    END) telephone,
                BOOL_AND(ba.companyCustomer) AS isCompanyCustomer,
                MAX(u.domainId) domainId,
                MAX(CASE WHEN ba.companyCustomer = true
                        THEN \'\'
                        ELSE pg.name
                    END) pricingGroup,
                MAX(ba.city) city,
                MAX(CASE WHEN ba.companyCustomer = true
                        THEN ba.companyName
                        ELSE CONCAT(u.lastName, \' \', u.firstName)
                    END) AS name,
                COUNT(o.id) ordersCount,
                SUM(o.totalPriceWithVat) ordersSumPrice,
                MAX(o.createdAt) lastOrderAt,
                BOOL_AND(ba.activated) as isActivated')
            ->from(CustomerUser::class, 'u')
            ->where('u.domainId = :selectedDomainId')
            ->setParameter('selectedDomainId', $domainId)
            ->join('u.customer', 'c')
            ->leftJoin(BillingAddress::class, 'ba', 'WITH', 'c.id = ba.customer')
            ->leftJoin(Order::class, 'o', 'WITH', 'o.customerUser = u.id AND o.deleted = :deleted')
            ->setParameter('deleted', false)
            ->leftJoin(PricingGroup::class, 'pg', 'WITH', 'pg.id = u.pricingGroup')
            ->groupBy('c.id');

        if ($quickSearchData->text !== null && $quickSearchData->text !== '') {
            $queryBuilder
                ->andWhere('
                    (
                        NORMALIZED(u.lastName) LIKE NORMALIZED(:text)
                        OR
                        NORMALIZED(u.email) LIKE NORMALIZED(:text)
                        OR
                        NORMALIZED(ba.companyName) LIKE NORMALIZED(:text)
                        OR
                        NORMALIZED(u.telephone) LIKE :text
                    )');
            $querySearchText = $this->databaseSearchingHelper->getFullTextLikeSearchString($quickSearchData->text);
            $queryBuilder->setParameter('text', $querySearchText);
        }

        return $queryBuilder;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $oldPricingGroup
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $newPricingGroup
     */
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

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
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

    /**
     * @param string $customerUserUuid
     * @param \DateTimeInterface $referenceDateTime
     * @return bool
     */
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
     * @param int $salesRepresentativeId
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
