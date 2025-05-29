<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency\Grid;

use Override;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Form\Admin\Pricing\Currency\CurrencyFormType;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class CurrencyInlineEdit extends AbstractGridInlineEdit
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Grid\CurrencyGridFactory $currencyGridFactory
     * @param \Symfony\Bundle\SecurityBundle\Security $security
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyDataFactory $currencyDataFactory
     */
    public function __construct(
        CurrencyGridFactory $currencyGridFactory,
        Security $security,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly CurrencyDataFactory $currencyDataFactory,
    ) {
        parent::__construct($currencyGridFactory, $security);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData $currencyData
     * @return int|string
     */
    #[Override]
    protected function createEntityAndGetId(mixed $currencyData): int|string
    {
        $currency = $this->currencyFacade->create($currencyData);

        return $currency->getId();
    }

    /**
     * @param int|string $currencyId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData $currencyData
     */
    #[Override]
    protected function editEntity(int|string $currencyId, mixed $currencyData): void
    {
        $this->currencyFacade->edit($currencyId, $currencyData);
    }

    /**
     * @param int|string|null $rowId
     * @return \Symfony\Component\Form\FormInterface
     */
    #[Override]
    public function getForm(int|string|null $rowId): FormInterface
    {
        if ($rowId !== null) {
            $currency = $this->currencyFacade->getById((int)$rowId);
            $currencyData = $this->currencyDataFactory->createFromCurrency($currency);
        } else {
            $currencyData = $this->currencyDataFactory->create();
        }

        return $this->formFactory->create(CurrencyFormType::class, $currencyData, [
            'is_default_currency' => $this->isDefaultCurrencyId($rowId),
        ]);
    }

    /**
     * @param int|null $currencyId
     * @return bool
     */
    protected function isDefaultCurrencyId(?int $currencyId): bool
    {
        if ($currencyId !== null) {
            $currency = $this->currencyFacade->getById($currencyId);

            if ($this->currencyFacade->isDefaultCurrency($currency)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    #[Override]
    protected function getEditRole(): string
    {
        return Roles::ROLE_SUPER_ADMIN;
    }
}
