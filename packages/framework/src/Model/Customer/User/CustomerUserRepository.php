<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddress;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundByEmailAndDomainException;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

class CustomerUserRepository
{
    protected EntityManagerInterface $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getCustomerUserRepository()
    {
        return $this->em->getRepository(CustomerUser::class);
    }

    /**
     * @param string $email
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    public function findCustomerUserByEmailAndDomain($email, $domainId)
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
    public function getCustomerUserByEmailAndDomain($email, $domainId)
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
    public function getCustomerUserById($id)
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
    public function findById($id)
    {
        return $this->getCustomerUserRepository()->find($id);
    }

    /**
     * @param int $id
     * @param string $loginToken
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    public function findByIdAndLoginToken($id, $loginToken)
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
        $domainId,
        QuickSearchFormData $quickSearchData,
    ) {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('
                u.id,
                u.email,
                u.telephone,
                u.domainId,
                MAX(pg.name) AS pricingGroup,
                MAX(ba.city) city,
                MAX(CASE WHEN ba.companyCustomer = true
                        THEN ba.companyName
                        ELSE CONCAT(u.lastName, \' \', u.firstName)
                    END) AS name,
                COUNT(o.id) ordersCount,
                SUM(o.totalPriceWithVat) ordersSumPrice,
                MAX(o.createdAt) lastOrderAt')
            ->from(CustomerUser::class, 'u')
            ->where('u.domainId = :selectedDomainId')
            ->setParameter('selectedDomainId', $domainId)
            ->join('u.customer', 'c')
            ->leftJoin(BillingAddress::class, 'ba', 'WITH', 'c.id = ba.customer')
            ->leftJoin(Order::class, 'o', 'WITH', 'o.customerUser = u.id AND o.deleted = :deleted')
            ->setParameter('deleted', false)
            ->leftJoin(PricingGroup::class, 'pg', 'WITH', 'pg.id = u.pricingGroup')
            ->groupBy('u.id');

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
            $querySearchText = DatabaseSearching::getFullTextLikeSearchString($quickSearchData->text);
            $queryBuilder->setParameter('text', $querySearchText);
        }

        return $queryBuilder;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $oldPricingGroup
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $newPricingGroup
     */
    public function replaceCustomerUsersPricingGroup(PricingGroup $oldPricingGroup, PricingGroup $newPricingGroup)
    {
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
}
