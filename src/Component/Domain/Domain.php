<?php

declare(strict_types=1);

namespace App\Component\Domain;

use Shopsys\FrameworkBundle\Component\Domain\Domain as BaseDomain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Symfony\Component\HttpFoundation\Request;

/**
 * @property \App\Component\Setting\Setting $setting
 */
class Domain extends BaseDomain
{
    /**
     * @var string|null
     */
    private $cdnDomain;

    /**
     * @var bool
     */
    private $isOnCdnDomain;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[] $domainConfigs
     * @param \App\Component\Setting\Setting $setting
     * @param string|null $cdnDomain
     */
    public function __construct(array $domainConfigs, Setting $setting, ?string $cdnDomain = null)
    {
        $this->cdnDomain = $cdnDomain;
        $this->isOnCdnDomain = false;

        parent::__construct($domainConfigs, $setting);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function switchDomainByRequest(Request $request)
    {
        try {
            return parent::switchDomainByRequest($request);
        } catch (\Shopsys\FrameworkBundle\Component\Domain\Exception\UnableToResolveDomainException $unableToResolveDomainException) {
            $requestDomain = $request->getSchemeAndHttpHost() . $request->getBasePath();
            if ($requestDomain === $this->cdnDomain) {
                $this->isOnCdnDomain = true;
            } else {
                throw $unableToResolveDomainException;
            }
        }
    }

    /**
     * @return bool
     */
    public function isHttps()
    {
        if ($this->isOnCdnDomain() === true) {
            return strpos($this->cdnDomain, 'https://') === 0;
        }

        return parent::isHttps();
    }

    /**
     * @return bool
     */
    public function isOnCdnDomain(): bool
    {
        return $this->isOnCdnDomain;
    }
}
