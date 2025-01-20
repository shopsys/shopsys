<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\LanguageConstant;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class LanguageConstantFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantData $languageConstantData
     * @return \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstant
     */
    public function create(LanguageConstantData $languageConstantData): LanguageConstant
    {
        $entityName = $this->entityNameResolver->resolve(LanguageConstant::class);

        return new $entityName($languageConstantData);
    }
}
