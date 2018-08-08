<?php

namespace Shopsys\ProductFeed\HeurekaBundle\Form;

use Shopsys\Plugin\PluginCrudExtensionInterface;
use Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategory;
use Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryFacade;
use Symfony\Component\Translation\TranslatorInterface;

class CategoryCrudExtension implements PluginCrudExtensionInterface
{
    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @var \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryFacade
     */
    private $heurekaCategoryFacade;

    public function __construct(
        TranslatorInterface $translator,
        HeurekaCategoryFacade $heurekaCategoryFacade
    ) {
        $this->translator = $translator;
        $this->heurekaCategoryFacade = $heurekaCategoryFacade;
    }

    public function getFormTypeClass(): string
    {
        return CategoryFormType::class;
    }

    public function getFormLabel(): string
    {
        return $this->translator->trans('Heureka.cz product feed');
    }

    public function getData(int $categoryId): array
    {
        $heurekaCategory = $this->heurekaCategoryFacade->findByCategoryId($categoryId);

        $pluginData = [];
        if ($heurekaCategory !== null) {
            $pluginData['heureka_category'] = $heurekaCategory;
        }

        return $pluginData;
    }

    public function saveData(int $categoryId, array $data): void
    {
        if (isset($data['heureka_category']) && $data['heureka_category'] instanceof HeurekaCategory) {
            $this->heurekaCategoryFacade->changeHeurekaCategoryForCategoryId($categoryId, $data['heureka_category']);
        } else {
            $this->heurekaCategoryFacade->removeHeurekaCategoryForCategoryId($categoryId);
        }
    }

    public function removeData(int $categoryId): void
    {
        $this->heurekaCategoryFacade->removeHeurekaCategoryForCategoryId($categoryId);
    }
}
