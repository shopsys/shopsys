<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Country;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpInheritedColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[AsMcpInheritedColumn(fieldName: 'id')]
#[AsMcpInheritedColumn(fieldName: 'locale')]
#[ORM\Table(name: 'country_translations')]
#[ORM\Entity]
class CountryTranslation extends AbstractTranslation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\Country
     */
    #[AsMcpColumn]
    #[Prezent\Translatable(targetEntity: Country::class)]
    #[Override]
    protected $translatable;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    protected $name {
        set {
            $this->name = TransformStringHelper::getTrimmedStringOrNullOnEmpty($value);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }
}
