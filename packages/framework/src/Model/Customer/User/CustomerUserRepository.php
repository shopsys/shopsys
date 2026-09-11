<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use DateTimeInterface;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundByEmailAndDomainException;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

class CustomerUserRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
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
     * @param int[] $salesRepresentativeIds
     * @return array<int, array{count: int, emails: string[]}>
     */
    public function getCustomerUserEmailsSummaryIndexedBySalesRepresentativeId(
        array $salesRepresentativeIds,
        int $emailsLimit,
    ): array {
        $summariesIndexedBySalesRepresentativeId = array_fill_keys($salesRepresentativeIds, ['count' => 0, 'emails' => []]);

        if ($salesRepresentativeIds === []) {
            return $summariesIndexedBySalesRepresentativeId;
        }

        $rows = $this->em->getConnection()->fetchAllAssociative(
            'SELECT ranked_customer_users.sales_representative_id, ranked_customer_users.email, ranked_customer_users.customer_users_count
            FROM (
                SELECT
                    cu.sales_representative_id,
                    cu.email,
                    COUNT(*) OVER (PARTITION BY cu.sales_representative_id) AS customer_users_count,
                    ROW_NUMBER() OVER (PARTITION BY cu.sales_representative_id ORDER BY cu.email) AS email_position
                FROM customer_users cu
                WHERE cu.sales_representative_id IN (:salesRepresentativeIds)
            ) ranked_customer_users
            WHERE ranked_customer_users.email_position <= :emailsLimit
            ORDER BY ranked_customer_users.email_position',
            [
                'salesRepresentativeIds' => $salesRepresentativeIds,
                'emailsLimit' => $emailsLimit,
            ],
            [
                'salesRepresentativeIds' => ArrayParameterType::INTEGER,
            ],
        );

        foreach ($rows as $row) {
            $salesRepresentativeId = (int)$row['sales_representative_id'];
            $summariesIndexedBySalesRepresentativeId[$salesRepresentativeId]['count'] = (int)$row['customer_users_count'];
            $summariesIndexedBySalesRepresentativeId[$salesRepresentativeId]['emails'][] = $row['email'];
        }

        return $summariesIndexedBySalesRepresentativeId;
    }
}
