<?php
declare(strict_types=1);

namespace App\Model\Product\Transfer\ScontoBridge\Mapper\Entity;

use App\Model\Product\Transfer\ScontoBridge\Mapper\Entity\ScontoBridgeErpOrder\ScontoBridgeOrderItem;
use JsonSerializable;

class ScontoBridgeErpOrder implements JsonSerializable
{
    private int $eshopId;
    private string $eshopOrderNumber;
    private int $distributionChannelId;
    private int $eshopUserId;
    private string $creationTime;
    private float $priceWithVat;
    private string $priceCurrency;
    private string $title;
    private string $lastName;
    private string $firstName;
    private string $invoiceAddressStreet;
    private string $invoiceAddressCountryISO;
    private string $invoiceAddressZipCode;
    private string $invoiceAddressCity;
    private string $phone;
    private string $email;
    private int $paymentMethodId;
    private int $deliveryMethodId;
    private string $collectionStoreCode;
    private string $deliveryAddressLastName;
    private string $deliveryAddressFirstName;
    private string $deliveryAddressStreet;
    private string $deliveryAddressCountryISO;
    private string $deliveryAddressZipCode;
    private string $deliveryAddressCity;
    private string $deliveryAddressPhone;

    /**
     * @var ScontoBridgeOrderItem[]
     */
    private array $orderItems = [];

    /**
     * @param int $eshopId
     */
    public function setEshopId(int $eshopId): void
    {
        $this->eshopId = $eshopId;
    }

    /**
     * @param string $eshopOrderNumber
     */
    public function setEshopOrderNumber(string $eshopOrderNumber): void
    {
        $this->eshopOrderNumber = $eshopOrderNumber;
    }

    /**
     * @param int $distributionChannelId
     */
    public function setDistributionChannelId(int $distributionChannelId): void
    {
        $this->distributionChannelId = $distributionChannelId;
    }

    /**
     * @param int $eshopUserId
     */
    public function setEshopUserId(int $eshopUserId): void
    {
        $this->eshopUserId = $eshopUserId;
    }

    /**
     * @param mixed $creationTime
     */
    public function setCreationTime($creationTime): void
    {
        $this->creationTime = $creationTime;
    }

    /**
     * @param float $priceWithVat
     */
    public function setPriceWithVat(float $priceWithVat): void
    {
        $this->priceWithVat = $priceWithVat;
    }

    /**
     * @param string $priceCurrency
     */
    public function setPriceCurrency(string $priceCurrency): void
    {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @param string $invoiceAddressStreet
     */
    public function setInvoiceAddressStreet(string $invoiceAddressStreet): void
    {
        $this->invoiceAddressStreet = $invoiceAddressStreet;
    }

    /**
     * @param string $invoiceAddressCountryISO
     */
    public function setInvoiceAddressCountryISO(string $invoiceAddressCountryISO): void
    {
        $this->invoiceAddressCountryISO = $invoiceAddressCountryISO;
    }

    /**
     * @param string $invoiceAddressZipCode
     */
    public function setInvoiceAddressZipCode(string $invoiceAddressZipCode): void
    {
        $this->invoiceAddressZipCode = $invoiceAddressZipCode;
    }

    /**
     * @param string $invoiceAddressCity
     */
    public function setInvoiceAddressCity(string $invoiceAddressCity): void
    {
        $this->invoiceAddressCity = $invoiceAddressCity;
    }

    /**
     * @param string $phone
     */
    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @param int $paymentMethodId
     */
    public function setPaymentMethodId(int $paymentMethodId): void
    {
        $this->paymentMethodId = $paymentMethodId;
    }

    /**
     * @param int $deliveryMethodId
     */
    public function setDeliveryMethodId(int $deliveryMethodId): void
    {
        $this->deliveryMethodId = $deliveryMethodId;
    }

    /**
     * @param string $collectionStoreCode
     */
    public function setCollectionStoreCode(string $collectionStoreCode): void
    {
        $this->collectionStoreCode = $collectionStoreCode;
    }

    /**
     * @param string $deliveryAddressLastName
     */
    public function setDeliveryAddressLastName(string $deliveryAddressLastName): void
    {
        $this->deliveryAddressLastName = $deliveryAddressLastName;
    }

    /**
     * @param string $deliveryAddressFirstName
     */
    public function setDeliveryAddressFirstName(string $deliveryAddressFirstName): void
    {
        $this->deliveryAddressFirstName = $deliveryAddressFirstName;
    }

    /**
     * @param string $deliveryAddressStreet
     */
    public function setDeliveryAddressStreet(string $deliveryAddressStreet): void
    {
        $this->deliveryAddressStreet = $deliveryAddressStreet;
    }

    /**
     * @param string $deliveryAddressCountryISO
     */
    public function setDeliveryAddressCountryISO(string $deliveryAddressCountryISO): void
    {
        $this->deliveryAddressCountryISO = $deliveryAddressCountryISO;
    }

    /**
     * @param string $deliveryAddressZipCode
     */
    public function setDeliveryAddressZipCode(string $deliveryAddressZipCode): void
    {
        $this->deliveryAddressZipCode = $deliveryAddressZipCode;
    }

    /**
     * @param string $deliveryAddressCity
     */
    public function setDeliveryAddressCity(string $deliveryAddressCity): void
    {
        $this->deliveryAddressCity = $deliveryAddressCity;
    }

    /**
     * @param string $deliveryAddressPhone
     */
    public function setDeliveryAddressPhone(string $deliveryAddressPhone): void
    {
        $this->deliveryAddressPhone = $deliveryAddressPhone;
    }

    public function addItem(ScontoBridgeOrderItem $item): void
    {
        $this->orderItems[] = $item;
    }

    public function jsonSerialize(): array
    {
        return [
            'eshopId' => $this->eshopId,
            'eshopOrderNumber' => $this->eshopOrderNumber,
            'distributionChannelId' => $this->distributionChannelId,
            'eshopUserId' => $this->eshopUserId,
            'creationTime' => $this->creationTime,
            'priceWithVat' => $this->priceWithVat,
            'priceCurrency' => $this->priceCurrency,
            'title' => $this->title,
            'lastName' => $this->lastName,
            'firstName' => $this->firstName,
            'invoiceAddressStreet' => $this->invoiceAddressStreet,
            'invoiceAddressCountryISO' => $this->invoiceAddressCountryISO,
            'invoiceAddressZipCode' => $this->invoiceAddressZipCode,
            'invoiceAddressCity' => $this->invoiceAddressCity,
            'phone' => $this->phone,
            'email' => $this->email,
            'paymentMethodId' => $this->paymentMethodId,
            'deliveryMethodId' => $this->deliveryMethodId,
            'collectionStoreCode' => $this->collectionStoreCode,
            'deliveryAddressLastName' => $this->deliveryAddressLastName,
            'deliveryAddressFirstName' => $this->deliveryAddressFirstName,
            'deliveryAddressStreet' => $this->deliveryAddressStreet,
            'deliveryAddressCountryISO' => $this->deliveryAddressCountryISO,
            'deliveryAddressZipCode' => $this->deliveryAddressZipCode,
            'deliveryAddressCity' => $this->deliveryAddressCity,
            'deliveryAddressPhone' => $this->deliveryAddressPhone,
            'orderItems' => $this->orderItems,
        ];
    }
}
