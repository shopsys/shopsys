<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\LanguageConstant;

use Shopsys\FrameworkBundle\Component\Grid\ArrayWithPaginationDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;

class LanguageConstantGridFactory
{
    public function __construct(
        protected readonly LanguageConstantFacade $languageConstantFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly LanguageConstantRepository $languageConstantRepository,
        protected readonly ArrayWithPaginationDataSourceFactory $arrayWithPaginationDataSourceFactory,
    ) {
    }

    public function create(string $locale, ?string $search = null): Grid
    {
        $allOriginalTranslations = $this->languageConstantFacade->getAllOriginalTranslationsByLocaleIndexedByNamespace($locale);
        $allUserTranslations = $this->languageConstantFacade->getAllUserTranslationsByLocaleIndexedByNamespacedKey($locale);
        $translations = $search !== null
            ? $this->getTranslationsWithSearch($allOriginalTranslations, $allUserTranslations, $locale, mb_strtolower($search))
            : $this->getTranslations($allOriginalTranslations, $allUserTranslations, $locale);

        $grid = $this->gridFactory->create('languageConstantList', $this->arrayWithPaginationDataSourceFactory->create($translations, 'key'), AdminRoleConstant::ROLE_LANGUAGE_CONSTANTS);
        $grid->setDefaultOrder('key');
        $grid->enablePaging();

        $grid->addColumn('locale', 'locale', t('Language'));

        $grid
            ->addColumn('namespace', 'namespace', t('Namespace'), true)
            ->setClassAttribute('table-col table-col-10');
        $grid
            ->addColumn('key', 'key', t('Key'), true)
            ->setClassAttribute('table-col table-col-30');
        $grid
            ->addColumn('originalTranslation', 'originalTranslation', t('Original translation'), true)
            ->setClassAttribute('table-col table-col-30');
        $grid
            ->addColumn('userTranslation', 'userTranslation', t('User translation'), true)
            ->setClassAttribute('table-col table-col-30');

        $grid->addEditActionColumn('admin_languageconstant_edit', [
            'key' => 'key',
            'namespace' => 'namespace',
        ]);
        $grid
            ->addDeleteActionColumn('admin_languageconstant_delete', [
                'key' => 'key',
                'namespace' => 'namespace',
            ])
            ->setConfirmMessage(t('Do you really want to remove this language constant translation?'));

        $grid->setTheme('@ShopsysAdministration/content/languageConstant/listGrid.html.twig');

        return $grid;
    }

    /**
     * @param array<string, string[]> $allOriginalTranslations
     * @param string[] $allUserTranslations
     * @return array<int, array{key: string, locale: string, namespace: string, originalTranslation: string, userTranslation: string}>
     */
    protected function getTranslations(
        array $allOriginalTranslations,
        array $allUserTranslations,
        string $locale,
    ): array {
        $translations = [];

        foreach ($allOriginalTranslations as $namespace => $originalTranslations) {
            foreach ($originalTranslations as $key => $originalTranslation) {
                // Create a namespace-specific key for user translations lookup
                $namespacedKey = $this->languageConstantRepository->createNamespacedKey($namespace, (string)$key);
                $translations[] = [
                    'key' => $key,
                    'locale' => $locale,
                    'namespace' => $namespace,
                    'originalTranslation' => $originalTranslation,
                    'userTranslation' => $allUserTranslations[$namespacedKey] ?? '',
                ];
            }
        }

        return $translations;
    }

    /**
     * @param array<string, string[]> $allOriginalTranslations
     * @param string[] $allUserTranslations
     * @return array<int, array{key: string, locale: string, namespace: string, originalTranslation: string, userTranslation: string}>
     */
    protected function getTranslationsWithSearch(
        array $allOriginalTranslations,
        array $allUserTranslations,
        string $locale,
        string $search,
    ): array {
        $translations = [];

        foreach ($allOriginalTranslations as $namespace => $originalTranslations) {
            foreach ($originalTranslations as $key => $originalTranslation) {
                $namespacedKey = $this->languageConstantRepository->createNamespacedKey($namespace, (string)$key);
                $userTranslation = $allUserTranslations[$namespacedKey] ?? '';

                if (str_contains(mb_strtolower((string)$key), $search) ||
                    str_contains(mb_strtolower($originalTranslation), $search) ||
                    str_contains(mb_strtolower($userTranslation), $search)) {
                    $translations[] = [
                        'key' => $key,
                        'locale' => $locale,
                        'namespace' => $namespace,
                        'originalTranslation' => $originalTranslation,
                        'userTranslation' => $userTranslation,
                    ];
                }
            }
        }

        return $translations;
    }
}
