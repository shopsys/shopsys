<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataProviderInterface;

class FlagDetailFriendlyUrlDataProvider implements FriendlyUrlDataProviderInterface
{
    protected const string ROUTE_NAME = 'front_flag_detail';

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
            ->select('f.id, ft.name')
            ->distinct()
            ->from(Flag::class, 'f')
            ->join('f.translations', 'ft', Join::WITH, 'ft.locale = :locale')
            ->setParameter('locale', $domainConfig->getLocale())
            ->leftJoin(FriendlyUrl::class, 'fu', Join::WITH, 'f.id = fu.entityId AND fu.routeName = :routeName AND fu.domainId = :domainId')
            ->setParameter('routeName', self::ROUTE_NAME)
            ->setParameter('domainId', $domainConfig->getId())
            ->andWhere('fu.entityId IS NULL');

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
        return self::ROUTE_NAME;
    }
}
