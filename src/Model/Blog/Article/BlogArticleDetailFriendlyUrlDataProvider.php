<?php

declare(strict_types=1);

namespace App\Model\Blog\Article;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataFactoryInterface;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataProviderInterface;

class BlogArticleDetailFriendlyUrlDataProvider implements FriendlyUrlDataProviderInterface
{
    private const ROUTE_NAME = 'front_blogarticle_detail';

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataFactoryInterface
     */
    private $friendlyUrlDataFactory;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataFactoryInterface $friendlyUrlDataFactory
     */
    public function __construct(
        EntityManagerInterface $em,
        FriendlyUrlDataFactoryInterface $friendlyUrlDataFactory
    ) {
        $this->em = $em;
        $this->friendlyUrlDataFactory = $friendlyUrlDataFactory;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData[]
     */
    public function getFriendlyUrlData(DomainConfig $domainConfig): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('ba.id, bat.name')
            ->distinct()
            ->from(BlogArticle::class, 'ba')
            ->join('ba.translations', 'bat', Join::WITH, 'bat.locale = :locale')
            ->setParameter('locale', $domainConfig->getId())
            ->leftJoin(FriendlyUrl::class, 'f', Join::WITH, 'ba.id = f.entityId AND f.routeName = :routeName AND f.domainId = :domainId')
            ->setParameter('routeName', self::ROUTE_NAME)
            ->setParameter('domainId', $domainConfig->getId())
            ->andWhere('f.entityId IS NULL');

        $scalarData = $queryBuilder->getQuery()->getScalarResult();

        $friendlyUrlsData = [];

        foreach ($scalarData as $data) {
            $friendlyUrlsData[] = $this->createFromIdAndName($data['id'], $data['name']);
        }

        return $friendlyUrlsData;
    }

    /**
     * @return string
     */
    public function getRouteName(): string
    {
        return self::ROUTE_NAME;
    }

    /**
     * @param int $id
     * @param string $name
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData
     */
    public function createFromIdAndName(int $id, string $name): FriendlyUrlData
    {
        $friendlyUrlData = $this->friendlyUrlDataFactory->create();
        $friendlyUrlData->id = $id;
        $friendlyUrlData->name = $name;

        return $friendlyUrlData;
    }
}
