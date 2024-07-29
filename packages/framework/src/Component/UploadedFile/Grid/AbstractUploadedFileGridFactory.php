<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Grid;

use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;

class AbstractUploadedFileGridFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     */
    public function __construct(protected readonly GridFactory $gridFactory)
    {
    }

    /**
     * @param string $gridName
     * @param \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface $dataSource
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    protected function createInstance(string $gridName, DataSourceInterface $dataSource): Grid
    {
        $grid = $this->gridFactory->create($gridName, $dataSource);

        $grid->enablePaging();

        $grid->setDefaultOrder('id', DataSourceInterface::ORDER_DESC);

        $grid->addColumn('filename', 'filename', t('Filename'));
        $grid->addColumn('translatedName', 'ut.name', t('Name'), true);
        $grid->addColumn('extension', 'u.extension', t('Ext.'), true);

        return $grid;
    }
}
