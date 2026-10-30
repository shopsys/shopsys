<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Twig\Runtime;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\DomainFacade;
use Symfony\Component\Asset\Packages;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

class DomainIconRuntime implements RuntimeExtensionInterface
{
    /**
     * @var array<int, string>|null
     */
    protected ?array $icons = null;

    public function __construct(
        protected readonly Environment $twigEnvironment,
        protected readonly Domain $domain,
        protected readonly DomainFacade $domainFacade,
        protected readonly Packages $assetPackages,
        protected readonly FilesystemOperator $filesystem,
        protected readonly string $domainImagesDirectory,
    ) {
    }

    public function renderDomainIcon(int $domainId, array $attr = []): string
    {
        $this->loadIcons();

        $domainName = $this->domain->getDomainConfigById($domainId)->getName();

        if (array_key_exists($domainId, $this->icons)) {
            $iconSrc = $this->assetPackages->getUrl('content/admin/images/domain/' . $this->icons[$domainId]);
        } else {
            $iconSrc = null;
        }

        return $this->twigEnvironment->render(
            '@ShopsysAdministration/partial/domain_icon.html.twig',
            [
                'domainId' => $domainId,
                'domainName' => $domainName,
                'iconSrc' => $iconSrc,
                'attr' => $attr,
            ],
        );
    }

    protected function loadIcons(): void
    {
        if ($this->icons !== null) {
            return;
        }

        $this->icons = [];

        $iconFiles = $this->filesystem->listContents($this->domainImagesDirectory)
            ->filter(static fn (StorageAttributes $storageAttributes): bool => $storageAttributes->isFile());

        foreach ($iconFiles as $iconFile) {
            $iconFileName = basename($iconFile->path());

            if (preg_match('/^(\d+)\.png$/', $iconFileName, $matches) === 1) {
                $this->icons[(int)$matches[1]] = $iconFileName;
            }
        }
    }
}
