<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdditionalService;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\AdditionalService\Exception\AdditionalServiceNotFoundException;

class AdditionalServiceRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    protected function getAdditionalServiceRepository(): EntityRepository
    {
        return $this->em->getRepository(AdditionalService::class);
    }

    public function getById(int $additionalServiceId): AdditionalService
    {
        $additionalService = $this->findById($additionalServiceId);

        if ($additionalService === null) {
            throw new AdditionalServiceNotFoundException(
                'Additional service with ID ' . $additionalServiceId . ' not found.',
            );
        }

        return $additionalService;
    }

    public function findById(int $additionalServiceId): ?AdditionalService
    {
        return $this->getAdditionalServiceRepository()->find($additionalServiceId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[]
     */
    public function getAllOrderedByPosition(): array
    {
        return $this->em->createQueryBuilder()
            ->select('ads, at')
            ->from(AdditionalService::class, 'ads')
            ->leftJoin('ads.translations', 'at')
            ->orderBy('ads.position', 'ASC')
            ->addOrderBy('ads.id', 'ASC')
            ->getQuery()->getResult();
    }
}
