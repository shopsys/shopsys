<?php

namespace Shopsys\ShopBundle\Model\Gdpr;

class PersonalDataAccessRequestData
{

    /**
     * @var \DateTimeImmutable
     */
    public $createAt;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $hash;

    /**
     * @var int
     */
    public $domainId;
}
