<?php

declare(strict_types=1);

namespace App\Component\Targito;

use Symfony\Component\OptionsResolver\OptionsResolver;

class TargitoConfig
{
    public const GDPR_MARKETING_LIFETIME_IN_DAYS = 1095;
    public const FORMAT_DATETIME_FOR_TARGITO = 'Y-m-d H:i:s';
    public const TARGITO_BASE_URL = 'https://api.targito.com/v1.0/';

    /**
     * @var bool
     */
    public $enabled;

    /**
     * @var string|null
     */
    public $eshopToTargitoAccountId;

    /**
     * @var string|null
     */
    public $eshopToTargitoPassword;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @param array $targitoConfig
     */
    public function __construct(array $targitoConfig)
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setRequired([
            'enabled',
            'eshop_to_targito_account_id',
            'eshop_to_targito_password',
        ]);
        $optionsResolver->setAllowedTypes(
            'eshop_to_targito_account_id',
            ['string', 'null']
        );
        $optionsResolver->setAllowedTypes(
            'eshop_to_targito_password',
            ['string', 'null']
        );

        $optionsResolver->resolve($targitoConfig);

        $this->enabled = $targitoConfig['enabled'];
        $this->eshopToTargitoAccountId = $targitoConfig['eshop_to_targito_account_id'];
        $this->eshopToTargitoPassword = $targitoConfig['eshop_to_targito_password'];
    }
}
