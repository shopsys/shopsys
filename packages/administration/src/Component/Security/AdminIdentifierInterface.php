<?php

declare(strict_types=1);

namespace Shopsys\Administration\Component\Security;

interface AdminIdentifierInterface
{
    /**
     * @return int|null
     */
    public function getId(): ?int;
}
