<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
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
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFactoryInterface $parameterFactory
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory
     * @param \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        ParameterRepository $parameterRepository,
        ParameterFactoryInterface $parameterFactory,
        EventDispatcherInterface $eventDispatcher,
        ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
        Domain $domain,
        ParameterValueDataFactory $parameterValueDataFactory,
        UploadedFileFacade $uploadedFileFacade
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
                    $parameterValueData->text = t('Yes', [], null, $locale);
                } else {
                    $parameterValueData->text = t('No', [], null, $locale);
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
    public function getVariantSetup(array $variantParameterValuesIndexedByParameterId): array
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
}
