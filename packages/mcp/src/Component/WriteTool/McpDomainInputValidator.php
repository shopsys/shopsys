<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\WriteTool;

use Mcp\Exception\ToolCallException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class McpDomainInputValidator
{
    public function __construct(
        protected readonly Domain $domain,
    ) {
    }

    public function assertLocaleExists(string $locale): void
    {
        if (!in_array($locale, $this->domain->getAllLocales(), true)) {
            throw new ToolCallException(sprintf('The locale "%s" does not exist, use one of: %s.', $locale, implode(', ', $this->domain->getAllLocales())));
        }
    }

    public function assertDomainExists(int $domainId): void
    {
        if (!in_array($domainId, $this->domain->getAllIds(), true)) {
            throw new ToolCallException(sprintf('The domain with ID %d does not exist, use one of: %s.', $domainId, implode(', ', $this->domain->getAllIds())));
        }
    }
}
