<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Component\Logger;

use DateTimeImmutable;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\McpBundle\Component\Logger\McpRequestLogProcessor;
use Shopsys\McpBundle\Component\Security\McpTokenAuthenticator;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class McpRequestLogProcessorTest extends TestCase
{
    private const int ADMINISTRATOR_ID = 1;
    private const string ADMINISTRATOR_USERNAME = 'admin';
    private const string CLIENT_IP = '127.0.0.1';
    private const string MCP_PATH = '/_mcp';

    public function testInvokeAddsMcpRequestContextToExtra(): void
    {
        $requestStack = $this->createRequestStackWithMcpToken();
        $mcpRequestLogProcessor = new McpRequestLogProcessor($requestStack);

        $record = $mcpRequestLogProcessor(new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'mcp',
            level: Level::Info,
            message: 'test',
        ));

        $this->assertSame(self::CLIENT_IP, $record->extra['client_ip']);
        $this->assertSame(self::ADMINISTRATOR_ID, $record->extra['administrator_id']);
        $this->assertSame(self::ADMINISTRATOR_USERNAME, $record->extra['administrator_username']);
    }

    private function createRequestStackWithMcpToken(): RequestStack
    {
        $administratorStub = $this->createStub(Administrator::class);
        $administratorStub
            ->method('getId')
            ->willReturn(self::ADMINISTRATOR_ID);
        $administratorStub
            ->method('getUsername')
            ->willReturn(self::ADMINISTRATOR_USERNAME);

        $administratorMcpTokenStub = $this->createStub(AdministratorMcpToken::class);
        $administratorMcpTokenStub
            ->method('getAdministrator')
            ->willReturn($administratorStub);

        $request = Request::create(self::MCP_PATH, 'POST', server: ['REMOTE_ADDR' => self::CLIENT_IP]);
        $request->attributes->set(McpTokenAuthenticator::REQUEST_ATTRIBUTE_ADMINISTRATOR_MCP_TOKEN, $administratorMcpTokenStub);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        return $requestStack;
    }
}
