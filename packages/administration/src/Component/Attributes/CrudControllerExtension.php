<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Attributes;

use Attribute;
use Webmozart\Assert\Assert;

#[Attribute(Attribute::TARGET_CLASS)]
final class CrudControllerExtension
{
    /**
     * @param int|null $priority Bigger priority means that the extension will be applied later. Maximum priority is 999. Extensions defined in `App` namespace without priority are automatically executed as the latest
     */
    public function __construct(
        private readonly string $crudController,
        private readonly ?int $priority = 0,
    ) {
        Assert::lessThan($priority, 1000, sprintf('Priority must be lower than 1000, %d given.', $priority));
    }

    public function getCrudController(): string
    {
        return $this->crudController;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
