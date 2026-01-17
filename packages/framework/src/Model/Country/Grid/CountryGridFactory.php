<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Country\Grid;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Model\Country\CountryRepository;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class CountryGridFactory implements GridFactoryInterface
{
    public function __construct(
        protected readonly CountryRepository $countryRepository,
        protected readonly GridFactory $gridFactory,
        protected readonly Localization $localization,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    #[Override]
    public function create(?string $roleConstant): Grid
    {
        $queryBuilder = $this->countryRepository
            ->createSortedJoinedQueryBuilder($this->localization->getCurrentLocaleForTranslatableEntities(), Domain::FIRST_DOMAIN_ID)
            ->addSelect('ct');

        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'c.id');

        $grid = $this->gridFactory->create('CountryList', $dataSource, $roleConstant);

        $grid->addColumn('name', 'ct.name', t('Name'), true);
        $grid->addColumn('code', 'c.code', t('Country code'), true);

        $grid->addEditActionColumn('admin_country_edit', ['id' => 'c.id']);

        $grid->setTheme('@ShopsysAdministration/content/country/listGrid.html.twig');

        return $grid;
    }
}
