<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Tool;

use Mcp\Capability\Attribute\McpTool;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\McpBundle\Component\Logger\McpToolCallLogger;

class GetDomainsTool
{
    protected const string TOOL_NAME = 'getDomains';

    public function __construct(
        protected readonly Domain $domain,
        protected readonly McpToolCallLogger $mcpToolCallLogger,
    ) {
    }

    /**
     * @return array{domains: array<int, array{id: int, name: string, locale: string, url: string}>}
     */
    #[McpTool(
        name: self::TOOL_NAME,
        description: 'Returns the domains of the shop with their IDs, names, locales and URLs. Use the IDs and locales as keys of the inputs of the write tools.',
        outputSchema: [
            'type' => 'object',
            'properties' => [
                'domains' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => [
                                'type' => 'integer',
                            ],
                            'name' => [
                                'type' => 'string',
                            ],
                            'locale' => [
                                'type' => 'string',
                            ],
                            'url' => [
                                'type' => 'string',
                            ],
                        ],
                        'required' => ['id', 'name', 'locale', 'url'],
                    ],
                ],
            ],
            'required' => ['domains'],
        ],
    )]
    public function getDomains(): array
    {
        $startedAt = microtime(true);
        $domains = [];

        foreach ($this->domain->getAll() as $domainConfig) {
            $domains[] = [
                'id' => $domainConfig->getId(),
                'name' => $domainConfig->getName(),
                'locale' => $domainConfig->getLocale(),
                'url' => $domainConfig->getUrl(),
            ];
        }

        $this->mcpToolCallLogger->logSuccess(static::TOOL_NAME, [], [
            'returned_domain_count' => count($domains),
        ], $startedAt);

        return [
            'domains' => $domains,
        ];
    }
}
