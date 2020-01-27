<?php

declare(strict_types=1);

namespace App\Model\Product\Series;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataFactoryInterface;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataProviderInterface;

class ProductSeriesFriendlyUrlProvider implements FriendlyUrlDataProviderInterface
{
    public const ROUTE_NAME = 'front_productseries_detail';

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
    public function __construct(EntityManagerInterface $em, FriendlyUrlDataFactoryInterface $friendlyUrlDataFactory)
    {
        $this->em = $em;
        $this->friendlyUrlDataFactory = $friendlyUrlDataFactory;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return array
     */
    public function getFriendlyUrlData(DomainConfig $domainConfig): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('ps')
            ->distinct()
            ->from(ProductSeries::class, 'ps')
            ->join(ProductSeriesDomain::class, 'psd', Join::WITH, 'ps.id = psd.productSeries')
            ->leftJoin(FriendlyUrl::class, 'f', Join::WITH, 'ps.id = f.entityId AND f.routeName = :routeName AND f.domainId = psd.domainId')
            ->setParameter('routeName', static::ROUTE_NAME)
            ->where('f.entityId IS NULL AND psd.domainId = :domainId')
            ->setParameter('domainId', $domainConfig->getId());
        $scalarData = $queryBuilder->getQuery()->getScalarResult();

        $friendlyUrlsData = [];

        foreach ($scalarData as $data) {
            $friendlyUrlsData[] = $this->friendlyUrlDataFactory->createFromIdAndName($data['id'], $data['seoH1']);
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
}
