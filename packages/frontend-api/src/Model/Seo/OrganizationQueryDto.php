<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Seo;

class OrganizationQueryDto
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $companyTaxNumber;

    /**
     * @var string|null
     */
    public $companyNumber;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var string|null
     */
    public $street;

    /**
     * @var string|null
     */
    public $city;

    /**
     * @var string|null
     */
    public $postcode;

    /**
     * @var string|null
     */
    public $country;

    /**
     * @var string|null
     */
    public $logo;

    /**
     * @var string[]
     */
    public $socialNetworkUrls = [];
}
