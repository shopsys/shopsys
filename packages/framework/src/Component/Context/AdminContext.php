<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Context;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Utils\Utils;
use Shopsys\FrameworkBundle\Model\Administration\AdminUrlProvider;
use Symfony\Component\HttpFoundation\RequestStack;

final class AdminContext extends AbstractContext
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Administration\AdminUrlProvider $adminUrlProvider
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param string[] $additionalAdminPathPrefixes
     */
    public function __construct(
        private readonly AdminUrlProvider $adminUrlProvider,
        private readonly Domain $domain,
        private readonly RequestStack $requestStack,
        private readonly array $additionalAdminPathPrefixes = [],
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDescription(): string
    {
        return 'Matches requests to the administration UI';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function matches(): bool
    {
        $request = $this->requestStack->getMainRequest();

        if ($request === null) {
            return false;
        }

        return $this->isPathMatchingAdminPattern($request->getPathInfo());
    }

    /**
     * @param string $pathinfo
     * @return bool
     */
    private function isPathMatchingAdminPattern(string $pathinfo): bool
    {
        return Utils::strStartsWithAny($pathinfo, $this->additionalAdminPathPrefixes) || preg_match('~^(' . $this->getAdminUrlPattern() . ')~', $pathinfo) === 1;
    }

    /**
     * @return string
     */
    private function getAdminUrlPattern(): string
    {
        $pattern = '(/' . $this->adminUrlProvider->getAdminUrl() . '($|/))';

        $domainConfigs = $this->domain->getAllIncludingDomainConfigsWithoutDataCreated();

        foreach ($domainConfigs as $domainConfig) {
            $postfix = $domainConfig->getPostfix();

            if ($postfix !== null) {
                $pattern .= '|(' . preg_quote($postfix, '~') . '/' . $this->adminUrlProvider->getAdminUrl() . '($|/))';
            }
        }

        return $pattern;
    }
}
