<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\Plugin\PluginCrudExtensionInterface;
use Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategory;
use Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryDownloader;
use Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryFacade;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CategoryCrudExtension implements PluginCrudExtensionInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly HeurekaCategoryFacade $heurekaCategoryFacade,
        private readonly Domain $domain,
        private readonly HeurekaCategoryDownloader $heurekaCategoryDownloader,
    ) {
    }

    #[Override]
    public function getFormTypeClass(): string
    {
        return CategoryFormType::class;
    }

    #[Override]
    public function getFormLabel(): string
    {
        return $this->translator->trans('Heureka product feed');
    }

    #[Override]
    public function getData(int $categoryId): array
    {
        $pluginData = [];

        foreach ($this->heurekaCategoryDownloader->getSupportedLocales() as $locale) {
            if (!$this->domain->anyDomainHasLocale($locale)) {
                continue;
            }

            $heurekaCategory = $this->heurekaCategoryFacade->findByCategoryIdAndLocale($categoryId, $locale);

            if ($heurekaCategory !== null) {
                $pluginData[self::createFormFieldKeyByLocale($locale)] = $heurekaCategory;
            }
        }

        return $pluginData;
    }

    #[Override]
    public function saveData(int $categoryId, mixed $data): void
    {
        foreach ($this->heurekaCategoryDownloader->getSupportedLocales() as $locale) {
            if (!$this->domain->anyDomainHasLocale($locale)) {
                continue;
            }

            $key = self::createFormFieldKeyByLocale($locale);

            if (isset($data[$key]) && $data[$key] instanceof HeurekaCategory) {
                $this->heurekaCategoryFacade->changeHeurekaCategoryForCategoryId($categoryId, $data[$key], $locale);
            } else {
                $this->heurekaCategoryFacade->removeHeurekaCategoryForCategoryId($categoryId, $locale);
            }
        }
    }

    #[Override]
    public function removeData(int $categoryId): void
    {
        foreach ($this->heurekaCategoryDownloader->getSupportedLocales() as $locale) {
            $this->heurekaCategoryFacade->removeHeurekaCategoryForCategoryId($categoryId, $locale);
        }
    }

    public static function createFormFieldKeyByLocale(string $locale): string
    {
        return 'heureka_' . $locale . '_category';
    }
}
