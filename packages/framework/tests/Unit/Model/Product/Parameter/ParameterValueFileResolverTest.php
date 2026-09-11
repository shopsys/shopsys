<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product\Parameter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataExtractor;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueFileResolver;

final class ParameterValueFileResolverTest extends TestCase
{
    #[DataProvider('provideNames')]
    public function testIconAlternativeUsesTranslatedNameOrParameterValue(?string $name, string $expected): void
    {
        $domainConfig = $this->createStub(DomainConfig::class);
        $domainConfig->method('getLocale')->willReturn('en');
        $file = $this->createStub(UploadedFile::class);
        $file->method('getTranslatedName')->willReturn($name);
        $facade = $this->createStub(UploadedFileFacade::class);
        $facade->method('getAllFilesIndexedByEntityId')->willReturn([
            1 => [$file],
        ]);
        $extractor = $this->createStub(UploadedFileDataExtractor::class);
        $extractor->method('extractUploadedFileData')->willReturn([
            'anchorText' => 'heart-red.svg',
            'url' => '/heart-red.svg',
            'viewUrl' => '/heart-red.svg',
            'filesize' => 100,
            'extension' => 'svg',
        ]);
        $resolver = new ParameterValueFileResolver($facade, $extractor);

        $data = $resolver->addIconDataToParameterValuesData([
            ['parameter_value_id' => 1, 'parameter_value_text' => 'Red'],
        ], $domainConfig);

        $this->assertSame($expected, $data[0]['parameter_value_icon_anchor_text']);
        $this->assertSame('/heart-red.svg', $data[0]['parameter_value_icon_url']);
    }

    /**
     * @return iterable<string, array{0: string|null, 1: string}>
     */
    public static function provideNames(): iterable
    {
        yield 'admin ALT' => ['Red hearts', 'Red hearts'];

        yield 'zero ALT' => ['0', '0'];

        yield 'missing ALT' => [null, 'Red'];

        yield 'empty ALT' => ['', 'Red'];

        yield 'blank ALT' => ['   ', 'Red'];
    }
}
