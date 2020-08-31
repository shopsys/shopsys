<?php
declare(strict_types=1);

namespace App\Model\Customer\Transfer\ScontoBridge\Entity;

use App\Model\Customer\Transfer\ScontoBridge\Entity\ScontoBridgeErpUser\ScontoBridgeCompany;
use App\Model\Customer\Transfer\ScontoBridge\Entity\ScontoBridgeErpUser\ScontoBridgeIndividual;
use App\Model\Customer\Transfer\ScontoBridge\Entity\ScontoBridgeErpUser\ScontoBridgePrimaryAddress;
use JsonSerializable;

class ScontoBridgeErpUser implements JsonSerializable
{
    private int $eshopId;
    private string $email;
    private bool $newsletter;
    private int $distributionChannelCode;
    private int $customerType;
    private int $phonePrefix;
    private ?string $phoneNumber;
    private ?ScontoBridgePrimaryAddress $primaryAddress;
    private ?ScontoBridgeIndividual $individual;
    private ScontoBridgeCompany $company;

    public function __construct()
    {
        $this->phoneNumber = null;
        $this->primaryAddress = null;
        $this->individual = null;
    }

    /**
     * @param int $eshopId
     */
    public function setEshopId(int $eshopId): void
    {
        $this->eshopId = $eshopId;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @param bool $newsletter
     */
    public function setNewsletter(bool $newsletter): void
    {
        $this->newsletter = $newsletter;
    }

    /**
     * @param int $distributionChannelCode
     */
    public function setDistributionChannelCode(int $distributionChannelCode): void
    {
        $this->distributionChannelCode = $distributionChannelCode;
    }

    /**
     * @param int $customerType
     */
    public function setCustomerType(int $customerType): void
    {
        $this->customerType = $customerType;
    }

    /**
     * @param int $phonePrefix
     */
    public function setPhonePrefix(int $phonePrefix): void
    {
        $this->phonePrefix = $phonePrefix;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @param ScontoBridgePrimaryAddress $primaryAddress
     */
    public function setPrimaryAddress(ScontoBridgePrimaryAddress $primaryAddress): void
    {
        $this->primaryAddress = $primaryAddress;
    }

    /**
     * @param ScontoBridgeIndividual $individual
     */
    public function setIndividual(ScontoBridgeIndividual $individual): void
    {
        $this->individual = $individual;
    }

    /**
     * @param ScontoBridgeCompany $company
     */
    public function setCompany(ScontoBridgeCompany $company): void
    {
        $this->company = $company;
    }

    public function jsonSerialize(): array
    {
        return [
            'eshopId' => $this->eshopId,
            'email' => $this->email,
            'newsletter' => $this->newsletter,
            'distributionChannelCode' => $this->distributionChannelCode,
            'customerType' => $this->customerType,
            'phonePrefix' => $this->phonePrefix,
            'phoneNumber' => $this->phoneNumber,
            'primaryAddress' => $this->primaryAddress,
            'individual' => $this->individual,
            'company' => $this->company
        ];
    }
}
