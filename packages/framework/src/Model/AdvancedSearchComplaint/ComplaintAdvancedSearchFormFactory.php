<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint;

use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOperatorTranslation;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AbstractAdvancedSearchFormFactory;
use Symfony\Component\Form\FormFactoryInterface;

class ComplaintAdvancedSearchFormFactory extends AbstractAdvancedSearchFormFactory
{
    public function __construct(
        ComplaintAdvancedSearchConfig $complaintAdvancedSearchConfig,
        AdvancedSearchComplaintFilterTranslation $advancedSearchComplaintFilterTranslation,
        FormFactoryInterface $formFactory,
        AdvancedSearchOperatorTranslation $advancedSearchOperatorTranslation,
    ) {
        parent::__construct(
            $complaintAdvancedSearchConfig,
            $advancedSearchComplaintFilterTranslation,
            $formFactory,
            $advancedSearchOperatorTranslation,
        );
    }
}
