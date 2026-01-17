<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Twig\Runtime;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\DomainFacade;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Finder\Finder;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

class DomainIconRuntime implements RuntimeExtensionInterface
{
    /**
     * @var array<int, string>|null
     */
    protected ?array $icons = null;

    public function __construct(
        protected readonly string $kernelProjectDir,
        protected readonly Environment $twigEnvironment,
        protected readonly Domain $domain,
        protected readonly DomainFacade $domainFacade,
        protected readonly Packages $assetPackages,
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

        $finder = Finder::create()
            ->files()
            ->in($this->kernelProjectDir . '/web/content/admin/images/domain')
            ->name('/\d+\.png$/')
            ->depth(0);

        foreach ($finder as $file) {
            $domainId = (int)$file->getBasename('.png');
            $this->icons[$domainId] = $file->getBasename();
        }
    }
}
