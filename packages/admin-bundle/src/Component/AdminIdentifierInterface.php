<?php

declare(strict_types=1);

namespace Shopsys\AdminBundle\Component;

interface AdminIdentifierInterface
{
    public function getId(): ?int;
}