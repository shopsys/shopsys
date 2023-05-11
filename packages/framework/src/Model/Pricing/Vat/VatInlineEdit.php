<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Form\Admin\Vat\VatFormType;
use Symfony\Component\Form\FormFactoryInterface;

class VatInlineEdit extends AbstractGridInlineEdit
{
    protected VatFacade $vatFacade;

    protected FormFactoryInterface $formFactory;

    protected VatDataFactoryInterface $vatDataFactory;

    protected AdminDomainTabsFacade $adminDomainTabsFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatGridFactory $vatGridFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatDataFactoryInterface $vatDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        VatGridFactory $vatGridFactory,
        VatFacade $vatFacade,
        FormFactoryInterface $formFactory,
        VatDataFactoryInterface $vatDataFactory,
        AdminDomainTabsFacade $adminDomainTabsFacade
    ) {
        parent::__construct($vatGridFactory);

        $this->vatFacade = $vatFacade;
        $this->formFactory = $formFactory;
        $this->vatDataFactory = $vatDataFactory;
        $this->adminDomainTabsFacade = $adminDomainTabsFacade;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData $vatData
     * @return int
     */
    protected function createEntityAndGetId($vatData)
    {
        $vat = $this->vatFacade->create($vatData, $this->adminDomainTabsFacade->getSelectedDomainId());

        return $vat->getId();
    }

    /**
     * @param int $vatId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData $vatData
     */
    protected function editEntity($vatId, $vatData)
    {
        $this->vatFacade->edit($vatId, $vatData);
    }

    /**
     * @param int|null $vatId
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm($vatId)
    {
        if ($vatId !== null) {
            $vat = $this->vatFacade->getById((int)$vatId);
            $vatData = $this->vatDataFactory->createFromVat($vat);
        } else {
            $vatData = $this->vatDataFactory->create();
        }

        return $this->formFactory->create(VatFormType::class, $vatData, [
            'scenario' => ($vatId === null ? VatFormType::SCENARIO_CREATE : VatFormType::SCENARIO_EDIT),
        ]);
    }
}
