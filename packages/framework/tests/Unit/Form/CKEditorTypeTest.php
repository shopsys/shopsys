<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form;

use FOS\CKEditorBundle\Config\CKEditorConfigurationInterface;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Override;
use Shopsys\FrameworkBundle\Form\WysiwygTypeExtension;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\Asset\Packages;
use Symfony\Reprise\Asset\EntrypointsLookupInterface;

final class CKEditorTypeTest extends AbstractHtmlContentFieldTestCase
{
    #[Override]
    protected function getTestedFormTypeClass(): string
    {
        return CKEditorType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getPreloadedFormTypes(): array
    {
        return [new CKEditorType($this->createStub(CKEditorConfigurationInterface::class))];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getPreloadedTypeExtensions(): array
    {
        $localization = $this->createStub(Localization::class);
        $localization->method('getRequestLocale')->willReturn('en');

        return parent::getPreloadedTypeExtensions() + [
            CKEditorType::class => [
                new WysiwygTypeExtension(
                    $localization,
                    $this->createStub(EntrypointsLookupInterface::class),
                    $this->createStub(Packages::class),
                    $this->createWysiwygCdnDataTransformer(),
                ),
            ],
        ];
    }
}
