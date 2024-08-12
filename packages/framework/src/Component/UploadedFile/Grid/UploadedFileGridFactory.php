<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Grid;

use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileAdminListFacade;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;

class UploadedFileGridFactory extends AbstractUploadedFileGridFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileAdminListFacade $uploadedFileAdminListFacade
     */
    public function __construct(
        GridFactory $gridFactory,
        protected readonly UploadedFileAdminListFacade $uploadedFileAdminListFacade,
    ) {
        parent::__construct($gridFactory);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchFormData
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function createWithSearch(QuickSearchFormData $quickSearchFormData): Grid
    {
        $queryBuilder = $this->uploadedFileAdminListFacade->getQueryBuilderByQuickSearchData($quickSearchFormData);

        $dataSource = new QueryBuilderWithRowManipulatorDataSource(
            $queryBuilder,
            'u.id',
            function ($row) {
                $row['filename'] = sprintf('%s.%s', $row['u']['name'], $row['u']['extension']);

                return $row;
            },
        );

        $grid = $this->createInstance('uploadedFileList', $dataSource);

        $grid->addEditActionColumn('admin_uploadedfile_edit', ['id' => 'u.id']);
        $grid->addDeleteActionColumn('admin_uploadedfile_delete', ['id' => 'u.id'])
            ->setConfirmMessage(t('Do you really want to delete this file? It will be permanently deleted and unassigned from related records.'));

        $grid->setTheme('@ShopsysFramework/Admin/Content/UploadedFile/listGrid.html.twig');

        return $grid;
    }
}
