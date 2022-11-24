<?php

declare(strict_types=1);

namespace Tests\App\Functional\Twig;

use ReflectionClass;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Tests\App\Test\FunctionalTestCase;

class FilesystemLoaderTest extends FunctionalTestCase
{
    public function testLoadingMultiDesignTemplate(): void
    {
        $twigFilesystemLoader = $this->getContainer()->get('twig.loader.native_filesystem');
        $setting = $this->createMock(Setting::class);
        $twig = $this->getContainer()->get('twig');

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

        $twigReflection = new ReflectionClass($twigFilesystemLoader);
        $domainReflection = $twigReflection->getProperty('domain');
        $domainReflection->setAccessible(true);
        $domainReflection->setValue($twigFilesystemLoader, $domain);

        $domain->switchDomainById(1);
        $fileOutput = $twig->render('Front/Tests/twigLoader.html.twig');
        $this->assertSame('Hello world', trim($fileOutput));

        $domain->switchDomainById(2);
        $fileOutput = $twig->render('Front/Tests/twigLoader.html.twig');
        $this->assertSame('Hello world custom', trim($fileOutput));

        $domainReflection->setValue($twigFilesystemLoader, $this->domain);
        $domainReflection->setAccessible(false);
    }
}
