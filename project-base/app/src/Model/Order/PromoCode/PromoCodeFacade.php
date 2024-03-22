<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Component\String\HashGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrandFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrandRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData as BasePromoCodeData;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade as BasePromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroupFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroupRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeRepository;

/**
 * @property \App\Model\Order\PromoCode\PromoCodeRepository $promoCodeRepository
 * @method \App\Model\Order\PromoCode\PromoCode getById(int $promoCodeId)
 * @method \App\Model\Order\PromoCode\PromoCode[] getAll()
 */
class PromoCodeFacade extends BasePromoCodeFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Order\PromoCode\PromoCodeRepository $promoCodeRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFactory $promoCodeFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductRepository $promoCodeProductRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryRepository $promoCodeCategoryRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductFactory $promoCodeProductFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryFactory $promoCodeCategoryFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitRepository $promoCodeLimitRepository
     * @param \App\Component\String\HashGenerator $hashGenerator
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrandRepository $promoCodeBrandRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrandFactory $promoCodeBrandFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroupRepository $promoCodePricingGroupRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroupFactory $promoCodePricingGroupFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository $promoCodeFlagRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        PromoCodeRepository $promoCodeRepository,
        PromoCodeFactoryInterface $promoCodeFactory,
        private readonly Domain $domain,
        private readonly PromoCodeProductRepository $promoCodeProductRepository,
        private readonly PromoCodeCategoryRepository $promoCodeCategoryRepository,
        private readonly PromoCodeProductFactory $promoCodeProductFactory,
        private readonly PromoCodeCategoryFactory $promoCodeCategoryFactory,
        private readonly PromoCodeLimitRepository $promoCodeLimitRepository,
        private readonly HashGenerator $hashGenerator,
        private readonly PromoCodeBrandRepository $promoCodeBrandRepository,
        private readonly PromoCodeBrandFactory $promoCodeBrandFactory,
        private readonly PromoCodePricingGroupRepository $promoCodePricingGroupRepository,
        private readonly PromoCodePricingGroupFactory $promoCodePricingGroupFactory,
        private readonly PromoCodeFlagRepository $promoCodeFlagRepository,
    ) {
        parent::__construct($em, $promoCodeRepository, $promoCodeFactory);
    }

    /**
     * @param string $code
     * @return \App\Model\Order\PromoCode\PromoCode|null
     */
    public function findPromoCodeByCode($code): ?PromoCode
    {
        return $this->promoCodeRepository->findByCodeAndDomainId($code, $this->domain->getId());
    }

    /**
     * @param string $code
     * @param int $domainId
     * @return \App\Model\Order\PromoCode\PromoCode|null
     */
    public function findPromoCodeByCodeAndDomain(string $code, int $domainId): ?PromoCode
    {
        return $this->promoCodeRepository->findByCodeAndDomainId($code, $domainId);
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     * @return \App\Model\Order\PromoCode\PromoCode
     */
    public function create(BasePromoCodeData $promoCodeData): PromoCode
    {
        /** @var \App\Model\Order\PromoCode\PromoCode $promoCode */
        $promoCode = parent::create($promoCodeData);
        $this->refreshPromoCodeRelations($promoCode, $promoCodeData);

        return $promoCode;
    }

    /**
     * @param int $promoCodeId
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     * @return \App\Model\Order\PromoCode\PromoCode
     */
    public function edit($promoCodeId, BasePromoCodeData $promoCodeData): PromoCode
    {
        /** @var \App\Model\Order\PromoCode\PromoCode $promoCode */
        $promoCode = parent::edit($promoCodeId, $promoCodeData);
        $this->refreshPromoCodeRelations($promoCode, $promoCodeData);

        return $promoCode;
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     */
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

    /**
     * @return int
     */
    public function getMassLastGeneratedBatchId(): int
    {
        return $this->promoCodeRepository->getMassLastGeneratedBatchId();
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit[] $limits
     */
    private function refreshPromoCodeLimits(PromoCode $promoCode, array $limits): void
    {
        $this->promoCodeLimitRepository->deleteByPromoCodeId($promoCode->getId());

        foreach ($limits as $limit) {
            $limit->setPromoCode($promoCode);
            $this->em->persist($limit);
        }

        $this->em->flush();
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup[] $pricingGroups
     */
    private function refreshPromoCodePricingGroups(PromoCode $promoCode, array $pricingGroups): void
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
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \App\Model\Category\Category[] $categories
     */
    private function refreshPromoCodeCategories(PromoCode $promoCode, array $categories): void
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
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \App\Model\Product\Product[] $products
     */
    private function refreshPromoCodeProducts(PromoCode $promoCode, array $products): void
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
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \App\Model\Product\Brand\Brand[] $brands
     */
    private function refreshPromoCodeBrands(PromoCode $promoCode, array $brands): void
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
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag[] $flags
     */
    private function refreshPromoCodeFlags(PromoCode $promoCode, array $flags): void
    {
        $this->promoCodeFlagRepository->deleteByPromoCodeId($promoCode->getId());

        foreach ($flags as $flag) {
            $flag->setPromoCode($promoCode);
            $this->em->persist($flag);
        }

        $this->em->flush();
    }

    /**
     * @param int $batchId
     * @return \App\Model\Order\PromoCode\PromoCode[]|null
     */
    public function findByMassBatchId(int $batchId): ?array
    {
        return $this->promoCodeRepository->findByMassBatchId($batchId);
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     */
    private function refreshPromoCodeRelations(PromoCode $promoCode, PromoCodeData $promoCodeData): void
    {
        $this->refreshPromoCodeLimits($promoCode, $promoCodeData->limits);
        $this->refreshPromoCodeProducts($promoCode, $promoCodeData->productsWithSale);
        $this->refreshPromoCodeCategories($promoCode, $promoCodeData->categoriesWithSale);
        $this->refreshPromoCodePricingGroups($promoCode, $promoCodeData->limitedPricingGroups);
        $this->refreshPromoCodeBrands($promoCode, $promoCodeData->brandsWithSale);
        $this->refreshPromoCodeFlags($promoCode, $promoCodeData->flags);
    }
}
