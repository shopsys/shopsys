<?php

declare(strict_types=1);

namespace Tests\App\Functional\Twig;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\Multidomain\Twig\FilesystemLoader;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Tests\App\Test\FunctionalTestCase;
use Twig\Environment;

class FilesystemLoaderTest extends FunctionalTestCase
{
    public function testLoadingMultiDesignTemplate(): void
    {
        $twigFilesystemLoader = $this->getContainer()->get('twig.loader.native_filesystem');
        $paths = $twigFilesystemLoader->getPaths();
        $setting = $this->createMock(Setting::class);

        $domainConfigWithoutCustomDesignId = new DomainConfig(
            1,
            'http://webserver:8080',
            'domain1',
            'en',
            'sa',
            ''
        );

        $domainConfigWithCustomDesignId = new DomainConfig(
            2,
            'http://webserver:8080',
            'domain2',
            'en',
            'sa',
            'custom'
        );

        $domain = new Domain([$domainConfigWithoutCustomDesignId, $domainConfigWithCustomDesignId], $setting);

        $twigFilesystemLoader = new FilesystemLoader($paths, null, $domain);
        $twigEnvironment = new Environment($twigFilesystemLoader);

        $domain->switchDomainById(1);
        $fileOutput = $twigEnvironment->render('Front/Tests/twigLoader.html.twig');
        $this->assertSame('Hello world', trim($fileOutput));

        $domain->switchDomainById(2);
        $fileOutput = $twigEnvironment->render('Front/Tests/twigLoader.html.twig');
        $this->assertSame('Hello world custom', trim($fileOutput));
    }
}
