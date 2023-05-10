<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Component\DateTimeHelper\DateTimeHelper;
use App\Component\String\HashGenerator;
use App\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData as BasePromoCodeData;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade as BasePromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeRepository;

/**
 * @property \App\Model\Order\PromoCode\PromoCodeRepository $promoCodeRepository
 * @method \App\Model\Order\PromoCode\PromoCode getById(int $promoCodeId)
 * @method \App\Model\Order\PromoCode\PromoCode[] getAll()
 */
class PromoCodeFacade extends BasePromoCodeFacade
{
    public const PROMOCODE_DEFAULT_TIME_FROM = '00:00:00';
    public const PROMOCODE_DEFAULT_TIME_TO = '23:59:59';
    public const DATABASE_DATE_FORMAT = 'Y-m-d';
    private const MASS_CREATE_BATCH_SIZE = 200;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Component\DateTimeHelper\DateTimeHelper
     */
    private $dateTimeHelper;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeProductRepository
     */
    private $promoCodeProductRepository;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeCategoryRepository
     */
    private $promoCodeCategoryRepository;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeProductFactory
     */
    private $promoCodeProductFactory;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeCategoryFactory
     */
    private $promoCodeCategoryFactory;

    /**
     * @var \App\Component\String\HashGenerator
     */
    private $hashGenerator;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeLimitRepository
     */
    private $promoCodeLimitRepository;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeLimitFactory
     */
    private PromoCodeLimitFactory $promoCodeLimitFactory;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeBrandRepository
     */
    private PromoCodeBrandRepository $promoCodeBrandRepository;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeBrandFactory
     */
    private PromoCodeBrandFactory $promoCodeBrandFactory;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodePricingGroupRepository
     */
    private PromoCodePricingGroupRepository $promoCodePricingGroupRepository;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodePricingGroupFactory
     */
    private PromoCodePricingGroupFactory $promoCodePricingGroupFactory;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository
     */
    private PromoCodeFlagRepository $promoCodeFlagRepository;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Order\PromoCode\PromoCodeRepository $promoCodeRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFactoryInterface $promoCodeFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\DateTimeHelper\DateTimeHelper $dateTimeHelper
     * @param \App\Model\Order\PromoCode\PromoCodeProductRepository $promoCodeProductRepository
     * @param \App\Model\Order\PromoCode\PromoCodeCategoryRepository $promoCodeCategoryRepository
     * @param \App\Model\Order\PromoCode\PromoCodeProductFactory $promoCodeProductFactory
     * @param \App\Model\Order\PromoCode\PromoCodeCategoryFactory $promoCodeCategoryFactory
     * @param \App\Model\Order\PromoCode\PromoCodeLimitRepository $promoCodeLimitRepository
     * @param \App\Component\String\HashGenerator $hashGenerator
     * @param \App\Model\Order\PromoCode\PromoCodeLimitFactory $promoCodeLimitFactory
     * @param \App\Model\Order\PromoCode\PromoCodeBrandRepository $promoCodeBrandRepository
     * @param \App\Model\Order\PromoCode\PromoCodeBrandFactory $promoCodeBrandFactory
     * @param \App\Model\Order\PromoCode\PromoCodePricingGroupRepository $promoCodePricingGroupRepository
     * @param \App\Model\Order\PromoCode\PromoCodePricingGroupFactory $promoCodePricingGroupFactory
     * @param \App\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository $promoCodeFlagRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        PromoCodeRepository $promoCodeRepository,
        PromoCodeFactoryInterface $promoCodeFactory,
        Domain $domain,
        DateTimeHelper $dateTimeHelper,
        PromoCodeProductRepository $promoCodeProductRepository,
        PromoCodeCategoryRepository $promoCodeCategoryRepository,
        PromoCodeProductFactory $promoCodeProductFactory,
        PromoCodeCategoryFactory $promoCodeCategoryFactory,
        PromoCodeLimitRepository $promoCodeLimitRepository,
        HashGenerator $hashGenerator,
        PromoCodeLimitFactory $promoCodeLimitFactory,
        PromoCodeBrandRepository $promoCodeBrandRepository,
        PromoCodeBrandFactory $promoCodeBrandFactory,
        PromoCodePricingGroupRepository $promoCodePricingGroupRepository,
        PromoCodePricingGroupFactory $promoCodePricingGroupFactory,
        PromoCodeFlagRepository $promoCodeFlagRepository
    ) {
        parent::__construct($em, $promoCodeRepository, $promoCodeFactory);

        $this->domain = $domain;
        $this->dateTimeHelper = $dateTimeHelper;
        $this->promoCodeProductRepository = $promoCodeProductRepository;
        $this->promoCodeCategoryRepository = $promoCodeCategoryRepository;
        $this->promoCodeProductFactory = $promoCodeProductFactory;
        $this->promoCodeCategoryFactory = $promoCodeCategoryFactory;
        $this->promoCodeLimitRepository = $promoCodeLimitRepository;
        $this->hashGenerator = $hashGenerator;
        $this->promoCodeLimitFactory = $promoCodeLimitFactory;
        $this->promoCodeBrandRepository = $promoCodeBrandRepository;
        $this->promoCodeBrandFactory = $promoCodeBrandFactory;
        $this->promoCodePricingGroupRepository = $promoCodePricingGroupRepository;
        $this->promoCodePricingGroupFactory = $promoCodePricingGroupFactory;
        $this->promoCodeFlagRepository = $promoCodeFlagRepository;
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
        $this->prepareDatetimeValid($promoCodeData);

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
        $this->prepareDatetimeValid($promoCodeData);

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

            $promoCodeDataForCreate->limits = [];
            foreach ($promoCodeData->limits as $promoCodeLimit) {
                $promoCodeDataForCreate->limits[] = $this->promoCodeLimitFactory->create(
                    $promoCodeLimit->getFromPriceWithVat(),
                    $promoCodeLimit->getDiscount()
                );
            }

            $promoCode = $this->create($promoCodeDataForCreate);
            $this->em->persist($promoCode);

            $existingPromoCodeCodes[] = $code;
            $generatedPromoCodeCount++;

            if ($generatedPromoCodeCount % self::MASS_CREATE_BATCH_SIZE !== 0) {
                continue;
            }

            $this->em->flush();
            $this->em->clear(PromoCodeCategory::class);
            $this->em->clear(PromoCodeLimit::class);
            $this->em->clear(PromoCode::class);
        }

        $this->em->flush();
        $this->em->clear();
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
     * @param \App\Model\Order\PromoCode\PromoCodeLimit[] $limits
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
     * @param \App\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag[] $flags
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
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     */
    private function prepareDatetimeValid(PromoCodeData $promoCodeData): void
    {
        if ($promoCodeData->dateValidFrom !== null) {
            $promoCodeData->datetimeValidFrom = $this->createDateTimeInUtc(
                $promoCodeData->dateValidFrom,
                $promoCodeData->timeValidFrom ?? self::PROMOCODE_DEFAULT_TIME_FROM
            );
        }

        $promoCodeData->timeValidTo = $promoCodeData->timeValidTo ? $promoCodeData->timeValidTo . ':59' : self::PROMOCODE_DEFAULT_TIME_TO;

        if ($promoCodeData->dateValidTo !== null) {
            $promoCodeData->datetimeValidTo = $this->createDateTimeInUtc(
                $promoCodeData->dateValidTo,
                $promoCodeData->timeValidTo
            );
        }
    }

    /**
     * @param \DateTime $date
     * @param string $time
     * @return \DateTime
     */
    private function createDateTimeInUtc(DateTime $date, string $time): DateTime
    {
        return $this->dateTimeHelper->convertDatetimeStringFromDisplayTimeZoneToUtc(
            $date->format(self::DATABASE_DATE_FORMAT) . 'T' . $time
        );
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
