<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\AdditionalService;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServicePriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Product;

class AdditionalServiceQueryDtoFactory
{
    public function __construct(
        protected readonly AdditionalServicePriceCalculation $additionalServicePriceCalculation,
        protected readonly Domain $domain,
    ) {
    }

    protected function createInstance(): AdditionalServiceQueryDto
    {
        return new AdditionalServiceQueryDto();
    }

    public function create(AdditionalService $additionalService, Product $product): AdditionalServiceQueryDto
    {
        $additionalServiceQueryDto = $this->createInstance();
        $this->fillFromAdditionalService($additionalServiceQueryDto, $additionalService, $product);

        return $additionalServiceQueryDto;
    }

    protected function fillFromAdditionalService(
        AdditionalServiceQueryDto $additionalServiceQueryDto,
        AdditionalService $additionalService,
        Product $product,
    ): void {
        $locale = $this->domain->getLocale();

        $additionalServiceQueryDto->id = $additionalService->getId();
        $additionalServiceQueryDto->uuid = $additionalService->getUuid();
        $additionalServiceQueryDto->name = $additionalService->getName($locale) ?? $additionalService->getCatnum();
        $additionalServiceQueryDto->catnum = $additionalService->getCatnum();
        $additionalServiceQueryDto->description = $additionalService->getDescription($locale);
        $additionalServiceQueryDto->deliveryDaysExtension = $additionalService->getDeliveryDaysExtension();
        $additionalServiceQueryDto->price = $this->additionalServicePriceCalculation->calculatePrice(
            $additionalService,
            $product,
            $this->domain->getId(),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[] $additionalServices
     * @return \Shopsys\FrontendApiBundle\Model\AdditionalService\AdditionalServiceQueryDto[]
     */
    public function createMultiple(array $additionalServices, Product $product): array
    {
        return array_map(
            fn (AdditionalService $additionalService) => $this->create($additionalService, $product),
            $additionalServices,
        );
    }
}
