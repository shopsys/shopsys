<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Inquiry;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="inquiries")
 * @ORM\Entity
 */
class Inquiry
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $firstName;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $lastName;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $email;

    /**
     * @var string
     * @ORM\Column(type="string", length=30)
     */
    protected $telephone;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $companyName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $companyNumber;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $companyTaxNumber;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    protected $createdAt;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $note;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product")
     * @ORM\JoinColumn(nullable=true, name="product_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $product;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $productCatnum;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\InquiryData $inquiryData
     */
    public function __construct(InquiryData $inquiryData)
    {
        $this->createdAt = $inquiryData->createdAt ?? new DateTimeImmutable();
        $this->domainId = $inquiryData->domainId;

        $this->setData($inquiryData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\InquiryData $inquiryData
     */
    protected function setData(InquiryData $inquiryData): void
    {
        $this->firstName = $inquiryData->firstName;
        $this->lastName = $inquiryData->lastName;
        $this->email = $inquiryData->email;
        $this->telephone = $inquiryData->telephone;
        $this->companyName = $inquiryData->companyName;
        $this->companyNumber = $inquiryData->companyNumber;
        $this->companyTaxNumber = $inquiryData->companyTaxNumber;
        $this->note = $inquiryData->note;
        $this->product = $inquiryData->product;
        $this->productCatnum = $inquiryData->product->getCatnum();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * @return string|null
     */
    public function getCompanyName()
    {
        return $this->companyName;
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
    public function getCompanyTaxNumber()
    {
        return $this->companyTaxNumber;
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
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product|null
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return string
     */
    public function getProductCatnum()
    {
        return $this->productCatnum;
    }
}
