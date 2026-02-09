<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Translation;

trait FrontendApiValidationTranslationDomainDetectorTrait
{
    protected bool $isFrontendApiBundleConstraintClass = false;

    protected function setIfFrontendApiClass(string $className): void
    {
        if (str_starts_with($className, 'Shopsys\\FrontendApiBundle\\') || str_starts_with($className, 'App\\FrontendApi\\')) {
            $this->isFrontendApiBundleConstraintClass = true;
        }
    }

    protected function getTranslationDomain(): string
    {
        if ($this->isFrontendApiBundleConstraintClass) {
            return Translator::CUSTOMER_VALIDATOR_TRANSLATION_DOMAIN;
        }

        return Translator::VALIDATOR_TRANSLATION_DOMAIN;
    }

    protected function resetDetection(): void
    {
        $this->isFrontendApiBundleConstraintClass = false;
    }
}
