<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Form\Admin\Vat\VatFormType;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class VatInlineEdit extends AbstractGridInlineEdit
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatGridFactory $vatGridFactory
     * @param \Symfony\Bundle\SecurityBundle\Security $security
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatDataFactory $vatDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        VatGridFactory $vatGridFactory,
        Security $security,
        protected readonly VatFacade $vatFacade,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly VatDataFactory $vatDataFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
        parent::__construct($vatGridFactory, $security);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData $vatData
     * @return int|string
     */
    #[Override]
    protected function createEntityAndGetId(mixed $vatData): int|string
    {
        $vat = $this->vatFacade->create($vatData, $this->adminDomainTabsFacade->getSelectedDomainId());

        return $vat->getId();
    }

    /**
     * @param int|string $vatId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData $vatData
     */
    #[Override]
    protected function editEntity(int|string $vatId, mixed $vatData): void
    {
        $this->vatFacade->edit($vatId, $vatData);
    }

    /**
     * @param int|string|null $rowId
     * @return \Symfony\Component\Form\FormInterface
     */
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

    /**
     * @return string
     */
    #[Override]
    protected function getEditRole(): string
    {
        return Roles::ROLE_VAT_FULL;
    }
}
