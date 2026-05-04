<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\DataFixtures;

use Override;
use Shopsys\Plugin\PluginDataFixtureInterface;
use Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryDataFactory;
use Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryFacade;

class ZboziCategoryDataFixture implements PluginDataFixtureInterface
{
    protected const ZBOZI_CATEGORY_ID_FIRST = 10;
    protected const ZBOZI_CATEGORY_ID_SECOND = 11;
    protected const ZBOZI_CATEGORY_ID_THIRD = 12;

    protected const CATEGORY_ID_FIRST = 2;
    protected const CATEGORY_ID_SECOND = 3;
    protected const CATEGORY_ID_THIRD = 4;
    protected const CATEGORY_ID_FOURTH = 5;
    protected const CATEGORY_ID_FIFTH = 6;

    public function __construct(
        private readonly ZboziCategoryFacade $zboziCategoryFacade,
        private readonly ZboziCategoryDataFactory $zboziCategoryDataFactory,
    ) {
    }

    #[Override]
    public function load(): void
    {
        $czechLocale = 'cs';
        $zboziCategoriesData = [];

        $firstZboziCategoryData = $this->zboziCategoryDataFactory->create($czechLocale);
        $firstZboziCategoryData->zboziId = static::ZBOZI_CATEGORY_ID_FIRST;
        $firstZboziCategoryData->name = 'Blesky';
        $firstZboziCategoryData->fullName = 'Foto | Foto doplnky a prislusenstvi | Blesky';
        $zboziCategoriesData[] = $firstZboziCategoryData;

        $secondZboziCategoryData = $this->zboziCategoryDataFactory->create($czechLocale);
        $secondZboziCategoryData->zboziId = static::ZBOZI_CATEGORY_ID_SECOND;
        $secondZboziCategoryData->name = 'Objektivy';
        $secondZboziCategoryData->fullName = 'Foto | Foto doplnky a prislusenstvi | Objektivy';
        $zboziCategoriesData[] = $secondZboziCategoryData;

        $thirdZboziCategoryData = $this->zboziCategoryDataFactory->create($czechLocale);
        $thirdZboziCategoryData->zboziId = static::ZBOZI_CATEGORY_ID_THIRD;
        $thirdZboziCategoryData->name = 'Predsadky, filtry a krytky';
        $thirdZboziCategoryData->fullName = 'Foto | Foto doplnky a prislusenstvi | Predsadky, filtry a krytky';
        $zboziCategoriesData[] = $thirdZboziCategoryData;

        $this->zboziCategoryFacade->saveZboziCategories($zboziCategoriesData, $czechLocale);

        $zboziCategoryFirst = $this->zboziCategoryFacade->getOneByZboziIdAndLocale(
            static::ZBOZI_CATEGORY_ID_FIRST,
            $czechLocale,
        );
        $this->zboziCategoryFacade->changeZboziCategoryForCategoryId(
            static::CATEGORY_ID_FIRST,
            $zboziCategoryFirst,
            $czechLocale,
        );

        $zboziCategorySecond = $this->zboziCategoryFacade->getOneByZboziIdAndLocale(
            static::ZBOZI_CATEGORY_ID_SECOND,
            $czechLocale,
        );
        $this->zboziCategoryFacade->changeZboziCategoryForCategoryId(
            static::CATEGORY_ID_SECOND,
            $zboziCategorySecond,
            $czechLocale,
        );
        $this->zboziCategoryFacade->changeZboziCategoryForCategoryId(
            static::CATEGORY_ID_THIRD,
            $zboziCategorySecond,
            $czechLocale,
        );

        $zboziCategoryThird = $this->zboziCategoryFacade->getOneByZboziIdAndLocale(
            static::ZBOZI_CATEGORY_ID_THIRD,
            $czechLocale,
        );
        $this->zboziCategoryFacade->changeZboziCategoryForCategoryId(
            static::CATEGORY_ID_FOURTH,
            $zboziCategoryThird,
            $czechLocale,
        );
        $this->zboziCategoryFacade->changeZboziCategoryForCategoryId(
            static::CATEGORY_ID_FIFTH,
            $zboziCategoryThird,
            $czechLocale,
        );
    }
}
