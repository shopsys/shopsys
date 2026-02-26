<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Override;
use Symfony\Bridge\Doctrine\Middleware\Debug\DebugDataHolder;
use Symfony\Bridge\Doctrine\Middleware\Debug\Query;

class ToggleableDebugDataHolder extends DebugDataHolder
{
    protected bool $enabled = true;

    #[Override]
    public function addQuery(string $connectionName, Query $query): void
    {
        if (!$this->enabled) {
            return;
        }

        parent::addQuery($connectionName, $query);
    }

    public function disable(): void
    {
        $this->reset();
        $this->enabled = false;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }
}
