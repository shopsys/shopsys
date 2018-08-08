<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency\Grid;

use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Form\Admin\Pricing\Currency\CurrencyFormType;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Symfony\Component\Form\FormFactoryInterface;

class CurrencyInlineEdit extends AbstractGridInlineEdit
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    private $currencyFacade;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyDataFactoryInterface
     */
    private $currencyDataFactory;

    public function __construct(
        CurrencyGridFactory $currencyGridFactory,
        CurrencyFacade $currencyFacade,
        FormFactoryInterface $formFactory,
        CurrencyDataFactoryInterface $currencyDataFactory
    ) {
        parent::__construct($currencyGridFactory);
        $this->currencyFacade = $currencyFacade;
        $this->formFactory = $formFactory;
        $this->currencyDataFactory = $currencyDataFactory;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData $currencyData
     */
    protected function createEntityAndGetId($currencyData): int
    {
        $currency = $this->currencyFacade->create($currencyData);

        return $currency->getId();
    }

    /**
     * @param int $currencyId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData $currencyData
     */
    protected function editEntity($currencyId, $currencyData): void
    {
        $this->currencyFacade->edit($currencyId, $currencyData);
    }

    /**
     * @param int|null $currencyId
     */
    public function getForm($currencyId): \Symfony\Component\Form\FormInterface
    {
        if ($currencyId !== null) {
            $currency = $this->currencyFacade->getById((int)$currencyId);
            $currencyData = $this->currencyDataFactory->createFromCurrency($currency);
        } else {
            $currencyData = $this->currencyDataFactory->create();
        }

        return $this->formFactory->create(CurrencyFormType::class, $currencyData, [
            'is_default_currency' => $this->isDefaultCurrencyId($currencyId),
        ]);
    }

    /**
     * @param int|null $currencyId
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
}
