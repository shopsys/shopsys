<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Grid;

use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;

class UploadedFileGridFactory extends AbstractUploadedFileGridFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchFormData
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function createWithSearch(QuickSearchFormData $quickSearchFormData): Grid
    {
        $dataSource = $this->createDataSource($quickSearchFormData);

        $grid = $this->createInstance('uploadedFileList', $dataSource);

        $grid->addEditActionColumn('admin_uploadedfile_edit', ['id' => 'u.id']);
        $grid->addDeleteActionColumn('admin_uploadedfile_delete', ['id' => 'u.id'])
            ->setConfirmMessage(t('Do you really want to delete this file? It will be permanently deleted and unassigned from related records.'));

        $grid->setTheme('@ShopsysAdministration/content/uploadedFile/listGrid.html.twig');

        return $grid;
    }
}
