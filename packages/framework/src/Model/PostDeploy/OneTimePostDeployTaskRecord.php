<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PostDeploy;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'one_time_post_deploy_tasks')]
#[ORM\Entity]
class OneTimePostDeployTaskRecord
{
    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255)]
    protected $name;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable')]
    protected $executedAt;

    public function __construct(string $name, DateTimeImmutable $executedAt)
    {
        $this->name = $name;
        $this->executedAt = $executedAt;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getExecutedAt()
    {
        return $this->executedAt;
    }
}
