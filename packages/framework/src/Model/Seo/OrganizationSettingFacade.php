<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\Visibility;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;
use Shopsys\FrameworkBundle\Component\Image\Processing\Exception\FileIsNotSupportedImageException;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Component\Setting\Exception\SettingValueNotFoundException;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;

class OrganizationSettingFacade
{
    public const string ORGANIZATION_SETTING = 'seoOrganization';

    public function __construct(
        protected readonly Setting $setting,
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
        protected readonly FileUpload $fileUpload,
        protected readonly FilesystemOperator $filesystem,
        protected readonly Domain $domain,
        protected readonly MailSettingFacade $mailSettingFacade,
        protected readonly string $domainImagesDirectory,
        protected readonly string $domainImagesUrlPrefix,
    ) {
    }

    /**
     * @return array<string, string|null>
     */
    public function getSettings(int $domainId): array
    {
        try {
            $settings = Json::decode((string)$this->setting->getForDomain(self::ORGANIZATION_SETTING, $domainId), true);
        } catch (SettingValueNotFoundException | JsonException) {
            return [];
        }

        return is_array($settings) ? $settings : [];
    }

    /**
     * @param array<string, string|null> $data
     */
    public function saveSettings(array $data, ?ImageUploadData $logo, bool $deleteLogo, int $domainId): void
    {
        $previousLogo = $this->getSettings($domainId)['logoFilename'] ?? null;
        $filename = $deleteLogo ? null : $previousLogo;

        if ($logo !== null && $logo->uploadedFiles !== []) {
            $uploadedFile = array_first($logo->uploadedFiles);
            $temporaryPath = $this->fileUpload->getTemporaryFilepath($uploadedFile);
            $extension = match ($this->filesystem->mimeType($temporaryPath)) {
                'image/png' => 'png',
                'image/jpeg' => 'jpg',
                default => throw new FileIsNotSupportedImageException('Only PNG and JPEG images are supported'),
            };
            $filename = 'organization-' . $domainId . '-' . bin2hex(random_bytes(16)) . '.' . $extension;
            $this->filesystem->copy($temporaryPath, $this->domainImagesDirectory . '/' . $filename);
            $this->filesystem->setVisibility($this->domainImagesDirectory . '/' . $filename, Visibility::PUBLIC);
        }

        $data['logoFilename'] = $filename;
        $this->setting->setForDomain(self::ORGANIZATION_SETTING, Json::encode($data), $domainId);

        try {
            $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::SETTINGS_QUERY_KEY_PART);
        } finally {
            if ($previousLogo !== null
                && $previousLogo !== $filename
                && !$this->isLogoUsedByOtherDomain($previousLogo, $domainId)
            ) {
                $this->filesystem->delete($this->domainImagesDirectory . '/' . $previousLogo);
            }
        }
    }

    protected function isLogoUsedByOtherDomain(string $logoFilename, int $excludedDomainId): bool
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            if ($domainId === $excludedDomainId) {
                continue;
            }

            if (($this->getSettings($domainId)['logoFilename'] ?? null) === $logoFilename) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, string|array<string>|null>
     */
    public function getOrganization(int $domainId): array
    {
        $data = $this->getSettings($domainId);
        $filename = $data['logoFilename'] ?? null;
        unset($data['logoFilename']);
        $data['logo'] = $filename !== null
            ? rtrim($this->domain->getDomainConfigById($domainId)->getUrl(), '/') . $this->domainImagesUrlPrefix . '/' . $filename
            : null;
        $data['sameAs'] = array_values(array_filter($this->mailSettingFacade->getFooterIconUrls($domainId)));

        return $data;
    }
}
