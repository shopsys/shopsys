<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Component\WriteTool\data;

use Override;
use Shopsys\McpBundle\Component\WriteTool\McpApprovableToolInterface;
use Shopsys\McpBundle\Component\WriteTool\McpChange;
use Throwable;

/**
 * @implements \Shopsys\McpBundle\Component\WriteTool\McpApprovableToolInterface<\Tests\McpBundle\Unit\Component\WriteTool\data\TestApprovableWriteToolArguments>
 */
class TestApprovableWriteTool implements McpApprovableToolInterface
{
    public const string TOOL_NAME = 'editPublishedTestArticle';

    public string $currentName = 'Old name';

    public ?Throwable $applyException = null;

    /**
     * @var \Tests\McpBundle\Unit\Component\WriteTool\data\TestApprovableWriteToolArguments[]
     */
    public array $appliedArguments = [];

    #[Override]
    public static function getToolName(): string
    {
        return static::TOOL_NAME;
    }

    /**
     * @param \Tests\McpBundle\Unit\Component\WriteTool\data\TestApprovableWriteToolArguments $arguments
     */
    #[Override]
    public function previewChange(object $arguments): McpChange
    {
        return new McpChange('Published test article', [], '/admin/test-article/edit/' . $arguments->articleId)
            ->addField('Name', $this->currentName, $arguments->name);
    }

    /**
     * @param \Tests\McpBundle\Unit\Component\WriteTool\data\TestApprovableWriteToolArguments $arguments
     */
    #[Override]
    public function apply(object $arguments): void
    {
        if ($this->applyException !== null) {
            throw $this->applyException;
        }

        $this->appliedArguments[] = $arguments;
    }
}
