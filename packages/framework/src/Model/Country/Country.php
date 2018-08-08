<?php

namespace Shopsys\FrameworkBundle\Model\Country;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="countries")
 * @ORM\Entity
 */
class Country
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * Country code in ISO 3166-1 alpha-2
     * @var string|null
     *
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    protected $code;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $domainId;
    
    public function __construct(CountryData $countryData, int $domainId)
    {
        $this->name = $countryData->name;
        $this->domainId = $domainId;
        $this->code = $countryData->code;
    }

    public function edit(CountryData $countryData): void
    {
        $this->name = $countryData->name;
        $this->code = $countryData->code;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }
}
