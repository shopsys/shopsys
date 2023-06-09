<?php

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataFactoryInterface;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataProviderInterface;
use Shopsys\FrameworkBundle\Model\Category\Category;

class ProductListFriendlyUrlDataProvider implements FriendlyUrlDataProviderInterface
{
    protected const ROUTE_NAME = 'front_product_list';

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataFactoryInterface $friendlyUrlDataFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly FriendlyUrlDataFactoryInterface $friendlyUrlDataFactory
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData[]
     */
    public function getFriendlyUrlData(DomainConfig $domainConfig): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('c.id, ct.name')
            ->distinct()
            ->from(Category::class, 'c')
            ->join('c.translations', 'ct', Join::WITH, 'ct.locale = :locale')
            ->setParameter('locale', $domainConfig->getLocale())
            ->leftJoin(
                FriendlyUrl::class,
                'f',
                Join::WITH,
                'c.id = f.entityId AND f.routeName = :routeName AND f.domainId = :domainId'
            )
            ->setParameter('routeName', static::ROUTE_NAME)
            ->setParameter('domainId', $domainConfig->getId())
            ->where('f.entityId IS NULL AND ct.name IS NOT NULL');

        $scalarData = $queryBuilder->getQuery()->getScalarResult();

        $friendlyUrlsData = [];

        foreach ($scalarData as $data) {
            $friendlyUrlsData[] = $this->friendlyUrlDataFactory->createFromIdAndName($data['id'], $data['name']);
        }

        return $friendlyUrlsData;
    }

    /**
     * @return string
     */
    public function getRouteName(): string
    {
        return static::ROUTE_NAME;
    }
}
