<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Module;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'enabled_modules')]
#[ORM\Entity]
class EnabledModule
{
    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 100)]
    protected $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
