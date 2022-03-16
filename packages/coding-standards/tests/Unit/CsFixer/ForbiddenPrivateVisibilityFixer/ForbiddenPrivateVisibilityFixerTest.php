<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\ForbiddenPrivateVisibilityFixer;

use Shopsys\CodingStandards\CsFixer\ForbiddenPrivateVisibilityFixer;
use Tests\CodingStandards\Unit\CsFixer\AbstractFixerTestCase;

final class ForbiddenPrivateVisibilityFixerTest extends AbstractFixerTestCase
{
    /**
     * @return \Shopsys\CodingStandards\CsFixer\ForbiddenPrivateVisibilityFixer
     */
    protected function createFixerService(): ForbiddenPrivateVisibilityFixer
    {
        $fixer = new ForbiddenPrivateVisibilityFixer();
        $fixer->configure(['analyzed_namespaces' => ['TestNamespace']]);

        return $fixer;
    }

    /**
     * {@inheritDoc}
     */
    public function getTestingFiles(): iterable
    {
        yield [__DIR__ . '/fixed/fixed.php', __DIR__ . '/wrong/wrong.php'];
        yield [__DIR__ . '/correct/correct.php'];
        yield [__DIR__ . '/correct/ignored-namespace.php'];
    }
}
