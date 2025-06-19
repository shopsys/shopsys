<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency\Grid;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class CurrencyGridFactory implements GridFactoryInterface
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GridFactory $gridFactory,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    /**
     * @param string|null $roleConstant
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    #[Override]
    public function create(?string $roleConstant): Grid
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('c')
            ->from(Currency::class, 'c');
        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'c.id');

        $grid = $this->gridFactory->create('currencyList', $dataSource, $roleConstant);
        $grid->setDefaultOrder('name');
        $grid->addColumn('name', 'c.name', t('Name'), true);
        $grid->addColumn('code', 'c.code', t('Code'), true);
        $grid->addColumn('minFractionDigits', 'c.minFractionDigits', t('Min fraction digits'), true);
        $grid->addColumn('roundingType', 'c.roundingType', t('Rounding type'), true);
        $grid->addColumn('exchangeRate', 'c.exchangeRate', t('Exchange rate'), true);
        $grid->addColumn('roundingPlacesPriceWithoutVat', 'c.roundingPlacesPriceWithoutVat', t('Rounding places of price without VAT and VAT amount'), true);

        $grid->addDeleteActionColumn('admin_currency_deleteconfirm', ['id' => 'c.id'])
            ->setAjaxConfirm();

        $grid->setTheme(
            '@ShopsysAdministration/content/currency/listGrid.html.twig',
            [
                'defaultCurrency' => $this->currencyFacade->getDefaultCurrency(),
                'notAllowedToDeleteCurrencyIds' => $this->currencyFacade->getNotAllowedToDeleteCurrencyIds(),
            ],
        );

        return $grid;
    }
}
