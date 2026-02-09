<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cdn;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class CdnFacade
{
    protected ?string $cdnDomain = null;

    public function __construct(
        ?string $cdnDomain,
        protected readonly Domain $domain,
    ) {
        if (trim($cdnDomain, '/') !== '') {
            $this->cdnDomain = $cdnDomain;
        }
    }

    public function resolveDomainUrlForAssets(DomainConfig $domainConfig): string
    {
        return $this->cdnDomain ?? $domainConfig->getBaseUrl();
    }

    public function replaceUrlsByCdnForAssets(?string $content): ?string
    {
        if ($content === null || $this->cdnDomain === null) {
            return $content;
        }

        $escapedDomainUrls = [];

        foreach ($this->domain->getAll() as $domainConfig) {
            $escapedDomainUrls[] = preg_quote($domainConfig->getUrl(), '/');
        }

        $domainsPattern = implode('|', $escapedDomainUrls);
        $pattern = '/((' . $domainsPattern . ')\/)(content|public)/i';

        $replacement = $this->cdnDomain . '/$3';

        return preg_replace($pattern, $replacement, $content);
    }
}
