<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Grid;

use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSourceFactory;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileAdminListFacade;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;

class FilePickerGridFactory extends AbstractUploadedFileGridFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileAdminListFacade $uploadedFileAdminListFacade
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSourceFactory $queryBuilderWithRowManipulatorDataSourceFactory
     */
    public function __construct(
        GridFactory $gridFactory,
        protected readonly UploadedFileAdminListFacade $uploadedFileAdminListFacade,
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly QueryBuilderWithRowManipulatorDataSourceFactory $queryBuilderWithRowManipulatorDataSourceFactory,
    ) {
        parent::__construct($gridFactory);
    }

    /**
     * @param string $jsInstanceId
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchFormData
     * @param bool $isMultiple
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function createWithSearch(
        string $jsInstanceId,
        QuickSearchFormData $quickSearchFormData,
        bool $isMultiple,
    ): Grid {
        $queryBuilder = $this->uploadedFileAdminListFacade->getQueryBuilderByQuickSearchData($quickSearchFormData);

        $dataSource = $this->queryBuilderWithRowManipulatorDataSourceFactory->create(
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
            ->setClassAttribute('text-center');

        $grid->setTheme('@ShopsysAdministration/content/filePicker/listGrid.html.twig', [
            'jsInstanceId' => $jsInstanceId,
            'isMultiple' => $isMultiple,
        ]);

        return $grid;
    }
}
