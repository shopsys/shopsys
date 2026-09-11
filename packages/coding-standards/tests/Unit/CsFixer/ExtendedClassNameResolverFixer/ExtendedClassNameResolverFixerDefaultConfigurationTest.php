<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\ExtendedClassNameResolverFixer;

use Override;
use Shopsys\CodingStandards\CsFixer\ExtendedClassNameResolverFixer;
use Tests\CodingStandards\Unit\CsFixer\AbstractFixerTestCase;

final class ExtendedClassNameResolverFixerDefaultConfigurationTest extends AbstractFixerTestCase
{
    #[Override]
    protected function createFixerService(): ExtendedClassNameResolverFixer
    {
        return new ExtendedClassNameResolverFixer();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getTestingFiles(): iterable
    {
        yield [__DIR__ . '/fixed/default_configuration.php', __DIR__ . '/wrong/default_configuration.php'];
    }
}
