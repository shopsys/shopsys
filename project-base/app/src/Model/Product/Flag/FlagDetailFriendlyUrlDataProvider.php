<?php

declare(strict_types=1);

namespace App\Model\Product\Flag;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataFactoryInterface;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataProviderInterface;

class FlagDetailFriendlyUrlDataProvider implements FriendlyUrlDataProviderInterface
{
    private const ROUTE_NAME = 'front_flag_detail';

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataFactoryInterface $friendlyUrlDataFactory
     */
    public function __construct(
        private EntityManagerInterface $em,
        private FriendlyUrlDataFactoryInterface $friendlyUrlDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Component\Router\FriendlyUrl\FriendlyUrlData[]
     */
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
     * @return \App\Component\Router\FriendlyUrl\FriendlyUrlData
     */
    public function createFromIdAndName(int $id, string $name): FriendlyUrlData
    {
        /** @var \App\Component\Router\FriendlyUrl\FriendlyUrlData $friendlyUrlData */
        $friendlyUrlData = $this->friendlyUrlDataFactory->create();
        $friendlyUrlData->id = $id;
        $friendlyUrlData->name = $name;

        return $friendlyUrlData;
    }
}
