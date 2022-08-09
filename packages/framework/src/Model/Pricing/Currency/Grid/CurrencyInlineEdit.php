<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency\Grid;

use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Form\Admin\Pricing\Currency\CurrencyFormType;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class CurrencyInlineEdit extends AbstractGridInlineEdit
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    protected $currencyFacade;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyDataFactoryInterface
     */
    protected $currencyDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Grid\CurrencyGridFactory $currencyGridFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyDataFactoryInterface $currencyDataFactory
     */
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
     * @param mixed $formData
     * @return int
     */
    protected function createEntityAndGetId(mixed $formData): int
    {
        $currency = $this->currencyFacade->create($formData);

        return $currency->getId();
    }

    /**
     * @param int|string $rowId
     * @param mixed $formData
     */
    protected function editEntity(int|string $rowId, mixed $formData): void
    {
        $this->currencyFacade->edit($rowId, $formData);
    }

    /**
     * @param int|null $currencyId
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm($currencyId): FormInterface
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
     * @return bool
     */
    protected function isDefaultCurrencyId($currencyId): bool
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
