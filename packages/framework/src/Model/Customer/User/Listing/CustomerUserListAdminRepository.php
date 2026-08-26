<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Listing;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddress;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneNumberSearchHelper;

class CustomerUserListAdminRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly DatabaseSearchingHelper $databaseSearchingHelper,
    ) {
    }

    public function getCustomerUserListQueryBuilder(int $domainId): QueryBuilder
    {
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

        $phoneExpr = PhoneNumberSearchHelper::getDqlExpression('cu');

        return $this->em->createQueryBuilder()
            ->select('
                ba.id AS billingAddressId,
                c.id AS customerId,
                cu.id AS id,
                CASE WHEN ba.companyCustomer = true THEN \'\' ELSE cu.email END as email,
                CASE WHEN ba.companyCustomer = true THEN \'\' ELSE ' . $phoneExpr . ' END as telephone,
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
    }

    public function getCustomerUserListQueryBuilderByQuickSearchData(
        int $domainId,
        QuickSearchFormData $quickSearchData,
    ): QueryBuilder {
        $queryBuilder = $this->getCustomerUserListQueryBuilder($domainId);

        if ($quickSearchData->text !== null && $quickSearchData->text !== '') {
            $uuidCondition = '';

            if (Uuid::isValid($quickSearchData->text)) {
                $uuidCondition = 'cu.uuid = :exactText OR ';
                $queryBuilder->setParameter('exactText', $quickSearchData->text);
            }

            $phoneExpr = PhoneNumberSearchHelper::getDqlExpression('cu');

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
                        NORMALIZED(' . $phoneExpr . ') LIKE NORMALIZED(:text)
                    )');
            $querySearchText = $this->databaseSearchingHelper->getFullTextLikeSearchString($quickSearchData->text);
            $queryBuilder->setParameter('text', $querySearchText);
        }

        return $queryBuilder;
    }
}
