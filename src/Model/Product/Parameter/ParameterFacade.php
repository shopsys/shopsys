<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use App\Component\Image\Image;
use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade as BaseParameterFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @property \App\Model\Product\Parameter\ParameterRepository $parameterRepository
 * @method \App\Model\Product\Parameter\Parameter getById(int $parameterId)
 * @method \App\Model\Product\Parameter\Parameter[] getAll()
 * @method \App\Model\Product\Parameter\Parameter create(\App\Model\Product\Parameter\ParameterData $parameterData)
 * @method \App\Model\Product\Parameter\Parameter|null findParameterByNames(string[] $namesByLocale)
 * @method \App\Model\Product\Parameter\Parameter edit(int $parameterId, \App\Model\Product\Parameter\ParameterData $parameterData)
 * @method \App\Model\Product\Parameter\ParameterValue getParameterValueByValueTextAndLocale(string $valueText, string $locale)
 * @method dispatchParameterEvent(\App\Model\Product\Parameter\Parameter $parameter, string $eventType)
 */
class ParameterFacade extends BaseParameterFacade
{
    /**
     * @var \App\Model\CategorySeo\ReadyCategorySeoMixFacade
     */
    private $readyCategorySeoMixFacade;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\Product\Parameter\ParameterValueDataFactory
     */
    private $parameterValueDataFactory;

    /**
     * @var \App\Component\UploadedFile\UploadedFileFacade
     */
    protected $uploadedFileFacade;

    /**
     * @var \App\Component\Image\ImageFacade
     */
    private $imageFacade;

    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlRepository
     */
    private $friendlyUrlRepository;

    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private $friendlyUrlFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFactoryInterface $parameterFactory
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory
     * @param \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        ParameterRepository $parameterRepository,
        ParameterFactoryInterface $parameterFactory,
        EventDispatcherInterface $eventDispatcher,
        ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
        Domain $domain,
        ParameterValueDataFactory $parameterValueDataFactory,
        UploadedFileFacade $uploadedFileFacade,
        ImageFacade $imageFacade,
        FriendlyUrlRepository $friendlyUrlRepository,
        FriendlyUrlFacade $friendlyUrlFacade
    ) {
        parent::__construct(
            $em,
            $parameterRepository,
            $parameterFactory,
            $eventDispatcher
        );
        $this->readyCategorySeoMixFacade = $readyCategorySeoMixFacade;
        $this->domain = $domain;
        $this->parameterValueDataFactory = $parameterValueDataFactory;
        $this->uploadedFileFacade = $uploadedFileFacade;
        $this->imageFacade = $imageFacade;
        $this->friendlyUrlRepository = $friendlyUrlRepository;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
    }

    /**
     * @param string $akeneoCode
     * @return \App\Model\Product\Parameter\Parameter|null
     */
    public function findParameterByAkeneoCode(string $akeneoCode): ?Parameter
    {
        return $this->parameterRepository->findParameterByAkeneoCode($akeneoCode);
    }

    /**
     * @param int[] $parameterValueIdsByParameterId
     * @return string[]
     */
    public function getParameterValueNamesIndexedByParameterNames(array $parameterValueIdsByParameterId): array
    {
        $parameterValueNamesIndexedByParameterNames = [];
        foreach ($parameterValueIdsByParameterId as $parameterId => $parameterValueId) {
            $parameter = $this->getById((int)$parameterId);
            $parameterValue = $this->parameterRepository->getParameterValueById((int)$parameterValueId);

            $parameterValueNamesIndexedByParameterNames[$parameter->getName()] = $parameterValue->getText();
        }

        return $parameterValueNamesIndexedByParameterNames;
    }

    /**
     * @param int $parameterValueId
     * @return \App\Model\Product\Parameter\ParameterValue
     */
    public function getParameterValueById(int $parameterValueId): ParameterValue
    {
        return $this->parameterRepository->getParameterValueById($parameterValueId);
    }

    /**
     * @param int $parameterId
     */
    public function deleteById($parameterId): void
    {
        $parameter = $this->parameterRepository->getById($parameterId);
        $this->readyCategorySeoMixFacade->deleteAllWithParameter($parameter);

        parent::deleteById($parameterId);
    }

    /**
     * @return \App\Model\Product\Parameter\ParameterValue[][]
     */
    public function getListBooleanParameterValuesIndexedByLocaleAndText(): array
    {
        $locales = $this->domain->getAllLocales();
        $translationKeys = ['Yes', 'No'];

        $parameterValuesIndexedByLocaleAndText = [];
        foreach ($locales as $locale) {
            foreach ($translationKeys as $translationKey) {
                $parameterValueData = $this->parameterValueDataFactory->create();

                if ($translationKey === 'Yes') {
                    $parameterValueData->text = t('Yes', [], 'messages', $locale);
                } else {
                    $parameterValueData->text = t('No', [], 'messages', $locale);
                }

                $parameterValueData->locale = $locale;

                $parameterValuesIndexedByLocaleAndText[$locale][$parameterValueData->text] = $this->parameterRepository->findOrCreateParameterValueByParameterValueData(
                    $parameterValueData
                );
            }
        }

        return $parameterValuesIndexedByLocaleAndText;
    }

    /**
     * @return int[]
     */
    public function getAllAkeneoParameterIds(): array
    {
        return $this->parameterRepository->getAllAkeneoParameterIds();
    }

    /**
     * @param int $parameterValueId
     * @param \App\Model\Product\Parameter\ParameterValueData $parameterValueData
     * @return \App\Model\Product\Parameter\ParameterValue
     */
    public function editParameterValue(int $parameterValueId, ParameterValueData $parameterValueData): ParameterValue
    {
        $parameterValue = $this->parameterRepository->getParameterValueById($parameterValueId);
        $parameterValue->edit($parameterValueData);

        if ($parameterValueData->colourIcon->uploadedFilenames) {
            $this->uploadedFileFacade->manageSingleFile($parameterValue, $parameterValueData->colourIcon);
        }

        if (empty($parameterValueData->colourIcon->uploadedFilenames) && $parameterValueData->colourIcon->filesToDelete) {
            $this->uploadedFileFacade->deleteAllUploadedFilesByEntity($parameterValue);
        }

        $this->em->flush();

        return $parameterValue;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param string $locale
     * @return \App\Model\Product\Parameter\ParameterValue[][]
     */
    public function getParameterValuesIndexedByParameterIdForMainProduct(Product $product, string $locale): array
    {
        $parameterValuesIndexedByParameterId = [];
        foreach ($product->getVariantParameters() as $parameter) {
            $parameterValuesIndexedByParameterId[$parameter->getId()] =
                $this->parameterRepository->getParameterValuesForVariantsByMainProductAndParameter($product, $parameter, $locale);
        }

        return $parameterValuesIndexedByParameterId;
    }

    /**
     * @param \App\Model\Product\Product $productVariant
     * @param \App\Model\Product\Parameter\Parameter[] $variantParameters
     * @param string $locale
     * @return \App\Model\Product\Parameter\ParameterValue[]
     */
    public function getParameterValuesIndexedByParameterIdForProductVariant(
        Product $productVariant,
        array $variantParameters,
        string $locale
    ): array {
        $parameterValuesIndexedByParameterId = [];
        foreach ($variantParameters as $parameter) {
            try {
                $parameterValuesIndexedByParameterId[$parameter->getId()] =
                    $this->parameterRepository->getParameterValueForVariantByProductVariantAndParameter($productVariant, $parameter, $locale);
            } catch (NoResultException $exception) {
            }
        }

        return $parameterValuesIndexedByParameterId;
    }

    /**
     * @param array $variantParameterValuesIndexedByParameterId
     * @return int[]
     */
    public function getParameterValueIdIndexedByParameterId(array $variantParameterValuesIndexedByParameterId): array
    {
        $variantSetup = [];
        foreach ($variantParameterValuesIndexedByParameterId as $parameterId => $parameterValue) {
            $variantSetup[$parameterId] = $parameterValue->getId();
        }

        return $variantSetup;
    }

    /**
     * @param array $variantParameterValuesIndexedByParameterId
     * @return string
     */
    public function getVariantSetupKey(array $variantParameterValuesIndexedByParameterId): string
    {
        $variantSetupParts = [];
        foreach ($variantParameterValuesIndexedByParameterId as $parameterId => $parameterValue) {
            $variantSetupParts[] = $parameterId . '_' . $parameterValue->getId();
        }

        return implode('~', $variantSetupParts);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param string $locale
     * @return int[]
     */
    public function getVariantSetupKeyMapByMainProduct(Product $product, string $locale): array
    {
        $data = $this->parameterRepository->getVariantProductParameterValuesData($product, $locale);

        $variantSetupPartsIndexedByProductVariantId = [];
        foreach ($data as $variantParameterValue) {
            $variantSetupPartsIndexedByProductVariantId[$variantParameterValue['ppv_product_id']][] = $variantParameterValue['ppv_parameter_id'] . '_' . $variantParameterValue['ppv_value_id'];
        }

        $variantSetupKeyMap = [];
        foreach ($variantSetupPartsIndexedByProductVariantId as $productVariantId => $variantSetupParts) {
            $variantSetupKey = implode('~', $variantSetupParts);
            $variantSetupKeyMap[$variantSetupKey] = $productVariantId;
        }

        return $variantSetupKeyMap;
    }

    /**
     * @param \App\Model\Product\Product $mainProduct
     * @param string $locale
     * @param int $domainId
     * @return array
     */
    public function getVariantsSetupForElasticByMainProduct(Product $mainProduct, string $locale, int $domainId): array
    {
        $data = $this->parameterRepository->getVariantProductParameterValuesData($mainProduct, $locale);

        $variantSetup = [];
        foreach ($data as $variantParameterValue) {
            $variantSetup[$variantParameterValue['ppv_product_id']][] = [
                'parameter_id' => $variantParameterValue['ppv_parameter_id'],
                'parameter_value_id' => $variantParameterValue['ppv_value_id'],
            ];
        }

        /** @var \App\Component\Image\Image[] $imagesIndexedByEntityIds */
        $imagesIndexedByEntityIds = $this->imageFacade->getImagesByEntitiesIndexedByEntityId(array_keys($variantSetup), Product::class);

        $defaultVariantId = null;
        if (count($variantSetup) > 0 && $mainProduct->getDefaultVariant() !== null) {
            $defaultVariantId = $mainProduct->getDefaultVariant()->getId();
        }

        $variantsSetupForElastic = [];
        foreach ($variantSetup as $variantId => $parameterValuesList) {
            $variantParametersSetup = [
                'variant_id' => $variantId,
                'parameter_values_setup' => $parameterValuesList,
                'variant_url' => $this->getVariantUrl($variantId, $domainId),
            ];

            if (array_key_exists($variantId, $imagesIndexedByEntityIds)) {
                $variantParametersSetup['image_url'] = $this->getVariantImageUrl($imagesIndexedByEntityIds[$variantId], $domainId);
            }

            if ($variantId === $defaultVariantId) {
                $variantParametersSetup['is_default_variant'] = true;
            }

            $variantsSetupForElastic[] = $variantParametersSetup;
        }

        return $variantsSetupForElastic;
    }

    /**
     * @param \App\Component\Image\Image $image
     * @param int $domainId
     * @return string
     */
    private function getVariantImageUrl(Image $image, int $domainId): string
    {
        $domainConfig = $this->domain->getDomainConfigById($domainId);

        return $this->imageFacade->getImageUrl($domainConfig, $image, 'list');
    }

    /**
     * @param int $variantId
     * @param int $domainId
     * @return string
     */
    private function getVariantUrl(int $variantId, int $domainId): string
    {
        $friendlyUrl = $this->friendlyUrlRepository->getMainFriendlyUrl($domainId, 'front_product_detail', $variantId);

        return $this->friendlyUrlFacade->getAbsoluteUrlByFriendlyUrl($friendlyUrl);
    }
}
