<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Grid;

use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;

class FilePickerGridFactory extends AbstractUploadedFileGridFactory
{
    protected const string UPLOADED_FILE_TRANSLATIONS_CACHE = 'UPLOADED_FILE_TRANSLATIONS_CACHE';

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
        $dataSource = $this->createDataSource(
            $quickSearchFormData,
            function (array $row, array $rows): array {
                $allFileIds = array_column(array_column($rows, 'u'), 'id');
                $row['names'] = $this->getTranslationsForUploadedFile($allFileIds, $row['u']['id']);

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

    /**
     * @param int[] $allFileIds
     * @param int $uploadedFileId
     * @return array<string, string>
     */
    protected function getTranslationsForUploadedFile(array $allFileIds, int $uploadedFileId): array
    {
        $translationsByFileId = $this->inMemoryCache->getOrSaveValue(
            static::UPLOADED_FILE_TRANSLATIONS_CACHE,
            fn () => $this->uploadedFileFacade->getTranslationsIndexedByLocaleForUploadedFileIds($allFileIds),
            static::UPLOADED_FILE_TRANSLATIONS_CACHE,
            ...$allFileIds,
        );

        return $translationsByFileId[$uploadedFileId] ?? [];
    }
}
