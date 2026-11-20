<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\ChangeRequest;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable(exposed: false)]
#[ORM\Table(name: 'mcp_change_requests')]
#[ORM\Entity]
class McpChangeRequest
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    #[ORM\JoinColumn(nullable: false, name: 'administrator_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    protected $administrator;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255)]
    protected $toolName;

    /**
     * @var class-string
     */
    #[ORM\Column(type: 'string', length: 255)]
    protected $argumentsClass;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: 'json')]
    protected $arguments;

    /**
     * @var array{subject: string, subjectParameters: array<string, string|int>, subjectUrl: string|null, fields: array<int, array{label: string, labelParameters: array<string, string|int>, before: string|null, after: string|null}>}
     */
    #[ORM\Column(type: 'json')]
    protected $change;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 16)]
    protected $status;

    /**
     * @var \DateTimeImmutable
     */
    #[ORM\Column(type: 'datetime_immutable')]
    protected $createdAt;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $errorMessage;

    public function __construct(McpChangeRequestData $mcpChangeRequestData)
    {
        $this->administrator = $mcpChangeRequestData->administrator;
        $this->toolName = $mcpChangeRequestData->toolName;
        $this->argumentsClass = $mcpChangeRequestData->argumentsClass;
        $this->arguments = $mcpChangeRequestData->arguments;
        $this->change = $mcpChangeRequestData->change;
        $this->createdAt = $mcpChangeRequestData->createdAt;
        $this->status = McpChangeRequestStatusEnum::PENDING;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function getAdministrator()
    {
        return $this->administrator;
    }

    /**
     * @return string
     */
    public function getToolName()
    {
        return $this->toolName;
    }

    /**
     * @return class-string
     */
    public function getArgumentsClass()
    {
        return $this->argumentsClass;
    }

    /**
     * @return array<string, mixed>
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @return array{subject: string, subjectParameters: array<string, string|int>, subjectUrl: string|null, fields: array<int, array{label: string, labelParameters: array<string, string|int>, before: string|null, after: string|null}>}
     */
    public function getChange()
    {
        return $this->change;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string|null
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function isPending(): bool
    {
        return $this->status === McpChangeRequestStatusEnum::PENDING;
    }

    public function isRefreshable(): bool
    {
        return $this->status === McpChangeRequestStatusEnum::FAILED;
    }

    /**
     * @param array{subject: string, subjectParameters: array<string, string|int>, subjectUrl: string|null, fields: array<int, array{label: string, labelParameters: array<string, string|int>, before: string|null, after: string|null}>} $change
     */
    public function refresh(array $change): void
    {
        $this->change = $change;
        $this->status = McpChangeRequestStatusEnum::PENDING;
        $this->errorMessage = null;
    }

    public function fail(string $errorMessage): void
    {
        $this->status = McpChangeRequestStatusEnum::FAILED;
        $this->errorMessage = $errorMessage;
    }

    public function markInapplicable(string $errorMessage): void
    {
        $this->status = McpChangeRequestStatusEnum::INAPPLICABLE;
        $this->errorMessage = $errorMessage;
    }
}
