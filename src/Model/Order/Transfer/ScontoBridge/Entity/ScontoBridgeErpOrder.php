<?php

declare(strict_types=1);

namespace App\Model\Order\Transfer\ScontoBridge\Entity;

use App\Model\Order\Transfer\ScontoBridge\Entity\ScontoBridgeErpOrder\ScontoBridgeOrderItem;
use JsonSerializable;

class ScontoBridgeErpOrder implements JsonSerializable
{
    /**
     * @var int
     */
    private int $eshopId;

    /**
     * @var string
     */
    private string $eshopOrderNumber;

    /**
     * @var int
     */
    private int $distributionChannelCode;

    /**
     * @var int
     */
    private int $eshopUserId;

    /**
     * @var string
     */
    private string $creationTime;

    /**
     * @var float
     */
    private float $priceWithVat;

    /**
     * @var string
     */
    private string $priceCurrency;

    /**
     * @var int|null
     */
    private ?int $title;

    /**
     * @var string
     */
    private string $invoiceAddressLastName;

    /**
     * @var string
     */
    private string $invoiceAddressFirstName;

    /**
     * @var string
     */
    private string $invoiceAddressStreet;

    /**
     * @var string
     */
    private string $invoiceAddressCountryISO;

    /**
     * @var string
     */
    private string $invoiceAddressZipCode;

    /**
     * @var string
     */
    private string $invoiceAddressCity;

    /**
     * @var string
     */
    private string $invoiceAddressPhone;

    /**
     * @var string
     */
    private string $email;

    /**
     * @var int
     */
    private int $paymentMethodId;

    /**
     * @var int
     */
    private int $deliveryMethodId;

    /**
     * @var string|null
     */
    private ?string $collectionStoreCode;

    /**
     * @var string
     */
    private string $deliveryAddressLastName;

    /**
     * @var string
     */
    private string $deliveryAddressFirstName;

    /**
     * @var string
     */
    private string $deliveryAddressStreet;

    /**
     * @var string|null
     */
    private ?string $deliveryAddressCountryISO;

    /**
     * @var string
     */
    private string $deliveryAddressZipCode;

    /**
     * @var string
     */
    private string $deliveryAddressCity;

    /**
     * @var string
     */
    private string $deliveryAddressPhone;

    /**
     * @var ScontoBridgeOrderItem[]
     */
    private array $orderItems = [];

    /**
     * @var int
     */
    private int $typeOfDeliveryKey;

    /**
     * @var string
     */
    private string $deliveryCode;

    public function __construct()
    {
        $this->title = null;
        $this->collectionStoreCode = null;
        $this->deliveryAddressCountryISO = null;
    }

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
     * @param int $distributionChannelCode
     */
    public function setDistributionChannelCode(int $distributionChannelCode): void
    {
        $this->distributionChannelCode = $distributionChannelCode;
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
     * @param int $title
     */
    public function setTitle(int $title): void
    {
        $this->title = $title;
    }

    /**
     * @param string $invoiceAddressLastName
     */
    public function setInvoiceAddressLastName(string $invoiceAddressLastName): void
    {
        $this->invoiceAddressLastName = $invoiceAddressLastName;
    }

    /**
     * @param string $invoiceAddressFirstName
     */
    public function setInvoiceAddressFirstName(string $invoiceAddressFirstName): void
    {
        $this->invoiceAddressFirstName = $invoiceAddressFirstName;
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
     * @param string $invoiceAddressPhone
     */
    public function setInvoiceAddressPhone(string $invoiceAddressPhone): void
    {
        $this->invoiceAddressPhone = $invoiceAddressPhone;
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

    /**
     * @param ScontoBridgeOrderItem $item
     */
    public function addItem(ScontoBridgeOrderItem $item): void
    {
        $this->orderItems[] = $item;
    }

    /**
     * @param int $typeOfDeliveryKey
     */
    public function setTypeOfDeliveryKey(int $typeOfDeliveryKey): void
    {
        $this->typeOfDeliveryKey = $typeOfDeliveryKey;
    }

    /**
     * @param string $deliveryCode
     */
    public function setDeliveryCode(string $deliveryCode): void
    {
        $this->deliveryCode = $deliveryCode;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'eshopId' => $this->eshopId,
            'eshopOrderNumber' => $this->eshopOrderNumber,
            'distributionChannelCode' => $this->distributionChannelCode,
            'eshopUserId' => $this->eshopUserId,
            'creationTime' => $this->creationTime,
            'priceWithVat' => $this->priceWithVat,
            'priceCurrency' => $this->priceCurrency,
            'title' => $this->title,
            'invoiceAddressLastName' => $this->invoiceAddressLastName,
            'invoiceAddressFirstName' => $this->invoiceAddressFirstName,
            'invoiceAddressStreet' => $this->invoiceAddressStreet,
            'invoiceAddressCountryISO' => $this->invoiceAddressCountryISO,
            'invoiceAddressZipCode' => $this->invoiceAddressZipCode,
            'invoiceAddressCity' => $this->invoiceAddressCity,
            'invoiceAddressPhone' => $this->invoiceAddressPhone,
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
            'typeOfDeliveryKey' => $this->typeOfDeliveryKey,
            'deliveryCode' => $this->deliveryCode,
        ];
    }
}
