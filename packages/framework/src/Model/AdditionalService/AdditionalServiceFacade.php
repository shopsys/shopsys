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
}
