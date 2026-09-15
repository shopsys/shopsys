<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable(exposed: false)]
#[ORM\Table(name: 'organizations')]
#[ORM\Entity]
#[EntityImage]
class Organization
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', unique: true)]
    protected $domainId;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $name;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $companyTaxNumber;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $companyNumber;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $description;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $street;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $city;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $postcode;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $country;

    /**
     * @param int $domainId
     */
    public function __construct($domainId, OrganizationData $data)
    {
        $this->domainId = $domainId;
        $this->edit($data);
    }

    public function edit(OrganizationData $data): void
    {
        $this->name = $data->name;
        $this->companyTaxNumber = $data->companyTaxNumber;
        $this->companyNumber = $data->companyNumber;
        $this->description = $data->description;
        $this->street = $data->street;
        $this->city = $data->city;
        $this->postcode = $data->postcode;
        $this->country = $data->country;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getCompanyTaxNumber()
    {
        return $this->companyTaxNumber;
    }

    /**
     * @return string|null
     */
    public function getCompanyNumber()
    {
        return $this->companyNumber;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @return string|null
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return string|null
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @return string|null
     */
    public function getCountry()
    {
        return $this->country;
    }
}
