<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Form\Admin\Vat\VatFormType;
use Symfony\Component\Form\FormFactoryInterface;

class VatInlineEdit extends AbstractGridInlineEdit
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     */
    private $vatFacade;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatDataFactoryInterface
     */
    private $vatDataFactory;

    public function __construct(
        VatGridFactory $vatGridFactory,
        VatFacade $vatFacade,
        FormFactoryInterface $formFactory,
        VatDataFactoryInterface $vatDataFactory
    ) {
        parent::__construct($vatGridFactory);
        $this->vatFacade = $vatFacade;
        $this->formFactory = $formFactory;
        $this->vatDataFactory = $vatDataFactory;
    }

    /**
     * @param VatData $vatData
     */
    protected function createEntityAndGetId($vatData): int
    {
        $vat = $this->vatFacade->create($vatData);

        return $vat->getId();
    }

    /**
     * @param int $vatId
     * @param VatData $vatData
     */
    protected function editEntity($vatId, $vatData): void
    {
        $this->vatFacade->edit($vatId, $vatData);
    }

    public function getForm($vatId): \Symfony\Component\Form\FormInterface
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
