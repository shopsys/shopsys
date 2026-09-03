<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrameworkBundle\Model\Seo\SeoAttributesDataFactory;

class ReadyCategorySeoMixDataFactory
{
    public function __construct(
        protected readonly CategoryFacade $categoryFacade,
        protected readonly FlagFacade $flagFacade,
        protected readonly ParameterFacade $parameterFacade,
        protected readonly ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ReadyCategorySeoMixParameterParameterValueFactory $readyCategorySeoMixParameterValueFactory,
        protected readonly SelectedCategorySeoMixCombinationFactory $selectedCategorySeoMixCombinationFactory,
        protected readonly SeoAttributesDataFactory $seoAttributesDataFactory,
    ) {
    }

    protected function createInstance(): ReadyCategorySeoMixData
    {
        $readyCategorySeoMixData = new ReadyCategorySeoMixData();
        $readyCategorySeoMixData->seo = $this->seoAttributesDataFactory->create();

        return $readyCategorySeoMixData;
    }

    public function create(): ReadyCategorySeoMixData
    {
        return $this->createInstance();
    }

    public function createReadyCategorySeoMixData(
        ?SelectedCategorySeoMixCombination $selectedCategorySeoMixCombination,
    ): ReadyCategorySeoMixData {
        $readyCategorySeoMix = null;

        if ($selectedCategorySeoMixCombination !== null) {
            $readyCategorySeoMix = $this->readyCategorySeoMixFacade->findBySelectedCategorySeoMixCombination($selectedCategorySeoMixCombination);
        }

        $readyCategorySeoMixData = $this->createInstance();

        $readyCategorySeoMixData->urls = new UrlListData();

        if ($readyCategorySeoMix !== null) {
            $this->fillValuesFromReadyCategorySeoMix($readyCategorySeoMixData, $readyCategorySeoMix);

            $mainFriendlyUrl = $this->friendlyUrlFacade->findMainFriendlyUrl(
                $readyCategorySeoMix->getDomainId(),
                'front_category_seo',
                $readyCategorySeoMix->getId(),
            );
            $readyCategorySeoMixData->urls->mainFriendlyUrlsByDomainId[$readyCategorySeoMix->getDomainId()] = $mainFriendlyUrl;
        }

        return $readyCategorySeoMixData;
    }

    public function fillValuesFromSelectedCategorySeoMixCombination(
        ReadyCategorySeoMixData $readyCategorySeoMixData,
        SelectedCategorySeoMixCombination $selectedCategorySeoMixCombination,
    ): void {
        $readyCategorySeoMixData->domainId = $selectedCategorySeoMixCombination->getDomainId();

        $readyCategorySeoMixData->category = $this->categoryFacade->getById(
            $selectedCategorySeoMixCombination->getCategoryId(),
        );

        $readyCategorySeoMixData->flag = null;

        if ($selectedCategorySeoMixCombination->getFlagId() !== null) {
            $flag = $this->flagFacade->getById($selectedCategorySeoMixCombination->getFlagId());
            $readyCategorySeoMixData->flag = $flag;
        }

        $readyCategorySeoMixData->ordering = $selectedCategorySeoMixCombination->getOrdering();

        $readyCategorySeoMixData->readyCategorySeoMixParameterParameterValues = [];

        foreach ($selectedCategorySeoMixCombination->getParameterValueIdsByParameterIds() as $parameterId => $parameterValueId) {
            $readyCategorySeoMixData->readyCategorySeoMixParameterParameterValues[] = $this->readyCategorySeoMixParameterValueFactory->create(
                $this->parameterFacade->getById($parameterId),
                $this->parameterFacade->getParameterValueById($parameterValueId),
            );
        }

        $readyCategorySeoMixData->selectedCategorySeoMixCombinationJson = $this->selectedCategorySeoMixCombinationFactory->createJsonFromSelectedCategorySeoMixCombination($selectedCategorySeoMixCombination);
    }

    public function fillValuesFromReadyCategorySeoMix(
        ReadyCategorySeoMixData $readyCategorySeoMixData,
        ReadyCategorySeoMix $readyCategorySeoMix,
    ): void {
        $readyCategorySeoMixData->seo = $this->seoAttributesDataFactory->createFromSeoAttributes(
            $readyCategorySeoMix->getSeoAttributes(),
        );
        $readyCategorySeoMixData->shortDescription = $readyCategorySeoMix->getShortDescription();
        $readyCategorySeoMixData->description = $readyCategorySeoMix->getDescription();
        $readyCategorySeoMixData->showInCategory = $readyCategorySeoMix->showInCategory();
    }
}
