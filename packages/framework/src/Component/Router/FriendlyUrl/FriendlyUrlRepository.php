<?php

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Doctrine\ORM\EntityManagerInterface;

class FriendlyUrlRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function getFriendlyUrlRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(FriendlyUrl::class);
    }
    
    public function findByDomainIdAndSlug(int $domainId, string $slug): ?\Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl
    {
        return $this->getFriendlyUrlRepository()->findOneBy(
            [
                'domainId' => $domainId,
                'slug' => $slug,
            ]
        );
    }
    
    public function getMainFriendlyUrl(int $domainId, string $routeName, int $entityId): \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl
    {
        $criteria = [
            'domainId' => $domainId,
            'routeName' => $routeName,
            'entityId' => $entityId,
            'main' => true,
        ];
        $friendlyUrl = $this->getFriendlyUrlRepository()->findOneBy($criteria);

        if ($friendlyUrl === null) {
            throw new \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException();
        }

        return $friendlyUrl;
    }
    
    public function findMainFriendlyUrl(int $domainId, string $routeName, int $entityId): ?\Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl
    {
        $criteria = [
            'domainId' => $domainId,
            'routeName' => $routeName,
            'entityId' => $entityId,
            'main' => true,
        ];

        return $this->getFriendlyUrlRepository()->findOneBy($criteria);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
     */
    public function getAllByRouteNameAndEntityId(string $routeName, int $entityId): array
    {
        $criteria = [
            'routeName' => $routeName,
            'entityId' => $entityId,
        ];

        return $this->getFriendlyUrlRepository()->findBy(
            $criteria,
            [
                'domainId' => 'ASC',
                'slug' => 'ASC',
            ]
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
     */
    public function getAllByRouteNameAndEntityIdAndDomainId(string $routeName, int $entityId, int $domainId): array
    {
        $criteria = [
            'routeName' => $routeName,
            'entityId' => $entityId,
            'domainId' => $domainId,
        ];

        return $this->getFriendlyUrlRepository()->findBy($criteria);
    }

    /**
     * @param object[] $entities
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
     */
    public function getMainFriendlyUrlsByEntitiesIndexedByEntityId(array $entities, string $routeName, int $domainId): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('f')
            ->from(FriendlyUrl::class, 'f', 'f.entityId')
            ->andWhere('f.routeName = :routeName')->setParameter('routeName', $routeName)
            ->andWhere('f.entityId IN (:entities)')->setParameter('entities', $entities)
            ->andWhere('f.domainId = :domainId')->setParameter('domainId', $domainId)
            ->andWhere('f.main = TRUE');

        return $queryBuilder->getQuery()->execute();
    }
}
