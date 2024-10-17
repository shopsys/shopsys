<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;

class ReadyCategorySeoMixDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixParameterParameterValueFactory $readyCategorySeoMixParameterValueFactory
     */
    public function __construct(
        protected readonly CategoryFacade $categoryFacade,
        protected readonly FlagFacade $flagFacade,
        protected readonly ParameterFacade $parameterFacade,
        protected readonly ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ReadyCategorySeoMixParameterParameterValueFactory $readyCategorySeoMixParameterValueFactory,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixData
     */
    protected function createInstance(): ReadyCategorySeoMixData
    {
        return new ReadyCategorySeoMixData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixData
     */
    public function create(): ReadyCategorySeoMixData
    {
        return $this->createInstance();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ChoseCategorySeoMixCombination|null $choseCategorySeoMixCombination
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixData
     */
    public function createReadyCategorySeoMixData(
        ?ChoseCategorySeoMixCombination $choseCategorySeoMixCombination,
    ): ReadyCategorySeoMixData {
        $readyCategorySeoMix = null;

        if ($choseCategorySeoMixCombination !== null) {
            $readyCategorySeoMix = $this->readyCategorySeoMixFacade->findByChoseCategorySeoMixCombination($choseCategorySeoMixCombination);
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixData $readyCategorySeoMixData
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ChoseCategorySeoMixCombination $choseCategorySeoMixCombination
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
            $flag = $this->flagFacade->getById($choseCategorySeoMixCombination->getFlagId());
            $readyCategorySeoMixData->flag = $flag;
        }

        $readyCategorySeoMixData->ordering = $choseCategorySeoMixCombination->getOrdering();

        $readyCategorySeoMixData->readyCategorySeoMixParameterParameterValues = [];

        foreach ($choseCategorySeoMixCombination->getParameterValueIdsByParameterIds() as $parameterId => $parameterValueId) {
            $readyCategorySeoMixData->readyCategorySeoMixParameterParameterValues[] = $this->readyCategorySeoMixParameterValueFactory->create(
                $this->parameterFacade->getById($parameterId),
                $this->parameterFacade->getParameterValueById($parameterValueId),
            );
        }

        $readyCategorySeoMixData->choseCategorySeoMixCombinationJson = $choseCategorySeoMixCombination->getInJson();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixData $readyCategorySeoMixData
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix
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
