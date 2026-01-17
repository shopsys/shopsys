<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataProviderInterface;

class StoreFriendlyUrlProvider implements FriendlyUrlDataProviderInterface
{
    public const ROUTE_NAME = 'front_stores_detail';

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly FriendlyUrlDataFactory $friendlyUrlDataFactory,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData[]
     */
    #[Override]
    public function getFriendlyUrlData(DomainConfig $domainConfig): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('s.id, s.name')
            ->distinct()
            ->from(Store::class, 's')
            ->leftJoin(FriendlyUrl::class, 'f', Join::WITH, 's.id = f.entityId AND f.routeName = :routeName AND f.domainId = :domainId')
            ->where('f.entityId IS NULL')
            ->setParameter('routeName', static::ROUTE_NAME)
            ->setParameter('domainId', $domainConfig->getId());
        $scalarData = $queryBuilder->getQuery()->getScalarResult();

        $friendlyUrlsData = [];

        foreach ($scalarData as $data) {
            $friendlyUrlData = $this->friendlyUrlDataFactory->createFromIdAndName($data['id'], $data['name']);
            $friendlyUrlsData[] = $friendlyUrlData;
        }

        return $friendlyUrlsData;
    }

    #[Override]
    public function getRouteName(): string
    {
        return self::ROUTE_NAME;
    }
}
