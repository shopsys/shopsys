<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Inquiry;

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
}
