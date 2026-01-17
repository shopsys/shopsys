<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\LanguageConstant;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class LanguageConstantFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(LanguageConstantData $languageConstantData): LanguageConstant
    {
        $entityName = $this->entityNameResolver->resolve(LanguageConstant::class);

        return new $entityName($languageConstantData);
    }
}
