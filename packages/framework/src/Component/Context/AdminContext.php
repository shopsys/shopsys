<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Context;

use Override;
use Shopsys\FrameworkBundle\Component\Utils\Utils;
use Shopsys\FrameworkBundle\Model\Administration\AdminUrlProvider;
use Symfony\Component\HttpFoundation\RequestStack;

final class AdminContext extends AbstractContext
{
    /**
     * @param string[] $additionalAdminPathPrefixes
     */
    public function __construct(
        private readonly AdminUrlProvider $adminUrlProvider,
        private readonly ResolveContextHelper $resolveContextHelper,
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

    private function isPathMatchingAdminPattern(string $pathinfo): bool
    {
        return Utils::strStartsWithAny($pathinfo, $this->additionalAdminPathPrefixes) ||
            $this->resolveContextHelper->requestPathMatchesPattern($this->adminUrlProvider->getAdminUrl(), $pathinfo);
    }
}
