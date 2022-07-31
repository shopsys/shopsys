<?php

namespace Shopsys\ProductFeed\HeurekaBundle\Form;

use Shopsys\Plugin\PluginCrudExtensionInterface;
use Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategory;
use Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryFacade;
use Symfony\Contracts\Translation\TranslatorInterface;

class CategoryCrudExtension implements PluginCrudExtensionInterface
{
    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @var \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryFacade
     */
    private $heurekaCategoryFacade;

    /**
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryFacade $heurekaCategoryFacade
     */
    public function __construct(
        TranslatorInterface $translator,
        HeurekaCategoryFacade $heurekaCategoryFacade
    ) {
        $this->translator = $translator;
        $this->heurekaCategoryFacade = $heurekaCategoryFacade;
    }

    /**
     * @return string
     */
    public function getFormTypeClass(): string
    {
        return CategoryFormType::class;
    }

    /**
     * @return string
     */
    public function getFormLabel(): string
    {
        return $this->translator->trans('Heureka.cz product feed');
    }

    /**
     * @param int $categoryId
     * @return array{'heureka_category': \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategory}
     */
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
     * @param array{'heureka_category': \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategory} $data
     */
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
    public function removeData($categoryId): void
    {
        $this->heurekaCategoryFacade->removeHeurekaCategoryForCategoryId($categoryId);
    }
}
