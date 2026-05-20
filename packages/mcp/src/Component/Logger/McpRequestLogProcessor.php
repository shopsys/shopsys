<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Logger;

use Monolog\LogRecord;
use Shopsys\McpBundle\Component\Security\McpTokenAuthenticator;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpToken;
use Symfony\Component\HttpFoundation\RequestStack;

class McpRequestLogProcessor
{
    public function __construct(protected readonly RequestStack $requestStack)
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            return $record;
        }

        $extra = $record->extra;
        $extra['client_ip'] = $request->getClientIp();

        $administratorMcpToken = $request->attributes->get(McpTokenAuthenticator::REQUEST_ATTRIBUTE_ADMINISTRATOR_MCP_TOKEN);

        if (!$administratorMcpToken instanceof AdministratorMcpToken) {
            return $record->with(extra: $extra);
        }

        $extra['administrator_id'] = $administratorMcpToken->getAdministrator()->getId();
        $extra['administrator_username'] = $administratorMcpToken->getAdministrator()->getUsername();

        return $record->with(extra: $extra);
    }
}
