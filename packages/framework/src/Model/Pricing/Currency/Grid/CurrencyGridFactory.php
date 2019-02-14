<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency\Grid;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class CurrencyGridFactory implements GridFactoryInterface
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\GridFactory
     */
    protected $gridFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    protected $currencyFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        GridFactory $gridFactory,
        CurrencyFacade $currencyFacade
    ) {
        $this->em = $em;
        $this->gridFactory = $gridFactory;
        $this->currencyFacade = $currencyFacade;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create()
    {
        $dataSource = $this->getDataSource();
        return $this->getGridForDataSource($dataSource);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource
     */
    protected function getDataSource(): DataSourceInterface
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('c')
            ->from(Currency::class, 'c');
        return new QueryBuilderDataSource($queryBuilder, 'c.id');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface $dataSource
     * @throws \Shopsys\FrameworkBundle\Component\Grid\Exception\DuplicateColumnIdException
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    protected function getGridForDataSource(DataSourceInterface $dataSource): Grid
    {
        $grid = $this->gridFactory->create('currencyList', $dataSource);
        $grid->setDefaultOrder('name');
        $grid->addColumn('name', 'c.name', t('Name'), true);
        $grid->addColumn('code', 'c.code', t('Code'), true);
        $grid->addColumn('exchangeRate', 'c.exchangeRate', t('Exchange rate'), true);
        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addDeleteActionColumn('admin_currency_deleteconfirm', ['id' => 'c.id'])
            ->setAjaxConfirm();

        $grid->setTheme(
            '@ShopsysFramework/Admin/Content/Currency/listGrid.html.twig',
            [
                'defaultCurrency' => $this->currencyFacade->getDefaultCurrency(),
                'notAllowedToDeleteCurrencyIds' => $this->currencyFacade->getNotAllowedToDeleteCurrencyIds(),
            ]
        );

        return $grid;
    }
}
