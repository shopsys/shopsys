<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint;

use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOperatorTranslation;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AbstractAdvancedSearchFormFactory;
use Symfony\Component\Form\FormFactoryInterface;

class ComplaintAdvancedSearchFormFactory extends AbstractAdvancedSearchFormFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\ComplaintAdvancedSearchConfig $complaintAdvancedSearchConfig
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\AdvancedSearchComplaintFilterTranslation $advancedSearchComplaintFilterTranslation
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOperatorTranslation $advancedSearchOperatorTranslation
     */
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
