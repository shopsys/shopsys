<?php

declare(strict_types=1);

namespace App\Model\CategorySeo;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\Model\Category\CategoryFacade;
use App\Model\Product\Flag\FlagFacade;
use App\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;

class ReadyCategorySeoMixDataFactory
{
    /**
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\Product\Flag\FlagFacade $flagFacade
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        private readonly CategoryFacade $categoryFacade,
        private readonly FlagFacade $flagFacade,
        private readonly ParameterFacade $parameterFacade,
        private readonly ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
        private readonly FriendlyUrlFacade $friendlyUrlFacade,
    ) {
    }

    /**
     * @return \App\Model\CategorySeo\ReadyCategorySeoMixData
     */
    public function create(): ReadyCategorySeoMixData
    {
        return new ReadyCategorySeoMixData();
    }

    /**
     * @param \App\Model\CategorySeo\ChoseCategorySeoMixCombination|null $choseCategorySeoMixCombination
     * @return \App\Model\CategorySeo\ReadyCategorySeoMixData
     */
    public function createReadyCategorySeoMixData(
        ?ChoseCategorySeoMixCombination $choseCategorySeoMixCombination,
    ): ReadyCategorySeoMixData {
        $readyCategorySeoMix = null;

        if ($choseCategorySeoMixCombination !== null) {
            $readyCategorySeoMix = $this->readyCategorySeoMixFacade->findByChoseCategorySeoMixCombination($choseCategorySeoMixCombination);
        }

        $readyCategorySeoMixData = new ReadyCategorySeoMixData();

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

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixData $readyCategorySeoMixData
     * @param \App\Model\CategorySeo\ChoseCategorySeoMixCombination $choseCategorySeoMixCombination
     */
    public function fillValuesFromChoseCategorySeoMixCombination(
        ReadyCategorySeoMixData $readyCategorySeoMixData,
        ChoseCategorySeoMixCombination $choseCategorySeoMixCombination,
    ): void {
        $readyCategorySeoMixData->domainId = $choseCategorySeoMixCombination->getDomainId();

        $readyCategorySeoMixData->category = $this->categoryFacade->getById(
            $choseCategorySeoMixCombination->getCategoryId(),
        );

        $readyCategorySeoMixData->flag = null;

        if ($choseCategorySeoMixCombination->getFlagId() !== null) {
            /** @var \App\Model\Product\Flag\Flag $flag */
            $flag = $this->flagFacade->getById($choseCategorySeoMixCombination->getFlagId());
            $readyCategorySeoMixData->flag = $flag;
        }

        $readyCategorySeoMixData->ordering = $choseCategorySeoMixCombination->getOrdering();

        $readyCategorySeoMixData->readyCategorySeoMixParameterParameterValues = [];

        foreach ($choseCategorySeoMixCombination->getParameterValueIdsByParameterIds() as $parameterId => $parameterValueId) {
            $readyCategorySeoMixData->readyCategorySeoMixParameterParameterValues[] = new ReadyCategorySeoMixParameterParameterValue(
                $this->parameterFacade->getById($parameterId),
                $this->parameterFacade->getParameterValueById($parameterValueId),
            );
        }

        $readyCategorySeoMixData->choseCategorySeoMixCombinationJson = $choseCategorySeoMixCombination->getInJson();
    }

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixData $readyCategorySeoMixData
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix
     */
    public function fillValuesFromReadyCategorySeoMix(
        ReadyCategorySeoMixData $readyCategorySeoMixData,
        ReadyCategorySeoMix $readyCategorySeoMix,
    ): void {
        $readyCategorySeoMixData->h1 = $readyCategorySeoMix->getH1();
        $readyCategorySeoMixData->shortDescription = $readyCategorySeoMix->getShortDescription();
        $readyCategorySeoMixData->description = $readyCategorySeoMix->getDescription();
        $readyCategorySeoMixData->title = $readyCategorySeoMix->getTitle();
        $readyCategorySeoMixData->metaDescription = $readyCategorySeoMix->getMetaDescription();
        $readyCategorySeoMixData->showInCategory = $readyCategorySeoMix->showInCategory();
    }
}
