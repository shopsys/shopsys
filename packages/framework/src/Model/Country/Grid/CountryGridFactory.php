<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Country\Grid;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Country\CountryRepository;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class CountryGridFactory implements GridFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryRepository $countryRepository
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly CountryRepository $countryRepository,
        protected readonly GridFactory $gridFactory,
        protected readonly Localization $localization,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param string|null $roleConstant
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    #[Override]
    public function create(?string $roleConstant): Grid
    {
        $queryBuilder = $this->countryRepository
            ->createSortedJoinedQueryBuilder($this->localization->getCurrentLocaleForTranslatableEntities(), $this->domain->getId())
            ->addSelect('ct');

        $dataSource = new QueryBuilderDataSource($queryBuilder, 'c.id');

        $grid = $this->gridFactory->create('CountryList', $dataSource, $roleConstant);

        $grid->addColumn('name', 'ct.name', t('Name'), true);
        $grid->addColumn('code', 'c.code', t('Country code'), true);

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_country_edit', ['id' => 'c.id']);

        $grid->setTheme('@ShopsysFramework/Admin/Content/Country/listGrid.html.twig');

        return $grid;
    }
}
