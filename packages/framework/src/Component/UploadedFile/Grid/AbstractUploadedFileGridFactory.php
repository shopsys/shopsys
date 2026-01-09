<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Grid;

use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileAdminListFacade;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;

abstract class AbstractUploadedFileGridFactory
{
    protected const string UPLOADED_FILES_CACHE = 'UPLOADED_FILES_CACHE';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileAdminListFacade $uploadedFileAdminListFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSourceFactory $queryBuilderWithRowManipulatorDataSourceFactory
     * @param \Shopsys\FrameworkBundle\Component\Cache\InMemoryCache $inMemoryCache
     */
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly UploadedFileAdminListFacade $uploadedFileAdminListFacade,
        protected readonly QueryBuilderWithRowManipulatorDataSourceFactory $queryBuilderWithRowManipulatorDataSourceFactory,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchFormData
     * @param callable(array $row, array $rows): array|null $additionalRowManipulator
     * @return \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface
     */
    protected function createDataSource(
        QuickSearchFormData $quickSearchFormData,
        ?callable $additionalRowManipulator = null,
    ): DataSourceInterface {
        $queryBuilder = $this->uploadedFileAdminListFacade->getQueryBuilderByQuickSearchData($quickSearchFormData);

        return $this->queryBuilderWithRowManipulatorDataSourceFactory->create(
            $queryBuilder,
            'u.id',
            function (array $row, array $rows) use ($additionalRowManipulator): array {
                $allFileIds = array_column(array_column($rows, 'u'), 'id');

                $uploadedFile = $this->getUploadedFileById($allFileIds, $row['u']['id']);
                $row['uploadedFile'] = $uploadedFile;
                $row['filename'] = $uploadedFile->getNameWithExtension();

                if ($additionalRowManipulator !== null) {
                    $row = $additionalRowManipulator($row, $rows);
                }

                return $row;
            },
        );
    }

    /**
     * @param int[] $allFileIds
     * @param int $uploadedFileId
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
     */
    protected function getUploadedFileById(array $allFileIds, int $uploadedFileId): UploadedFile
    {
        $uploadedFilesById = $this->inMemoryCache->getOrSaveValue(
            static::UPLOADED_FILES_CACHE,
            fn () => $this->uploadedFileFacade->getByIdsIndexedById($allFileIds),
            static::UPLOADED_FILES_CACHE,
            ...$allFileIds,
        );

        return $uploadedFilesById[$uploadedFileId];
    }

    /**
     * @param string $gridName
     * @param \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface $dataSource
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    protected function createInstance(string $gridName, DataSourceInterface $dataSource): Grid
    {
        $grid = $this->gridFactory->create($gridName, $dataSource, AdminRoleConstant::ROLE_FILES);

        $grid->enablePaging();

        $grid->setDefaultOrder('id', DataSourceInterface::ORDER_DESC);

        $grid->addColumn('filename', 'filename', t('Filename'));
        $grid->addColumn('translatedName', 'ut.name', t('Name'), true);
        $grid->addColumn('extension', 'u.extension', t('Ext.'), true);
        $grid->addColumn('preview', 'u.id', t('Preview'));

        return $grid;
    }
}
