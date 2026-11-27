<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Twig;

use Override;
use Shopsys\McpBundle\Component\WriteTool\McpChangeDiffCalculator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class McpChangeDiffExtension extends AbstractExtension
{
    public function __construct(
        protected readonly McpChangeDiffCalculator $mcpChangeDiffCalculator,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('mcpChangeDiff', $this->mcpChangeDiffCalculator->calculate(...)),
        ];
    }
}
