<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form\Admin;

use Override;
use Shopsys\FrameworkBundle\Form\Admin\GrapesJsMailType;
use Tests\FrameworkBundle\Unit\Form\AbstractHtmlContentFieldTestCase;

final class GrapesJsMailTypeTest extends AbstractHtmlContentFieldTestCase
{
    #[Override]
    protected function getTestedFormTypeClass(): string
    {
        return GrapesJsMailType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getPreloadedFormTypes(): array
    {
        return [new GrapesJsMailType($this->createWysiwygCdnDataTransformer())];
    }
}
