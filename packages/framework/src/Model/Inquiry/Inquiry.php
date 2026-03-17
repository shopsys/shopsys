<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Inquiry;

use Doctrine\ORM\Mapping as ORM;
use LogicException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Symfony\Component\Clock\DatePoint;

#[ORM\Table(name: 'inquiries')]
#[ORM\Entity]
class Inquiry
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100)]
    protected $firstName;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100)]
    protected $lastName;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255)]
    protected $email;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    protected $telephonePrefix;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 2, nullable: true)]
    protected $telephonePrefixCountryCode;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 30)]
    protected $telephoneNumber;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $companyName;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    protected $companyNumber;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    protected $companyTaxNumber;

    /**
     * @var \DateTimeImmutable
     */
    #[ORM\Column(type: 'datetime_immutable')]
    protected $createdAt;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $note;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     */
    #[ORM\JoinColumn(nullable: true, name: 'product_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    protected $product;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100)]
    protected $productCatnum;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    #[ORM\JoinColumn(nullable: true, name: 'customer_user_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: CustomerUser::class)]
    protected $customerUser;

    public function __construct(InquiryData $inquiryData)
    {
        $this->createdAt = $inquiryData->createdAt ?? new DatePoint();
        $this->domainId = $inquiryData->domainId;

        $this->setData($inquiryData);
    }

    protected function setData(InquiryData $inquiryData): void
    {
        if ($inquiryData->product === null && $inquiryData->productCatnum === null) {
            throw new LogicException('Either product or productCatnum must be set to properly create an inquiry.');
        }

        if ($inquiryData->product !== null && $inquiryData->productCatnum !== null) {
            throw new LogicException('Only one of product or productCatnum can be set to properly create an inquiry.');
        }

        $this->firstName = $inquiryData->firstName;
        $this->lastName = $inquiryData->lastName;
        $this->email = $inquiryData->email;
        $this->setTelephoneData($inquiryData->telephone);
        $this->companyName = $inquiryData->companyName;
        $this->companyNumber = $inquiryData->companyNumber;
        $this->companyTaxNumber = $inquiryData->companyTaxNumber;
        $this->note = $inquiryData->note;
        $this->product = $inquiryData->product;
        $this->productCatnum = $inquiryData->product ? $inquiryData->product->getCatnum() : $inquiryData->productCatnum;
        $this->customerUser = $inquiryData->customerUser;
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
        return $this->getTelephoneData()->toPhoneNumber();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData
     */
    public function getTelephoneData()
    {
        return new PhoneData(
            $this->telephonePrefixCountryCode,
            $this->telephonePrefix,
            $this->telephoneNumber,
        );
    }

    public function setTelephoneData(PhoneData $phoneData): void
    {
        $this->telephonePrefix = $phoneData->prefix;
        $this->telephonePrefixCountryCode = $phoneData->countryCode;
        $this->telephoneNumber = $phoneData->number;
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

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    public function getCustomerUser()
    {
        return $this->customerUser;
    }
}
