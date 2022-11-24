<?php

namespace Tests\FrameworkBundle\Unit\Model\Security\Filesystem;

use FM\ElfinderBundle\Configuration\ElFinderConfigurationReader;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Filesystem\FilepathComparator;
use Shopsys\FrameworkBundle\Model\Security\Filesystem\Exception\InstanceNotInjectedException;
use Shopsys\FrameworkBundle\Model\Security\Filesystem\FilemanagerAccess;

class FilemanagerAccessTest extends TestCase
{
    /**
     * @return array<int, array<bool|string|null>>
     */
    public function isPathAccessibleProvider(): array
    {
        return [
            [
                __DIR__,
                __DIR__,
                'read',
                null,
            ],
            [
                __DIR__,
                __DIR__ . '/foo',
                'read',
                null,
            ],
            [
                __DIR__,
                __DIR__ . 'foo',
                'read',
                false,
            ],
            [
                __DIR__,
                __DIR__ . '/.foo',
                'read',
                false,
            ],
            [
                __DIR__ . '/sandbox',
                __DIR__ . '/sandboxSecreet/dummyFile',
                'read',
                false,
            ],
            [
                __DIR__ . '/sandbox',
                __DIR__ . '/sandbox/subdirectory/dummyFile',
                'read',
                null,
            ],
            [
                __DIR__ . '/sandbox',
                __DIR__ . '/sandbox/dummyFile',
                'read',
                null,
            ],
        ];
    }

    /**
     * @dataProvider isPathAccessibleProvider
     * @param mixed $fileuploadDir
     * @param mixed $testPath
     * @param mixed $attr
     * @param null|bool $isAccessible
     */
    public function testIsPathAccessible(string $fileuploadDir, string $testPath, string $attr, ?bool $isAccessible): void
    {
        $elFinderConfigurationReaderMock = $this->getMockBuilder(ElFinderConfigurationReader::class)
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $filemanagerAccess = new FilemanagerAccess(
            $fileuploadDir,
            $elFinderConfigurationReaderMock,
            new FilepathComparator()
        );

        $this->assertSame($filemanagerAccess->isPathAccessible($attr, $testPath, null, null), $isAccessible);
    }

    /**
     * @dataProvider isPathAccessibleProvider
     * @param mixed $fileuploadDir
     * @param mixed $testPath
     * @param mixed $attr
     * @param null|bool $isAccessible
     */
    public function testIsPathAccessibleStatic(string $fileuploadDir, string $testPath, string $attr, ?bool $isAccessible): void
    {
        $elFinderConfigurationReaderMock = $this->getMockBuilder(ElFinderConfigurationReader::class)
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $filemanagerAccess = new FilemanagerAccess(
            $fileuploadDir,
            $elFinderConfigurationReaderMock,
            new FilepathComparator()
        );
        FilemanagerAccess::injectSelf($filemanagerAccess);

        $this->assertSame(FilemanagerAccess::isPathAccessibleStatic($attr, $testPath, null, null), $isAccessible);
    }

    public function testIsPathAccessibleStaticException(): void
    {
        FilemanagerAccess::detachSelf();
        $this->expectException(InstanceNotInjectedException::class);
        FilemanagerAccess::isPathAccessibleStatic('read', __DIR__, null, null);
    }
}
