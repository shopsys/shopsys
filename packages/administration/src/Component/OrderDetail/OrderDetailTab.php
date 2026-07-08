<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\OrderDetail;

use Closure;
use Shopsys\FrameworkBundle\Model\Order\Order;

readonly class OrderDetailTab
{
    public function __construct(
        protected string $id,
        protected string $label,
        protected string $componentName,
        protected int $position = 0,
        protected ?Closure $disabledWhen = null,
        protected ?Closure $visibleWhen = null,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getComponentName(): string
    {
        return $this->componentName;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function isDisabled(Order $order): bool
    {
        return $this->disabledWhen !== null && ($this->disabledWhen)($order);
    }

    public function isVisible(Order $order): bool
    {
        return $this->visibleWhen === null || ($this->visibleWhen)($order);
    }
}
