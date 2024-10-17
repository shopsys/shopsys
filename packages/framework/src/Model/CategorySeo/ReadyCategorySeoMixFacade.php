<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\HttpFoundation\TransactionalMasterRequestListener;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
use Shopsys\FrameworkBundle\Model\CategorySeo\Exception\ReadyCategorySeoMixNotFoundException;
use Shopsys\FrameworkBundle\Model\CategorySeo\Exception\ReadyCategorySeoMixUrlsContainBadDomainUrlException;
use Shopsys\FrameworkBundle\Model\CategorySeo\Exception\ReadyCategorySeoMixUrlsDoNotContainMainFriendlyUrlException;
use Shopsys\FrameworkBundle\Model\CategorySeo\Exception\ReadyCategorySeoMixUrlsDoNotContainUrlForCorrectDomainException;
use Shopsys\FrameworkBundle\Model\CategorySeo\Exception\UnableToFindReadyCategorySeoMixException;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Exception\ParameterNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Exception\ParameterValueNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;

class ReadyCategorySeoMixFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixRepository $readyCategorySeoMixRepository
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFactory $readyCategorySeoMixFactory
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ReadyCategorySeoMixRepository $readyCategorySeoMixRepository,
        protected readonly ReadyCategorySeoMixFactory $readyCategorySeoMixFactory,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly Domain $domain,
        protected readonly FlagFacade $flagFacade,
        protected readonly ParameterFacade $parameterFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ChoseCategorySeoMixCombination $choseCategorySeoMixCombination
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixData $readyCategorySeoMixData
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData $urlListData
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix
     */
    public function createOrEdit(
        ChoseCategorySeoMixCombination $choseCategorySeoMixCombination,
        ReadyCategorySeoMixData $readyCategorySeoMixData,
        UrlListData $urlListData,
    ): ReadyCategorySeoMix {
        $readyCategorySeoMix = $this->findByChoseCategorySeoMixCombination($choseCategorySeoMixCombination);

        $this->em->beginTransaction();

        if ($readyCategorySeoMix === null) {
            $readyCategorySeoMix = $this->readyCategorySeoMixFactory->create($readyCategorySeoMixData);
            $this->em->persist($readyCategorySeoMix);
            $this->em->flush();

            foreach ($readyCategorySeoMixData->readyCategorySeoMixParameterParameterValues as $readyCategorySeoMixParameterParameterValue) {
                $readyCategorySeoMixParameterParameterValue->setReadyCategorySeoMix($readyCategorySeoMix);
                $this->em->persist($readyCategorySeoMixParameterParameterValue);
                $this->em->flush();
            }
        } else {
            $readyCategorySeoMix->edit($readyCategorySeoMixData);
            $this->em->flush();
        }

        $this->saveReadyCategoryMixFriendlyUrls($readyCategorySeoMix, $urlListData);

        try {
            $this->validateReadyCategoryMixFriendlyUrls($readyCategorySeoMix);
        } catch (ReadyCategorySeoMixUrlsContainBadDomainUrlException | ReadyCategorySeoMixUrlsDoNotContainUrlForCorrectDomainException $e) {
            TransactionalMasterRequestListener::setTransactionManuallyRollbacked();
            $this->em->rollback();

            throw $e;
        }

        $this->em->commit();

        return $readyCategorySeoMix;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ChoseCategorySeoMixCombination $choseCategorySeoMixCombination
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix|null
     */
    public function findByChoseCategorySeoMixCombination(
        ChoseCategorySeoMixCombination $choseCategorySeoMixCombination,
    ): ?ReadyCategorySeoMix {
        return $this->readyCategorySeoMixRepository->findByChoseCategorySeoMixCombination($choseCategorySeoMixCombination);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix|null
     */
    public function findById(int $id): ?ReadyCategorySeoMix
    {
        return $this->readyCategorySeoMixRepository->findById($id);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix
     */
    public function getById(int $id): ReadyCategorySeoMix
    {
        $readyCategorySeoMix = $this->readyCategorySeoMixRepository->findById($id);

        if ($readyCategorySeoMix === null) {
            throw new ReadyCategorySeoMixNotFoundException(sprintf('ReadyCategorySeoMix with ID %s not found', $id));
        }

        return $readyCategorySeoMix;
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix
     */
    public function getByUuid(string $uuid): ReadyCategorySeoMix
    {
        $readyCategorySeoMix = $this->readyCategorySeoMixRepository->findByUuid($uuid);

        if ($readyCategorySeoMix === null) {
            throw new ReadyCategorySeoMixNotFoundException(sprintf('ReadyCategorySeoMix with UUID %s not found', $uuid));
        }

        return $readyCategorySeoMix;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix
     */
    public function delete(ReadyCategorySeoMix $readyCategorySeoMix): void
    {
        $this->em->remove($readyCategorySeoMix);
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData $urlListData
     */
    protected function saveReadyCategoryMixFriendlyUrls(
        ReadyCategorySeoMix $readyCategorySeoMix,
        UrlListData $urlListData,
    ): void {
        $this->friendlyUrlFacade->saveUrlListFormData('front_category_seo', $readyCategorySeoMix->getId(), $urlListData);

        $mainFriendlyUrl = $this->friendlyUrlFacade->findMainFriendlyUrl($readyCategorySeoMix->getDomainId(), 'front_category_seo', $readyCategorySeoMix->getId());

        if ($mainFriendlyUrl !== null) {
            return;
        }

        $readyCategoryMixAllFriendlyUrls = $this->friendlyUrlFacade->getAllByRouteNameAndEntityId('front_category_seo', $readyCategorySeoMix->getId());

        if (count($readyCategoryMixAllFriendlyUrls) === 0) {
            return;
        }

        $urlListDataForMainFriendlyUrl = new UrlListData();
        $urlListDataForMainFriendlyUrl->mainFriendlyUrlsByDomainId = [
            array_shift($readyCategoryMixAllFriendlyUrls),
        ];

        $this->friendlyUrlFacade->saveUrlListFormData('front_category_seo', $readyCategorySeoMix->getId(), $urlListDataForMainFriendlyUrl);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix
     */
    protected function validateReadyCategoryMixFriendlyUrls(ReadyCategorySeoMix $readyCategorySeoMix): void
    {
        $readyCategorySeoMixAllFriendlyUrls = $this->friendlyUrlFacade->getAllByRouteNameAndEntityId('front_category_seo', $readyCategorySeoMix->getId());

        $hasCorrectDomainUrl = false;
        $hasMainFriendlyUrl = false;

        foreach ($readyCategorySeoMixAllFriendlyUrls as $friendlyUrl) {
            if ($friendlyUrl->getDomainId() !== $readyCategorySeoMix->getDomainId()) {
                throw new ReadyCategorySeoMixUrlsContainBadDomainUrlException('ReadyCategorySeoMix urls contain bad domain url');
            }

            if ($friendlyUrl->isMain() === true && $hasMainFriendlyUrl === false) {
                $hasMainFriendlyUrl = true;
            }

            $hasCorrectDomainUrl = true;
        }

        if ($hasCorrectDomainUrl === false) {
            throw new ReadyCategorySeoMixUrlsDoNotContainUrlForCorrectDomainException('ReadyCategorySeoMix urls do not contain url for correct domain');
        }

        if ($hasMainFriendlyUrl === false) {
            throw new ReadyCategorySeoMixUrlsDoNotContainMainFriendlyUrlException('ReadyCategorySeoMix urls do not contain main FriendlyUrl');
        }
    }

    /**
     * @param int $categoryId
     * @param array<int, array{parameter: string, values: string[], minimalValue: float|null, maximalValue: float|null}> $parametersFilterData
     * @param string[] $flagUuids
     * @param string|null $orderingMode
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix|null
     */
    public function findReadyCategorySeoMixByQueryInputData(
        int $categoryId,
        array $parametersFilterData,
        array $flagUuids,
        ?string $orderingMode,
    ): ?ReadyCategorySeoMix {
        try {
            $currentDomainConfig = $this->domain->getCurrentDomainConfig();
            $this->checkPossibilityToFindReadyCategorySeoMix($parametersFilterData, $flagUuids, $orderingMode);
            // From now on, we can count on the following facts:
            // - Parameters have only 1 value, or values are empty, and minimalValue === maximalValue (i.e., exactly one value is selected in slider).
            // - Count of flagUuids is 0 or 1.

            return $this->readyCategorySeoMixRepository->getReadyCategorySeoMixFromFilter(
                $categoryId,
                $this->getParameterValueIdsByParameterId($parametersFilterData, $currentDomainConfig->getLocale()),
                $this->flagFacade->getFlagIdsByUuids($flagUuids),
                $orderingMode,
                $currentDomainConfig,
            );
        } catch (UnableToFindReadyCategorySeoMixException|ParameterValueNotFoundException) {
            return null;
        }
    }

    /**
     * @param array<int, array{parameter: string, values: string[], minimalValue: float|null, maximalValue: float|null}> $parametersFilterData
     * @param string[] $flagUuids
     * @param string|null $ordering
     */
    protected function checkPossibilityToFindReadyCategorySeoMix(
        array $parametersFilterData,
        array $flagUuids,
        ?string $ordering,
    ): void {
        if ($ordering === null && count($parametersFilterData) === 0 && count($flagUuids)) {
            throw new UnableToFindReadyCategorySeoMixException(
                'Unable to find ReadyCategorySeoMix: it cannot have set no conditions',
            );
        }

        foreach ($parametersFilterData as $parameterFilterData) {
            $valuesCount = count($parameterFilterData['values']);

            if ($valuesCount === 0 && ($parameterFilterData['minimalValue'] !== $parameterFilterData['maximalValue'])) {
                throw new UnableToFindReadyCategorySeoMixException(
                    'Unable to find ReadyCategorySeoMix: there must be exactly one value for slider parameters selected',
                );
            }

            if ($valuesCount > 1) {
                throw new UnableToFindReadyCategorySeoMixException(
                    'Unable to find ReadyCategorySeoMix: it cannot have more than one parameter value of one parameter',
                );
            }
        }

        if (count($flagUuids) > 1) {
            throw new UnableToFindReadyCategorySeoMixException(
                'Unable to find ReadyCategorySeoMix: it cannot have more than one flag',
            );
        }
    }

    /**
     * @param array $categoryIds
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix[][]
     */
    public function getAllIndexedByCategoryId(array $categoryIds, DomainConfig $domainConfig): array
    {
        return $this->readyCategorySeoMixRepository->getAllIndexedByCategoryId($categoryIds, $domainConfig);
    }

    /**
     * @return array<int>
     */
    public function getAllCategoryIdsInSeoMixes(): array
    {
        return $this->readyCategorySeoMixRepository->getAllCategoryIdsInSeoMixes();
    }

    /**
     * @param array<int, array{parameter: string, values: string[], minimalValue: float|null, maximalValue: float|null}> $parametersFilterData
     * @param string $currentLocale
     * @return array<int,int>
     */
    protected function getParameterValueIdsByParameterId(array $parametersFilterData, string $currentLocale): array
    {
        $parameterIdsByUuids = $this->parameterFacade->getParameterIdsIndexedByUuids(array_column($parametersFilterData, 'parameter'));
        $allParameterValuesUuids = array_merge(...array_column($parametersFilterData, 'values'));
        $parameterValueIdsByUuids = $this->parameterFacade->getParameterValueIdsIndexedByUuids($allParameterValuesUuids);
        $parameterValueIdsByParameterId = [];

        foreach ($parametersFilterData as $parameterFilterData) {
            if (array_key_exists($parameterFilterData['parameter'], $parameterIdsByUuids) === false) {
                throw new ParameterNotFoundException(sprintf(
                    'Parameter with uuid "%s" was not found',
                    $parameterFilterData['parameter'],
                ));
            }

            $parameterId = $parameterIdsByUuids[$parameterFilterData['parameter']];

            if (count($parameterFilterData['values']) === 0) {
                // slider parameter, minimal and maximal value are the same (see checkPossibilityToFindReadyCategorySeoMix method),
                // so it does not matter which one is used for grabbing the text
                $text = $parameterFilterData['minimalValue'];
                $parameterValueId = $this->parameterFacade->getParameterValueIdByText((string)$text, $currentLocale);
            } else {
                $parameterUuid = reset($parameterFilterData['values']);

                if (array_key_exists($parameterUuid, $parameterValueIdsByUuids) === false) {
                    throw new ParameterValueNotFoundException(sprintf(
                        'Parameter value with uuid "%s" was not found',
                        $parameterUuid,
                    ));
                }

                $parameterValueId = $parameterValueIdsByUuids[$parameterUuid];
            }
            $parameterValueIdsByParameterId[$parameterId] = $parameterValueId;
        }

        return $parameterValueIdsByParameterId;
    }
}
