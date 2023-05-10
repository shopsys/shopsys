<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\ForbiddenDoctrineInheritanceSniff;

use Shopsys\CodingStandards\Sniffs\ForbiddenDoctrineInheritanceSniff;
use Tests\CodingStandards\Unit\Sniffs\AbstractSniffTestCase;

final class ForbiddenDoctrineInheritanceSniffTest extends AbstractSniffTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getSniffClassName(): string
    {
        return ForbiddenDoctrineInheritanceSniff::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getWrongFiles(): iterable
    {
        yield [__DIR__ . '/Wrong/ClassWithFullNamespaceInheritanceMapping.php'];
        yield [__DIR__ . '/Wrong/EntityWithOrmInheritanceMapping.php'];
    }

    /**
     * {@inheritdoc}
     */
    public function getCorrectFiles(): iterable
    {
        yield [__DIR__ . '/Correct/fileWithoutClass.php'];
        yield [__DIR__ . '/Correct/EntityWithoutInheritanceMapping.php'];
    }
}
