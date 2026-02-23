<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\DataFixtures;

use Override;
use Shopsys\Plugin\PluginDataFixtureInterface;
use Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryDataFactory;
use Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryFacade;

class HeurekaCategoryDataFixture implements PluginDataFixtureInterface
{
    protected const HEUREKA_CATEGORY_ID_FIRST = 1;
    protected const HEUREKA_CATEGORY_ID_SECOND = 2;
    protected const HEUREKA_CATEGORY_ID_THIRD = 3;

    protected const CATEGORY_ID_FIRST = 2;
    protected const CATEGORY_ID_SECOND = 3;
    protected const CATEGORY_ID_THIRD = 4;
    protected const CATEGORY_ID_FOURTH = 5;
    protected const CATEGORY_ID_FIFTH = 6;

    public function __construct(
        private readonly HeurekaCategoryFacade $heurekaCategoryFacade,
        private readonly HeurekaCategoryDataFactory $heurekaCategoryDataFactory,
    ) {
    }

    #[Override]
    public function load(): void
    {
        $czechLocale = 'cs';
        $heurekaCategoriesData = [];

        $firstHeurekaCategoryData = $this->heurekaCategoryDataFactory->create($czechLocale);
        $firstHeurekaCategoryData->heurekaId = static::HEUREKA_CATEGORY_ID_FIRST;
        $firstHeurekaCategoryData->name = 'Autobaterie';
        $firstHeurekaCategoryData->fullName = 'Heureka.cz | Auto-moto | Autodoplňky | Autobaterie';

        $heurekaCategoriesData[] = $firstHeurekaCategoryData;

        $secondHeurekaCategoryData = $this->heurekaCategoryDataFactory->create($czechLocale);
        $secondHeurekaCategoryData->heurekaId = static::HEUREKA_CATEGORY_ID_SECOND;
        $secondHeurekaCategoryData->name = 'Bublifuky';
        $secondHeurekaCategoryData->fullName = 'Heureka.cz | Dětské zboží | Hračky | Hry na zahradu | Bublifuky';

        $heurekaCategoriesData[] = $secondHeurekaCategoryData;

        $thirdHeurekaCategoryData = $this->heurekaCategoryDataFactory->create($czechLocale);
        $thirdHeurekaCategoryData->heurekaId = static::HEUREKA_CATEGORY_ID_THIRD;
        $thirdHeurekaCategoryData->name = 'Cukřenky';
        $thirdHeurekaCategoryData->fullName = 'Heureka.cz | Dům a zahrada | Domácnost | Kuchyně | Stolování | Cukřenky';

        $heurekaCategoriesData[] = $thirdHeurekaCategoryData;

        $this->heurekaCategoryFacade->saveHeurekaCategories($heurekaCategoriesData, $czechLocale);

        $heurekaCategoryFirst = $this->heurekaCategoryFacade->getOneByHeurekaIdAndLocale(
            static::HEUREKA_CATEGORY_ID_FIRST,
            $czechLocale,
        );
        $this->heurekaCategoryFacade->changeHeurekaCategoryForCategoryId(
            static::CATEGORY_ID_FIRST,
            $heurekaCategoryFirst,
            $czechLocale,
        );

        $heurekaCategorySecond = $this->heurekaCategoryFacade->getOneByHeurekaIdAndLocale(
            static::HEUREKA_CATEGORY_ID_SECOND,
            $czechLocale,
        );
        $this->heurekaCategoryFacade->changeHeurekaCategoryForCategoryId(
            static::CATEGORY_ID_SECOND,
            $heurekaCategorySecond,
            $czechLocale,
        );
        $this->heurekaCategoryFacade->changeHeurekaCategoryForCategoryId(
            static::CATEGORY_ID_THIRD,
            $heurekaCategorySecond,
            $czechLocale,
        );

        $heurekaCategoryThird = $this->heurekaCategoryFacade->getOneByHeurekaIdAndLocale(
            static::HEUREKA_CATEGORY_ID_THIRD,
            $czechLocale,
        );
        $this->heurekaCategoryFacade->changeHeurekaCategoryForCategoryId(
            static::CATEGORY_ID_FOURTH,
            $heurekaCategoryThird,
            $czechLocale,
        );
        $this->heurekaCategoryFacade->changeHeurekaCategoryForCategoryId(
            static::CATEGORY_ID_FIFTH,
            $heurekaCategoryThird,
            $czechLocale,
        );
    }
}
