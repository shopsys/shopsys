<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

class ComplaintData
{
    /**
     * @var string|null
     */
    public $uuid;

    /**
     * @var int
     */
    public $domainId;

    /**
     * @var string
     */
    public $number;

    /**
     * @var \DateTimeImmutable|null
     */
    public $createdAt;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    public $order;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    public $customerUser;

    /**
     * @var string|null
     */
    public $deliveryFirstName;

    /**
     * @var string|null
     */
    public $deliveryLastName;

    /**
     * @var string|null
     */
    public $deliveryCompanyName;

    /**
     * @var \Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData|null
     */
    public $deliveryTelephone;

    /**
     * @var string|null
     */
    public $deliveryStreet;

    /**
     * @var string|null
     */
    public $deliveryCity;

    /**
     * @var string|null
     */
    public $deliveryPostcode;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\Country|null
     */
    public $deliveryCountry;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus|null
     */
    public $status;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData[]
     */
    public $complaintItems = [];

    /**
     * @var string|null
     */
    public $email;

    /**
     * @var string|null
     */
    public $manualDocumentNumber;

    /**
     * @var string|null
     */
    public $resolution;

    /**
     * @var string|null
     */
    public $bankAccountNumber;
}
