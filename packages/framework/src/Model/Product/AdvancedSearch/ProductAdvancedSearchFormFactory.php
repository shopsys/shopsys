<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\AdvancedSearch;

use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOperatorTranslation;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AbstractAdvancedSearchFormFactory;
use Symfony\Component\Form\FormFactoryInterface;

class ProductAdvancedSearchFormFactory extends AbstractAdvancedSearchFormFactory
{
    public function __construct(
        ProductAdvancedSearchConfig $productAdvancedSearchConfig,
        ProductAdvancedSearchFilterTranslation $productAdvancedSearchFilterTranslation,
        FormFactoryInterface $formFactory,
        AdvancedSearchOperatorTranslation $advancedSearchOperatorTranslation,
    ) {
        parent::__construct(
            $productAdvancedSearchConfig,
            $productAdvancedSearchFilterTranslation,
            $formFactory,
            $advancedSearchOperatorTranslation,
        );
    }
}
