<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Grid;

use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileAdminListFacade;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;

class FilePickerGridFactory extends AbstractUploadedFileGridFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileAdminListFacade $uploadedFileAdminListFacade
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     */
    public function __construct(
        GridFactory $gridFactory,
        protected readonly UploadedFileAdminListFacade $uploadedFileAdminListFacade,
        protected readonly UploadedFileFacade $uploadedFileFacade,
    ) {
        parent::__construct($gridFactory);
    }

    /**
     * @param string $jsInstanceId
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchFormData
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function createWithSearch(
        string $jsInstanceId,
        QuickSearchFormData $quickSearchFormData,
    ): Grid {
        $queryBuilder = $this->uploadedFileAdminListFacade->getQueryBuilderByQuickSearchData($quickSearchFormData);

        $dataSource = new QueryBuilderWithRowManipulatorDataSource(
            $queryBuilder,
            'u.id',
            function ($row) {
                $uploadedFile = $this->uploadedFileFacade->getById($row['u']['id']);
                $row['filename'] = $uploadedFile->getNameWithExtension();
                $row['uploadedFile'] = $uploadedFile;
                $row['names'] = $this->uploadedFileFacade->getTranslationsIndexedByLocaleForUploadedFileId($uploadedFile->getId());

                return $row;
            },
        );

        $grid = $this->createInstance('filePicker', $dataSource);

        $grid->addColumn('select', 'u.id', '')
            ->setClassAttribute('table-col table-col-15 text-center');

        $grid->setTheme('@ShopsysFramework/Admin/Content/FilePicker/listGrid.html.twig', [
            'isMultiple' => true,
            'jsInstanceId' => $jsInstanceId,
        ]);

        return $grid;
    }
}
