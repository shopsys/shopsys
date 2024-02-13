<?php

declare(strict_types=1);

namespace Shopsys\AdminBundle\Component\Admin;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Symfony\Contracts\Service\Attribute\Required;

class AbstractAdmin extends \Sonata\AdminBundle\Admin\AbstractAdmin
{
    #[Required]
    public function updateModelClass(EntityNameResolver $entityNameResolver): void
    {
        $this->setModelClass($entityNameResolver->resolve($this->getClass()));
    }
}