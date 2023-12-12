<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

use Symfony\Contracts\EventDispatcher\Event;

class CurrencyEvent extends Event
{
    /**
     * The CREATE event occurs once a currency was created.
     *
     * This event allows you to run jobs dependent on the currency creation.
     */
    public const CREATE = 'currency.create';
    /**
     * The UPDATE event occurs once a currency was changed.
     *
     * This event allows you to run jobs dependent on the currency change.
     */
    public const UPDATE = 'currency.update';
    /**
     * The DELETE event occurs once a currency was deleted.
     *
     * This event allows you to run jobs dependent on the currency deletion.
     */
    public const DELETE = 'currency.delete';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     */
    public function __construct(protected readonly Currency $currency)
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }
}
