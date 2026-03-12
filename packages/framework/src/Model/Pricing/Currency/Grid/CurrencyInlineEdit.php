<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency\Grid;

use Override;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Form\Admin\Pricing\Currency\CurrencyFormType;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class CurrencyInlineEdit extends AbstractGridInlineEdit
{
    public function __construct(
        CurrencyGridFactory $currencyGridFactory,
        AccessCheckerInterface $accessChecker,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly CurrencyDataFactory $currencyDataFactory,
    ) {
        parent::__construct($currencyGridFactory, $accessChecker);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData $currencyData
     */
    #[Override]
    protected function createEntityAndGetId(mixed $currencyData): int|string
    {
        $currency = $this->currencyFacade->create($currencyData);

        return $currency->getId();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData $currencyData
     */
    #[Override]
    protected function editEntity(int|string $currencyId, mixed $currencyData): void
    {
        $this->currencyFacade->edit($currencyId, $currencyData);
    }

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
            'is_used_in_orders' => $this->isCurrencyUsedInOrders($rowId),
        ]);
    }

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

    protected function isCurrencyUsedInOrders(?int $currencyId): bool
    {
        if ($currencyId === null) {
            return false;
        }

        $currencyIdsUsedInOrders = array_map(
            fn ($currency) => $currency->getId(),
            $this->currencyFacade->getCurrenciesUsedInOrders(),
        );

        return in_array($currencyId, $currencyIdsUsedInOrders, true);
    }

    #[Override]
    protected function getRoleConstant(): string
    {
        return SystemRole::SUPER_ADMIN;
    }
}
