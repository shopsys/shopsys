<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\AdvancedSearch;

use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOperatorTranslation;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AbstractAdvancedSearchFormFactory;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AdvancedSearchFilterRegistry;
use Symfony\Component\Form\FormFactoryInterface;

class ComplaintAdvancedSearchFormFactory extends AbstractAdvancedSearchFormFactory
{
    public function __construct(
        AdvancedSearchFilterRegistry $advancedSearchFilterRegistry,
        ComplaintAdvancedSearchFilterTranslation $complaintAdvancedSearchFilterTranslation,
        FormFactoryInterface $formFactory,
        AdvancedSearchOperatorTranslation $advancedSearchOperatorTranslation,
    ) {
        parent::__construct(
            $advancedSearchFilterRegistry,
            $complaintAdvancedSearchFilterTranslation,
            $formFactory,
            $advancedSearchOperatorTranslation,
        );
    }
}
