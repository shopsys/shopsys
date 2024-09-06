<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Category;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataFactoryInterface;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataProviderInterface;

class BlogCategoryDetailFriendlyUrlDataProvider implements FriendlyUrlDataProviderInterface
{
    protected const ROUTE_NAME = 'front_blogcategory_detail';

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataFactory $friendlyUrlDataFactory
     */
    public function __construct(
        protected EntityManagerInterface $em,
        protected FriendlyUrlDataFactoryInterface $friendlyUrlDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData[]
     */
    public function getFriendlyUrlData(DomainConfig $domainConfig): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('bc.id, bct.name')
            ->distinct()
            ->from(BlogCategory::class, 'bc')
            ->join('bc.translations', 'bct', Join::WITH, 'bct.locale = :locale')
            ->setParameter('locale', $domainConfig->getId())
            ->leftJoin(FriendlyUrl::class, 'f', Join::WITH, 'bc.id = f.entityId AND f.routeName = :routeName AND f.domainId = :domainId')
            ->setParameter('routeName', static::ROUTE_NAME)
            ->setParameter('domainId', $domainConfig->getId())
            ->where('f.entityId IS NULL');

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
