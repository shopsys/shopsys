<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Filesystem;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Filesystem\PathInsideDirectoryChecker;

class PathInsideDirectoryCheckerTest extends TestCase
{
    #[DataProvider('pathProvider')]
    public function testIsInside(string $path, string $directory, bool $expected): void
    {
        $this->assertSame($expected, PathInsideDirectoryChecker::isInside($path, $directory));
    }

    /**
     * @return iterable<string, array{string, string, bool}>
     */
    public static function pathProvider(): iterable
    {
        yield 'the directory itself' => ['web/content/wysiwyg', 'web/content/wysiwyg', true];
        yield 'nested file' => ['web/content/wysiwyg/a/b.png', 'web/content/wysiwyg', true];
        yield 'leading slashes are ignored' => ['/web/content/wysiwyg/a.png', 'web/content/wysiwyg/', true];
        yield 'dot segments staying inside' => ['web/content/wysiwyg/a/../b.png', 'web/content/wysiwyg', true];
        yield 'sibling directory' => ['web/content/other/a.png', 'web/content/wysiwyg', false];
        yield 'directory with the same prefix' => ['web/content/wysiwyg-evil/a.png', 'web/content/wysiwyg', false];
        yield 'parent directory' => ['web/content', 'web/content/wysiwyg', false];
        yield 'traversal out of the directory' => ['web/content/wysiwyg/../../../config/domains.yaml', 'web/content/wysiwyg', false];
        yield 'traversal above the filesystem root' => ['../config/domains.yaml', 'web/content/wysiwyg', false];
        yield 'backslash traversal' => ['web/content/wysiwyg\\..\\..\\..\\config', 'web/content/wysiwyg', false];
        yield 'control characters' => ["web/content/wysiwyg/a\x00.png", 'web/content/wysiwyg', false];
        yield 'nothing is inside an empty directory' => ['config/domains.yaml', '', false];
    }
}
