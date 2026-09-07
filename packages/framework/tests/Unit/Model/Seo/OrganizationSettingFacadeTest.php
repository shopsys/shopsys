<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Seo;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Nette\Utils\Json;
use Override;
use PHPUnit\Framework\TestCase;
use RedisException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Image\Processing\Exception\FileIsNotSupportedImageException;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Component\Setting\Exception\SettingValueNotFoundException;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;
use Shopsys\FrameworkBundle\Model\Seo\OrganizationSettingFacade;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

final class OrganizationSettingFacadeTest extends TestCase
{
    private string $directory;

    private Filesystem $filesystem;

    private OrganizationSettingFacade $facade;

    /**
     * @var array<int, string>
     */
    private array $settingsByDomain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade&\PHPUnit\Framework\MockObject\Stub
     */
    private CleanStorefrontCacheFacade $cleanStorefrontCacheFacade;

    #[Override]
    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/organization-logo-' . bin2hex(random_bytes(8));
        $this->filesystem = new Filesystem(new LocalFilesystemAdapter($this->directory));
        $this->filesystem->write('domain/previous.png', 'previous logo');
        $this->settingsByDomain = [
            Domain::FIRST_DOMAIN_ID => Json::encode(['logoFilename' => 'previous.png']),
        ];
        $setting = $this->createStub(Setting::class);
        $setting->method('getForDomain')->willReturnCallback(
            fn (string $key, int $domainId) => $this->settingsByDomain[$domainId]
                ?? throw new SettingValueNotFoundException(),
        );
        $setting->method('setForDomain')->willReturnCallback(function (string $key, string $value, int $domainId): void {
            $this->settingsByDomain[$domainId] = $value;
        });
        $fileUpload = $this->createStub(FileUpload::class);
        $fileUpload->method('getTemporaryFilepath')->willReturn('temporary/replacement.png');
        $this->cleanStorefrontCacheFacade = $this->createStub(CleanStorefrontCacheFacade::class);
        $domain = $this->createStub(Domain::class);
        $domain->method('getAllIds')->willReturn([Domain::FIRST_DOMAIN_ID, 2]);
        $this->facade = new OrganizationSettingFacade(
            $setting,
            $this->cleanStorefrontCacheFacade,
            $fileUpload,
            $this->filesystem,
            $domain,
            $this->createStub(MailSettingFacade::class),
            'domain',
            '/content/admin/images/domain',
        );
    }

    #[Override]
    protected function tearDown(): void
    {
        (new SymfonyFilesystem())->remove($this->directory);
    }

    public function testDeletingLogoRemovesPublicFile(): void
    {
        $this->facade->saveSettings(['name' => 'Company'], null, true, Domain::FIRST_DOMAIN_ID);

        $this->assertFalse($this->filesystem->fileExists('domain/previous.png'));
        $this->assertNull($this->facade->getSettings(Domain::FIRST_DOMAIN_ID)['logoFilename']);
    }

    public function testReplacingLogoRemovesOnlyPreviousFile(): void
    {
        $replacement = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aNJkAAAAASUVORK5CYII=', true);
        $this->filesystem->write('temporary/replacement.png', $replacement);
        $this->filesystem->write('domain/other-domain.png', 'other logo');
        $logo = (new ImageUploadDataFactory($this->createStub(ImageFacade::class)))->create();
        $logo->uploadedFiles = ['replacement.png'];

        $this->facade->saveSettings([], $logo, false, Domain::FIRST_DOMAIN_ID);

        $filename = $this->facade->getSettings(Domain::FIRST_DOMAIN_ID)['logoFilename'];
        $this->assertNotSame('previous.png', $filename);
        $this->assertSame($replacement, $this->filesystem->read('domain/' . $filename));
        $this->assertSame('public', $this->filesystem->visibility('domain/' . $filename));
        $this->assertFalse($this->filesystem->fileExists('domain/previous.png'));
        $this->assertTrue($this->filesystem->fileExists('domain/other-domain.png'));
    }

    public function testDeletingLogoPreservesFileReferencedByAnotherDomain(): void
    {
        $this->settingsByDomain[2] = Json::encode(['logoFilename' => 'previous.png']);

        $this->facade->saveSettings(['name' => 'Company'], null, true, Domain::FIRST_DOMAIN_ID);

        $this->assertTrue($this->filesystem->fileExists('domain/previous.png'));
        $this->assertNull($this->facade->getSettings(Domain::FIRST_DOMAIN_ID)['logoFilename']);
        $this->assertSame('previous.png', $this->facade->getSettings(2)['logoFilename']);
    }

    public function testReplacingLogoPreservesFileReferencedByAnotherDomain(): void
    {
        $replacement = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aNJkAAAAASUVORK5CYII=', true);
        $this->filesystem->write('temporary/replacement.png', $replacement);
        $this->settingsByDomain[2] = Json::encode(['logoFilename' => 'previous.png']);
        $logo = (new ImageUploadDataFactory($this->createStub(ImageFacade::class)))->create();
        $logo->uploadedFiles = ['replacement.png'];

        $this->facade->saveSettings([], $logo, false, Domain::FIRST_DOMAIN_ID);

        $this->assertTrue($this->filesystem->fileExists('domain/previous.png'));
        $this->assertNotSame('previous.png', $this->facade->getSettings(Domain::FIRST_DOMAIN_ID)['logoFilename']);
        $this->assertSame('previous.png', $this->facade->getSettings(2)['logoFilename']);
    }

    public function testSavingOtherSettingsPreservesLogo(): void
    {
        $this->facade->saveSettings(['name' => 'Updated company'], null, false, Domain::FIRST_DOMAIN_ID);

        $this->assertSame('previous logo', $this->filesystem->read('domain/previous.png'));
        $this->assertSame('previous.png', $this->facade->getSettings(Domain::FIRST_DOMAIN_ID)['logoFilename']);
    }

    public function testUnsupportedMimeTypeDoesNotReplaceOrPublishLogo(): void
    {
        $this->filesystem->write('temporary/replacement.png', '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        $logo = (new ImageUploadDataFactory($this->createStub(ImageFacade::class)))->create();
        $logo->uploadedFiles = ['replacement.png'];

        try {
            $this->facade->saveSettings([], $logo, false, Domain::FIRST_DOMAIN_ID);
            $this->fail('Unsupported MIME type must be rejected.');
        } catch (FileIsNotSupportedImageException) {
            $this->assertSame('previous.png', $this->facade->getSettings(Domain::FIRST_DOMAIN_ID)['logoFilename']);
        }

        $this->assertSame('previous logo', $this->filesystem->read('domain/previous.png'));
        $this->assertSame(
            ['domain/previous.png'],
            $this->filesystem->listContents('domain')->map(static fn ($file) => $file->path())->toArray(),
        );
    }

    public function testDeletingMissingLogoStillSavesSettings(): void
    {
        $this->filesystem->delete('domain/previous.png');

        $this->facade->saveSettings(['name' => 'Updated company'], null, true, Domain::FIRST_DOMAIN_ID);

        $this->assertSame(
            ['name' => 'Updated company', 'logoFilename' => null],
            $this->facade->getSettings(Domain::FIRST_DOMAIN_ID),
        );
    }

    public function testDeletingLogoRemovesPublicFileWhenCacheInvalidationFails(): void
    {
        $exception = new RedisException('Cache unavailable');
        $this->cleanStorefrontCacheFacade->method('cleanStorefrontGraphqlQueryCache')->willThrowException($exception);

        try {
            $this->facade->saveSettings([], null, true, Domain::FIRST_DOMAIN_ID);
            $this->fail('The cache exception must propagate.');
        } catch (RedisException $caughtException) {
            $this->assertSame($exception, $caughtException);
        }

        $this->assertFalse($this->filesystem->fileExists('domain/previous.png'));
        $this->assertNull($this->facade->getSettings(Domain::FIRST_DOMAIN_ID)['logoFilename']);
    }

    public function testMissingSettingsReturnEmptyData(): void
    {
        $this->assertSame([], $this->facade->getSettings(2));
    }

    public function testMalformedSettingsReturnEmptyData(): void
    {
        $this->settingsByDomain[Domain::FIRST_DOMAIN_ID] = '{malformed';

        $this->assertSame([], $this->facade->getSettings(Domain::FIRST_DOMAIN_ID));
    }

    public function testSettingsWithUnexpectedShapeReturnEmptyData(): void
    {
        $this->settingsByDomain[Domain::FIRST_DOMAIN_ID] = Json::encode('unexpected');

        $this->assertSame([], $this->facade->getSettings(Domain::FIRST_DOMAIN_ID));
    }
}
