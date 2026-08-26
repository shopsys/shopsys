<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataProviderInterface;

class ArticleDetailFriendlyUrlDataProvider implements FriendlyUrlDataProviderInterface
{
    protected const ROUTE_NAME = 'front_article_detail';

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
            ->select('a.id, a.name')
            ->distinct()
            ->from(Article::class, 'a')
            ->leftJoin(
                FriendlyUrl::class,
                'f',
                Join::WITH,
                'a.id = f.entityId AND f.routeName = :routeName AND f.domainId = a.domainId',
            )
            ->setParameter('routeName', static::ROUTE_NAME)
            ->where('f.entityId IS NULL AND a.domainId = :domainId')
            ->setParameter('domainId', $domainConfig->getId())
            ->andWhere('a.type = :type')
            ->setParameter('type', Article::TYPE_SITE);
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
