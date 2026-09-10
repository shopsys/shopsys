<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdditionalService;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;

class AdditionalServiceFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly AdditionalServiceRepository $additionalServiceRepository,
        protected readonly AdditionalServiceFactory $additionalServiceFactory,
        protected readonly ImageFacade $imageFacade,
    ) {
    }

    public function getById(int $additionalServiceId): AdditionalService
    {
        return $this->additionalServiceRepository->getById($additionalServiceId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[]
     */
    public function getAllOrderedByPosition(): array
    {
        return $this->additionalServiceRepository->getAllOrderedByPosition();
    }

    /**
     * @param int[] $productIds
     * @return array<int, \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[]>
     */
    public function getEnabledIndexedByProductIds(array $productIds, int $domainId): array
    {
        return $this->additionalServiceRepository->getEnabledIndexedByProductIds($productIds, $domainId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[]
     */
    public function getEnabledByProductIdAndDomainId(int $productId, int $domainId): array
    {
        return $this->additionalServiceRepository->getEnabledByProductIdAndDomainId($productId, $domainId);
    }

    /**
     * @param int[] $additionalServiceIds
     * @return \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[]
     */
    public function getEnabledByIds(array $additionalServiceIds, int $domainId): array
    {
        return $this->additionalServiceRepository->getEnabledByIds($additionalServiceIds, $domainId);
    }

    public function create(AdditionalServiceData $additionalServiceData): AdditionalService
    {
        $additionalService = $this->additionalServiceFactory->create($additionalServiceData);

        $this->em->persist($additionalService);
        $this->em->flush();

        $this->imageFacade->manageImages($additionalService, $additionalServiceData->image);
        $this->em->flush();

        return $additionalService;
    }

    public function edit(int $additionalServiceId, AdditionalServiceData $additionalServiceData): AdditionalService
    {
        $additionalService = $this->additionalServiceRepository->getById($additionalServiceId);
        $additionalService->edit($additionalServiceData);

        $this->imageFacade->manageImages($additionalService, $additionalServiceData->image);
        $this->em->flush();

        return $additionalService;
    }

    public function deleteById(int $additionalServiceId): void
    {
        $additionalService = $this->additionalServiceRepository->getById($additionalServiceId);

        $this->em->remove($additionalService);
        $this->em->flush();
    }

    /**
     * @param int[] $productIds
     * @return array<int, \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[]>
     */
    public function getShownInFeedsIndexedByProductIds(array $productIds, int $domainId): array
    {
        return $this->additionalServiceRepository->getShownInFeedsIndexedByProductIds($productIds, $domainId);
    }

    public function useProductVatRateWhereVatIsMissing(int $domainId): void
    {
        $this->additionalServiceRepository->useProductVatRateWhereVatIsMissing($domainId);
    }
}
