<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Twig\Runtime;

use League\Flysystem\DirectoryAttributes;
use League\Flysystem\DirectoryListing;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Twig\Runtime\DomainIconRuntime;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\DomainFacade;
use Symfony\Component\Asset\Packages;
use Twig\Environment;

class DomainIconRuntimeTest extends TestCase
{
    private const string DOMAIN_IMAGES_DIRECTORY = '/web/content/admin/images/domain';

    public function testIconsAreLoadedFromFilesystemOnce(): void
    {
        $filesystem = $this->createMock(FilesystemOperator::class);
        $filesystem->expects($this->once())
            ->method('listContents')
            ->with(self::DOMAIN_IMAGES_DIRECTORY)
            ->willReturn(new DirectoryListing([
                new FileAttributes('web/content/admin/images/domain/1.png'),
                new FileAttributes('web/content/admin/images/domain/readme.txt'),
                new DirectoryAttributes('web/content/admin/images/domain/2.png'),
            ]));

        $renderedIconSources = [];
        $twigEnvironment = $this->createStub(Environment::class);
        $twigEnvironment->method('render')
            ->willReturnCallback(static function (string $template, array $context) use (&$renderedIconSources): string {
                $renderedIconSources[$context['domainId']] = $context['iconSrc'];

                return '';
            });

        $domainConfig = $this->createStub(DomainConfig::class);
        $domainConfig->method('getName')->willReturn('example.com');
        $domain = $this->createStub(Domain::class);
        $domain->method('getDomainConfigById')->willReturn($domainConfig);

        $assetPackages = $this->createStub(Packages::class);
        $assetPackages->method('getUrl')->willReturnCallback(static fn (string $path): string => '/' . $path);

        $domainIconRuntime = new DomainIconRuntime(
            $twigEnvironment,
            $domain,
            $this->createStub(DomainFacade::class),
            $assetPackages,
            $filesystem,
            self::DOMAIN_IMAGES_DIRECTORY,
        );

        $domainIconRuntime->renderDomainIcon(1);
        $domainIconRuntime->renderDomainIcon(2);

        $this->assertSame(
            [
                1 => '/content/admin/images/domain/1.png',
                2 => null,
            ],
            $renderedIconSources,
        );
    }
}
