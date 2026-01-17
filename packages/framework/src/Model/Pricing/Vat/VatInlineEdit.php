<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Vat\VatFormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class VatInlineEdit extends AbstractGridInlineEdit
{
    public function __construct(
        VatGridFactory $vatGridFactory,
        AccessCheckerInterface $accessChecker,
        protected readonly VatFacade $vatFacade,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly VatDataFactory $vatDataFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
        parent::__construct($vatGridFactory, $accessChecker);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData $vatData
     */
    #[Override]
    protected function createEntityAndGetId(mixed $vatData): int|string
    {
        $vat = $this->vatFacade->create($vatData, $this->adminDomainTabsFacade->getSelectedDomainId());

        return $vat->getId();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData $vatData
     */
    #[Override]
    protected function editEntity(int|string $vatId, mixed $vatData): void
    {
        $this->vatFacade->edit($vatId, $vatData);
    }

    #[Override]
    public function getForm(int|string|null $rowId): FormInterface
    {
        if ($rowId !== null) {
            $vat = $this->vatFacade->getById((int)$rowId);
            $vatData = $this->vatDataFactory->createFromVat($vat);
        } else {
            $vatData = $this->vatDataFactory->create();
        }

        return $this->formFactory->create(VatFormType::class, $vatData, [
            'scenario' => ($rowId === null ? VatFormType::SCENARIO_CREATE : VatFormType::SCENARIO_EDIT),
        ]);
    }

    #[Override]
    protected function getRoleConstant(): string
    {
        return AdminRoleConstant::ROLE_VAT;
    }
}
