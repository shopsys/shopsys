<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Form;

use Override;
use Shopsys\Plugin\PluginCrudExtensionInterface;
use Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategory;
use Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryFacade;
use Symfony\Contracts\Translation\TranslatorInterface;

class CategoryCrudExtension implements PluginCrudExtensionInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly HeurekaCategoryFacade $heurekaCategoryFacade,
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
        return $this->translator->trans('Heureka.cz product feed');
    }

    /**
     * @param int $categoryId
     */
    #[Override]
    public function getData($categoryId): array
    {
        $heurekaCategory = $this->heurekaCategoryFacade->findByCategoryId($categoryId);

        $pluginData = [];

        if ($heurekaCategory !== null) {
            $pluginData['heureka_category'] = $heurekaCategory;
        }

        return $pluginData;
    }

    /**
     * @param int $categoryId
     * @param array $data
     */
    #[Override]
    public function saveData($categoryId, $data): void
    {
        if (isset($data['heureka_category']) && $data['heureka_category'] instanceof HeurekaCategory) {
            $this->heurekaCategoryFacade->changeHeurekaCategoryForCategoryId($categoryId, $data['heureka_category']);
        } else {
            $this->heurekaCategoryFacade->removeHeurekaCategoryForCategoryId($categoryId);
        }
    }

    /**
     * @param int $categoryId
     */
    #[Override]
    public function removeData($categoryId): void
    {
        $this->heurekaCategoryFacade->removeHeurekaCategoryForCategoryId($categoryId);
    }
}
