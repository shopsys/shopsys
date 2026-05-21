<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\LanguageConstant;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantFacade;
use Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantFactory;
use Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantRepository;

class LanguageConstantFacadeTest extends TestCase
{
    public function testGenerateAllNamespaceFilesWritesEmptyJsonObjectForEmptyNamespace(): void
    {
        $writtenFiles = [];
        $languageConstantFacade = $this->createLanguageConstantFacade(
            [
                ['cs', 'common', ['Add to cart' => 'To cart']],
                ['cs', 'accessibility', []],
            ],
            function (string $path, string $contents) use (&$writtenFiles): void {
                $writtenFiles[$path] = $contents;
            },
        );

        $languageConstantFacade->generateAllNamespaceFiles('cs');

        $this->assertSame('{"Add to cart":"To cart"}', $writtenFiles['/web/content/locales/cs/common.json']);
        $this->assertSame('{}', $writtenFiles['/web/content/locales/cs/accessibility.json']);
    }

    public function testGenerateAllNamespaceFilesAndCleanStorefrontCacheCleansCacheAfterFilesAreWritten(): void
    {
        $events = new class() {
            /**
             * @var list<string>
             */
            public array $items = [];
        };

        $cleanStorefrontCacheFacade = $this->createMock(CleanStorefrontCacheFacade::class);
        $cleanStorefrontCacheFacade
            ->expects($this->once())
            ->method('cleanStorefrontTranslationCache')
            ->with('cs', 'common')
            ->willReturnCallback(function () use ($events): void {
                $events->items[] = 'cache cleaned';
            });

        $languageConstantFacade = $this->createLanguageConstantFacade(
            [
                ['cs', 'common', ['Add to cart' => 'To cart']],
                ['cs', 'accessibility', []],
            ],
            function (string $path) use ($events): void {
                $events->items[] = $path;
            },
            $cleanStorefrontCacheFacade,
        );

        $languageConstantFacade->generateAllNamespaceFilesAndCleanStorefrontCache('cs', 'common');

        $this->assertSame(
            [
                '/web/content/locales/cs/common.json',
                '/web/content/locales/cs/accessibility.json',
                'cache cleaned',
            ],
            $events->items,
        );
    }

    /**
     * @param array<int, array{0: string, 1: string, 2: array<string, string>}> $userTranslationsByLocaleAndNamespace
     * @param callable(string, string): void $writeCallback
     */
    private function createLanguageConstantFacade(
        array $userTranslationsByLocaleAndNamespace,
        callable $writeCallback,
        ?CleanStorefrontCacheFacade $cleanStorefrontCacheFacade = null,
    ): LanguageConstantFacade {
        $languageConstantRepository = $this->createStub(LanguageConstantRepository::class);
        $languageConstantRepository
            ->method('getTranslationsByLocaleIndexedByKey')
            ->willReturnMap($userTranslationsByLocaleAndNamespace);

        $filesystem = $this->createMock(FilesystemOperator::class);
        $filesystem
            ->method('has')
            ->with('/web/content/locales/cs')
            ->willReturn(false);
        $filesystem
            ->expects($this->once())
            ->method('createDirectory')
            ->with('/web/content/locales/cs', ['directory_visibility' => 'public']);
        $filesystem
            ->expects($this->exactly(2))
            ->method('write')
            ->willReturnCallback($writeCallback);

        return new LanguageConstantFacade(
            $this->createStub(EntityManagerInterface::class),
            $languageConstantRepository,
            $this->createStub(LanguageConstantFactory::class),
            [
                'common' => 'http://webserver:8080/locales/%s/common.json',
                'accessibility' => 'http://webserver:8080/locales/%s/accessibility.json',
            ],
            '/web/content/locales/',
            $filesystem,
            $cleanStorefrontCacheFacade ?? $this->createStub(CleanStorefrontCacheFacade::class),
            $this->createStub(LoggerInterface::class),
        );
    }
}
