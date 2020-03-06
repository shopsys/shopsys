<?php

declare(strict_types=1);

namespace App\Model\Product\Series\Category;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataFactoryInterface;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataProviderInterface;

class ProductSeriesCategoryFriendlyUrlProvider implements FriendlyUrlDataProviderInterface
{
    public const ROUTE_NAME = 'front_productseriescategory_detail';

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
            ->select('IDENTITY(psct.translatable) as id, psct.name')
            ->distinct()
            ->from(ProductSeriesCategoryTranslation::class, 'psct')
            ->leftJoin(FriendlyUrl::class, 'f', Join::WITH, 'IDENTITY(psct.translatable) = f.entityId AND f.routeName = :routeName')
            ->setParameter('routeName', static::ROUTE_NAME)
            ->where('f.entityId IS NULL AND f.domainId = :domainId')
            ->andWhere('psct.locale = :locale')
            ->setParameter('domainId', $domainConfig->getId())
            ->setParameter('locale', $domainConfig->getLocale());
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
        return self::ROUTE_NAME;
    }
}
