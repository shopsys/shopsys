<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\WriteTool;

use Shopsys\McpBundle\Model\ChangeRequest\McpChangeRequestFacade;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @template TArguments of object
 * @implements \Shopsys\McpBundle\Component\WriteTool\McpApprovableToolInterface<TArguments>
 */
abstract class AbstractMcpApprovableTool implements McpApprovableToolInterface
{
    #[Required]
    public McpChangeRequestFacade $mcpChangeRequestFacade;

    /**
     * @param TArguments $arguments
     * @return array{status: string, changeRequestId: int, message: string}
     */
    protected function requestApproval(object $arguments): array
    {
        $mcpChangeRequest = $this->mcpChangeRequestFacade->create($this, $arguments);

        return [
            'status' => 'pending_approval',
            'changeRequestId' => $mcpChangeRequest->getId(),
            'message' => 'The change waits for approval by the administrator in administration.',
        ];
    }
}
