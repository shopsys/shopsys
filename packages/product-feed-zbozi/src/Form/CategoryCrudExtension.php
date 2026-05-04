<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\Plugin\PluginCrudExtensionInterface;
use Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategory;
use Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryDownloader;
use Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryFacade;
use Symfony\Contracts\Translation\TranslatorInterface;

class CategoryCrudExtension implements PluginCrudExtensionInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ZboziCategoryFacade $zboziCategoryFacade,
        private readonly Domain $domain,
        private readonly ZboziCategoryDownloader $zboziCategoryDownloader,
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
        return $this->translator->trans('Zbozi.cz product feed');
    }

    #[Override]
    public function getData(int $categoryId): array
    {
        $pluginData = [];

        foreach ($this->zboziCategoryDownloader->getSupportedLocales() as $locale) {
            if (!$this->domain->anyDomainHasLocale($locale)) {
                continue;
            }

            $zboziCategory = $this->zboziCategoryFacade->findByCategoryIdAndLocale($categoryId, $locale);

            if ($zboziCategory !== null) {
                $pluginData[self::createFormFieldKeyByLocale($locale)] = $zboziCategory;
            }
        }

        return $pluginData;
    }

    #[Override]
    public function saveData(int $categoryId, mixed $data): void
    {
        foreach ($this->zboziCategoryDownloader->getSupportedLocales() as $locale) {
            if (!$this->domain->anyDomainHasLocale($locale)) {
                continue;
            }

            $key = self::createFormFieldKeyByLocale($locale);

            if (isset($data[$key]) && $data[$key] instanceof ZboziCategory) {
                $this->zboziCategoryFacade->changeZboziCategoryForCategoryId($categoryId, $data[$key], $locale);
            } else {
                $this->zboziCategoryFacade->removeZboziCategoryForCategoryId($categoryId, $locale);
            }
        }
    }

    #[Override]
    public function removeData(int $categoryId): void
    {
        foreach ($this->zboziCategoryDownloader->getSupportedLocales() as $locale) {
            $this->zboziCategoryFacade->removeZboziCategoryForCategoryId($categoryId, $locale);
        }
    }

    public static function createFormFieldKeyByLocale(string $locale): string
    {
        return 'zbozi_' . $locale . '_category';
    }
}
