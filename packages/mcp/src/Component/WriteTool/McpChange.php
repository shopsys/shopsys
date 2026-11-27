<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\WriteTool;

/**
 * @phpstan-type McpChangeFieldArray array{
 *     label: string,
 *     labelParameters: array<string, string|int>,
 *     before: string|null,
 *     after: string|null,
 *     contextBefore: string|null,
 *     contextAfter: string|null
 * }
 * @phpstan-type McpChangeArray array{
 *     subject: string,
 *     subjectParameters: array<string, string|int>,
 *     subjectUrl: string|null,
 *     fields: array<int, McpChangeFieldArray>
 * }
 */
class McpChange
{
    /**
     * @var array<int, McpChangeFieldArray>
     */
    protected array $fields = [];

    /**
     * @param array<string, string|int> $subjectParameters
     */
    public function __construct(
        protected readonly string $subject,
        protected readonly array $subjectParameters = [],
        protected readonly ?string $subjectUrl = null,
    ) {
    }

    /**
     * @param array<string, string|int> $labelParameters
     */
    public function addField(
        string $label,
        ?string $before,
        ?string $after,
        array $labelParameters = [],
        ?string $contextBefore = null,
        ?string $contextAfter = null,
    ): static {
        if ($before === $after) {
            return $this;
        }

        $this->fields[] = [
            'label' => $label,
            'labelParameters' => $labelParameters,
            'before' => $before,
            'after' => $after,
            'contextBefore' => $contextBefore,
            'contextAfter' => $contextAfter,
        ];

        return $this;
    }

    /**
     * @return McpChangeArray
     */
    public function toArray(): array
    {
        return [
            'subject' => $this->subject,
            'subjectParameters' => $this->subjectParameters,
            'subjectUrl' => $this->subjectUrl,
            'fields' => $this->fields,
        ];
    }
}
