<?php

declare(strict_types=1);

namespace App\Model\LanguageConstant;

use App\Component\Grid\ArrayWithPaginationDataSource;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;

class LanguageConstantGridFactory
{
    /**
     * @param \App\Model\LanguageConstant\LanguageConstantFacade $languageConstantFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     */
    public function __construct(
        private readonly LanguageConstantFacade $languageConstantFacade,
        private readonly GridFactory $gridFactory,
    ) {
    }

    /**
     * @param string $locale
     * @param string|null $search
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(string $locale, ?string $search = null): Grid
    {
        $originalTranslations = $this->languageConstantFacade->getOriginalTranslationsByLocaleIndexedByKey($locale);
        $userTranslations = $this->languageConstantFacade->getUserTranslationsByLocaleIndexedByKey($locale);
        $translations = $search !== null
            ? $this->getTranslationsWithSearch($originalTranslations, $userTranslations, $locale, mb_strtolower($search))
            : $this->getTranslations($originalTranslations, $userTranslations, $locale);

        $grid = $this->gridFactory->create('languageConstantList', new ArrayWithPaginationDataSource($translations, 'key'));
        $grid->setDefaultOrder('key');
        $grid->enablePaging();

        $grid->addColumn('locale', 'locale', t('Language'));
        $grid
            ->addColumn('key', 'key', t('Key'), true)
            ->setClassAttribute('table-col table-col-30');
        $grid
            ->addColumn('originalTranslation', 'originalTranslation', t('Original translation'), true)
            ->setClassAttribute('table-col table-col-30');
        $grid
            ->addColumn('userTranslation', 'userTranslation', t('User translation'), true)
            ->setClassAttribute('table-col table-col-30');

        $grid->addEditActionColumn('admin_languageconstant_edit', ['key' => 'key']);
        $grid
            ->addDeleteActionColumn('admin_languageconstant_delete', ['key' => 'key'])
            ->setConfirmMessage(t('Do you really want to remove this language constant translation?'));

        $grid->setTheme('Admin/Content/LanguageConstant/listGrid.html.twig');

        return $grid;
    }

    /**
     * @param string[] $originalTranslations
     * @param string[] $userTranslations
     * @param string $locale
     * @return array<int, array{key: string, locale: string, originalTranslation: string, userTranslation: string}>
     */
    private function getTranslations(array $originalTranslations, array $userTranslations, string $locale): array
    {
        $translations = [];

        foreach ($originalTranslations as $key => $originalTranslation) {
            $translations[] = [
                'key' => $key,
                'locale' => $locale,
                'originalTranslation' => $originalTranslation,
                'userTranslation' => $userTranslations[$key] ?? '',
            ];
        }

        return $translations;
    }

    /**
     * @param string[] $originalTranslations
     * @param string[] $userTranslations
     * @param string $locale
     * @param string $search
     * @return array<int, array{key: string, locale: string, originalTranslation: string, userTranslation: string}>
     */
    private function getTranslationsWithSearch(
        array $originalTranslations,
        array $userTranslations,
        string $locale,
        string $search,
    ): array {
        $translations = [];

        foreach ($originalTranslations as $key => $originalTranslation) {
            $userTranslation = $userTranslations[$key] ?? '';

            if (str_contains(mb_strtolower((string)$key), $search) ||
                str_contains(mb_strtolower($originalTranslation), $search) ||
                str_contains(mb_strtolower($userTranslation), $search)) {
                $translations[] = [
                    'key' => $key,
                    'locale' => $locale,
                    'originalTranslation' => $originalTranslation,
                    'userTranslation' => $userTranslation,
                ];
            }
        }

        return $translations;
    }
}
