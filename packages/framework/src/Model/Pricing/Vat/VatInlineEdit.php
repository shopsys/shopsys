<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Form\Admin\Vat\VatFormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class VatInlineEdit extends AbstractGridInlineEdit
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     */
    protected $vatFacade;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatDataFactoryInterface
     */
    protected $vatDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade
     */
    protected $adminDomainTabsFacade;

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
     * @param mixed $formData
     * @return int
     */
    protected function createEntityAndGetId(mixed $formData): int
    {
        $vat = $this->vatFacade->create($formData, $this->adminDomainTabsFacade->getSelectedDomainId());

        return $vat->getId();
    }

    /**
     * @param int|string $rowId
     * @param mixed $formData
     */
    protected function editEntity(int|string $rowId, mixed $formData): void
    {
        $this->vatFacade->edit($rowId, $formData);
    }

    /**
     * @param int|null $vatId
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm($vatId): FormInterface
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
