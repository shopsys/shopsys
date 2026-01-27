<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\AdvancedSearch;

use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOperatorTranslation;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AbstractAdvancedSearchFormFactory;
use Symfony\Component\Form\FormFactoryInterface;

class OrderAdvancedSearchFormFactory extends AbstractAdvancedSearchFormFactory
{
    public function __construct(
        OrderAdvancedSearchConfig $orderAdvancedSearchConfig,
        OrderAdvancedSearchFilterTranslation $orderAdvancedSearchFilterTranslation,
        FormFactoryInterface $formFactory,
        AdvancedSearchOperatorTranslation $advancedSearchOperatorTranslation,
    ) {
        parent::__construct(
            $orderAdvancedSearchConfig,
            $orderAdvancedSearchFilterTranslation,
            $formFactory,
            $advancedSearchOperatorTranslation,
        );
    }
}
