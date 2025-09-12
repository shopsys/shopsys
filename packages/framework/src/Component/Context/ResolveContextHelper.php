<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Context;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class ResolveContextHelper
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param string $keyword
     * @param string $requestPath
     * @return bool
     */
    public function requestPathMatchesPattern(string $keyword, string $requestPath): bool
    {
        return preg_match($this->getPathPatternWithDomainPostfixes($keyword), $requestPath) === 1;
    }

    /**
     * @param string $keyword
     * @return string
     */
    protected function getPathPatternWithDomainPostfixes(string $keyword): string
    {
        $postfixes = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domainConfig) {
            $postfix = $domainConfig->getPostfix();

            if ($postfix !== null) {
                $postfixes[] = preg_quote(rtrim($postfix, '/'), '~');
            }
        }

        $postfixPattern = count($postfixes) > 0 ? '(' . implode('|', $postfixes) . ')' : '';

        return sprintf('~^(%s)?/%s(/|$)~', $postfixPattern, $keyword);
    }
}
