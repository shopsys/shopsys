<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\LimitNotReachedException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrandFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrandRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroupFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroupRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrentCurrencyProvider;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

class PromoCodeFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly PromoCodeRepository $promoCodeRepository,
        protected readonly PromoCodeFactory $promoCodeFactory,
        protected readonly PromoCodeLimitRepository $promoCodeLimitRepository,
        protected readonly PromoCodeProductRepository $promoCodeProductRepository,
        protected readonly PromoCodeCategoryRepository $promoCodeCategoryRepository,
        protected readonly PromoCodeProductFactory $promoCodeProductFactory,
        protected readonly PromoCodeCategoryFactory $promoCodeCategoryFactory,
        protected readonly PromoCodeBrandRepository $promoCodeBrandRepository,
        protected readonly PromoCodeBrandFactory $promoCodeBrandFactory,
        protected readonly PromoCodePricingGroupRepository $promoCodePricingGroupRepository,
        protected readonly PromoCodePricingGroupFactory $promoCodePricingGroupFactory,
        protected readonly PromoCodeFlagRepository $promoCodeFlagRepository,
        protected readonly HashGenerator $hashGenerator,
        protected readonly CurrentCurrencyProvider $currentCurrencyProvider,
    ) {
    }

    public function create(PromoCodeData $promoCodeData): PromoCode
    {
        $promoCode = $this->promoCodeFactory->create($promoCodeData);
        $this->em->persist($promoCode);
        $this->em->flush();

        $this->refreshPromoCodeRelations($promoCode, $promoCodeData);

        return $promoCode;
    }

    public function edit(int $promoCodeId, PromoCodeData $promoCodeData): PromoCode
    {
        $promoCode = $this->getById($promoCodeId);
        $promoCode->edit($promoCodeData);
        $this->em->flush();

        $this->refreshPromoCodeRelations($promoCode, $promoCodeData);

        return $promoCode;
    }

    public function getById(int $promoCodeId): PromoCode
    {
        return $this->promoCodeRepository->getById($promoCodeId);
    }

    public function deleteById(int $promoCodeId): void
    {
        $promoCode = $this->getById($promoCodeId);
        $this->em->remove($promoCode);
        $this->em->flush();
    }

    public function findPromoCodeByCodeAndDomain(string $code, int $domainId): ?PromoCode
    {
        return $this->promoCodeRepository->findByCodeAndDomainId($code, $domainId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode[]
     */
    public function getAll(): array
    {
        return $this->promoCodeRepository->getAll();
    }

    protected function refreshPromoCodeRelations(PromoCode $promoCode, PromoCodeData $promoCodeData): void
    {
        $this->refreshPromoCodeLimits($promoCode, $promoCodeData->limitsByCurrencyCode);
        $this->refreshPromoCodeProducts($promoCode, $promoCodeData->productsWithSale);
        $this->refreshPromoCodeCategories($promoCode, $promoCodeData->categoriesWithSale);
        $this->refreshPromoCodePricingGroups($promoCode, $promoCodeData->limitedPricingGroups);
        $this->refreshPromoCodeBrands($promoCode, $promoCodeData->brandsWithSale);
        $this->refreshPromoCodeFlags($promoCode, $promoCodeData->flags);
    }

    /**
     * @param array<string, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit[]> $limitsByCurrencyCode
     */
    protected function refreshPromoCodeLimits(PromoCode $promoCode, array $limitsByCurrencyCode): void
    {
        $this->removeExistingPromoCodeLimits($promoCode);

        if ($promoCode->isFreeTransportAndPaymentType()) {
            return;
        }

        $limits = array_merge(...array_values($limitsByCurrencyCode));

        foreach ($limits as $limit) {
            $promoCodeLimit = clone $limit;
            $promoCodeLimit->setPromoCode($promoCode);

            $this->em->persist($promoCodeLimit);
        }

        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup[] $pricingGroups
     */
    protected function refreshPromoCodePricingGroups(PromoCode $promoCode, array $pricingGroups): void
    {
        $needFlush = false;
        $pricingGroupIdsFromForm = [];
        $pricingGroupIdsFromStorage = [];

        foreach ($pricingGroups as $pricingGroup) {
            $pricingGroupIdsFromForm[$pricingGroup->getId()] = $pricingGroup->getId();
        }

        $promoCodePricingGroups = $this->promoCodePricingGroupRepository->getAllByPromoCodeId($promoCode->getId());

        foreach ($promoCodePricingGroups as $promoCodePricingGroup) {
            $pricingGroupId = $promoCodePricingGroup->getPricingGroup()->getId();

            if (in_array($pricingGroupId, $pricingGroupIdsFromForm, true) === false) {
                $this->em->remove($promoCodePricingGroup);
                $needFlush = true;
            } else {
                $pricingGroupIdsFromStorage[$pricingGroupId] = $pricingGroupId;
            }
        }

        if ($needFlush === true) {
            $this->em->flush();
        }

        foreach ($pricingGroups as $pricingGroup) {
            if (in_array($pricingGroup->getId(), $pricingGroupIdsFromStorage, true) === false) {
                $this->promoCodePricingGroupFactory->create($promoCode, $pricingGroup);
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     */
    protected function refreshPromoCodeCategories(PromoCode $promoCode, array $categories): void
    {
        $needFlush = false;
        $categoryIdsFromForm = [];
        $categoryIdsFromStorage = [];

        foreach ($categories as $category) {
            $categoryIdsFromForm[$category->getId()] = $category->getId();
        }

        $promoCodeCategories = $this->promoCodeCategoryRepository->getAllByPromoCodeId($promoCode->getId());

        foreach ($promoCodeCategories as $promoCodeCategory) {
            $categoryId = $promoCodeCategory->getCategory()->getId();

            if (in_array($categoryId, $categoryIdsFromForm, true) === false) {
                $this->em->remove($promoCodeCategory);
                $needFlush = true;
            } else {
                $categoryIdsFromStorage[$categoryId] = $categoryId;
            }
        }

        if ($needFlush === true) {
            $this->em->flush();
        }

        foreach ($categories as $category) {
            if (in_array($category->getId(), $categoryIdsFromStorage, true) === false) {
                $this->promoCodeCategoryFactory->create($promoCode, $category);
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     */
    protected function refreshPromoCodeProducts(PromoCode $promoCode, array $products): void
    {
        $needFlush = false;
        $productIdsFromForm = [];
        $productIdsFromStorage = [];

        foreach ($products as $product) {
            $productIdsFromForm[$product->getId()] = $product->getId();
        }

        $promoCodeProducts = $this->promoCodeProductRepository->getAllByPromoCodeId($promoCode->getId());

        foreach ($promoCodeProducts as $promoCodeProduct) {
            $productId = $promoCodeProduct->getProduct()->getId();

            if (in_array($productId, $productIdsFromForm, true) === false) {
                $this->em->remove($promoCodeProduct);
                $needFlush = true;
            } else {
                $productIdsFromStorage[$productId] = $productId;
            }
        }

        if ($needFlush === true) {
            $this->em->flush();
        }

        foreach ($products as $product) {
            if (in_array($product->getId(), $productIdsFromStorage, true) === false) {
                $this->promoCodeProductFactory->create($promoCode, $product);
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[] $brands
     */
    protected function refreshPromoCodeBrands(PromoCode $promoCode, array $brands): void
    {
        $needFlush = false;
        $brandIdsFromForm = [];
        $brandIdsFromStorage = [];

        foreach ($brands as $brand) {
            $brandIdsFromForm[$brand->getId()] = $brand->getId();
        }

        $promoCodeBrands = $this->promoCodeBrandRepository->getAllByPromoCodeId($promoCode->getId());

        foreach ($promoCodeBrands as $promoCodeBrand) {
            $brandId = $promoCodeBrand->getBrand()->getId();

            if (in_array($brandId, $brandIdsFromForm, true) === false) {
                $this->em->remove($promoCodeBrand);
                $needFlush = true;
            } else {
                $brandIdsFromStorage[$brandId] = $brandId;
            }
        }

        if ($needFlush === true) {
            $this->em->flush();
        }

        foreach ($brands as $brand) {
            if (in_array($brand->getId(), $brandIdsFromStorage, true) === false) {
                $this->promoCodeBrandFactory->create($promoCode, $brand);
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag[] $flags
     */
    protected function refreshPromoCodeFlags(PromoCode $promoCode, array $flags): void
    {
        $this->removeExistingPromoCodeFlags($promoCode);

        foreach ($flags as $flag) {
            $promoCodeFlag = clone $flag;
            $promoCodeFlag->setPromoCode($promoCode);

            $this->em->persist($promoCodeFlag);
        }

        $this->em->flush();
    }

    protected function removeExistingPromoCodeLimits(PromoCode $promoCode): void
    {
        foreach ($this->promoCodeLimitRepository->getLimitsByPromoCodeId($promoCode->getId()) as $promoCodeLimit) {
            $this->em->remove($promoCodeLimit);
        }

        $this->em->flush();
    }

    protected function removeExistingPromoCodeFlags(PromoCode $promoCode): void
    {
        foreach ($this->promoCodeFlagRepository->getFlagsByPromoCodeId($promoCode->getId()) as $promoCodeFlag) {
            $this->em->remove($promoCodeFlag);
        }

        $this->em->flush();
    }

    public function massCreate(PromoCodeData $promoCodeData): void
    {
        $existingPromoCodeCodes = $this->promoCodeRepository->getAllPromoCodeCodes();
        $generatedPromoCodeCount = 0;

        while ($generatedPromoCodeCount < $promoCodeData->quantity) {
            $promoCodeDataForCreate = clone $promoCodeData;
            $code = $promoCodeDataForCreate->prefix . strtoupper($this->hashGenerator->generateHashWithoutConfusingCharacters(PromoCode::MASS_GENERATED_CODE_LENGTH));

            if (in_array($code, $existingPromoCodeCodes, true)) {
                continue;
            }

            $promoCodeDataForCreate->code = $code;

            $this->create($promoCodeDataForCreate);

            $existingPromoCodeCodes[] = $code;
            $generatedPromoCodeCount++;
        }
    }

    public function getMassLastGeneratedBatchId(): int
    {
        return $this->promoCodeRepository->getMassLastGeneratedBatchId();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode[]|null
     */
    public function findByMassBatchId(int $batchId): ?array
    {
        return $this->promoCodeRepository->findByMassBatchId($batchId);
    }

    public function getHighestLimitByPromoCodeAndTotalPrice(
        PromoCode $promoCode,
        PriceInterface $price,
    ): PromoCodeLimit {
        $currency = $price->getCurrency() ?? $this->currentCurrencyProvider->getCurrentCurrencyOfDomain($promoCode->getDomainId());

        $promoCodeLimit = $this->promoCodeLimitRepository->getHighestLimitByPromoCodeAndTotalPrice(
            $promoCode,
            $price->getPriceWithVat(),
            $currency,
        );

        if ($promoCodeLimit === null) {
            throw new LimitNotReachedException($promoCode);
        }

        return $promoCodeLimit;
    }
}
