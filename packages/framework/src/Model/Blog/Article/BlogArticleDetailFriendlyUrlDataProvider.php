<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataProviderInterface;

class BlogArticleDetailFriendlyUrlDataProvider implements FriendlyUrlDataProviderInterface
{
    protected const ROUTE_NAME = 'front_blogarticle_detail';

    public function __construct(
        protected EntityManagerInterface $em,
        protected FriendlyUrlDataFactory $friendlyUrlDataFactory,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData[]
     */
    #[Override]
    public function getFriendlyUrlData(DomainConfig $domainConfig): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('ba.id, bat.name')
            ->distinct()
            ->from(BlogArticle::class, 'ba')
            ->join('ba.translations', 'bat', Join::WITH, 'bat.locale = :locale')
            ->setParameter('locale', $domainConfig->getLocale())
            ->leftJoin(FriendlyUrl::class, 'f', Join::WITH, 'ba.id = f.entityId AND f.routeName = :routeName AND f.domainId = :domainId')
            ->setParameter('routeName', static::ROUTE_NAME)
            ->setParameter('domainId', $domainConfig->getId())
            ->andWhere('f.entityId IS NULL')
            ->andWhere('bat.name IS NOT NULL');

        $scalarData = $queryBuilder->getQuery()->getScalarResult();

        $friendlyUrlsData = [];

        foreach ($scalarData as $data) {
            $friendlyUrlsData[] = $this->friendlyUrlDataFactory->createFromIdAndName($data['id'], $data['name']);
        }

        return $friendlyUrlsData;
    }

    #[Override]
    public function getRouteName(): string
    {
        return static::ROUTE_NAME;
    }
}
