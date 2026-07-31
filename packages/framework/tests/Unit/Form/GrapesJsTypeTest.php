<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form;

use Override;
use Shopsys\FrameworkBundle\Form\GrapesJsType;

final class GrapesJsTypeTest extends AbstractHtmlContentFieldTestCase
{
    #[Override]
    protected function getTestedFormTypeClass(): string
    {
        return GrapesJsType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getPreloadedFormTypes(): array
    {
        return [new GrapesJsType($this->createWysiwygCdnDataTransformer())];
    }
}
