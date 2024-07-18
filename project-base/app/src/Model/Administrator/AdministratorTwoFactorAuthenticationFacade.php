<?php

declare(strict_types=1);

namespace App\Model\Administrator;

use Shopsys\FrameworkBundle\Model\Administrator\AdministratorTwoFactorAuthenticationFacade as BaseAdministratorTwoFactorAuthenticationFacade;

/**
 * @method enableTwoFactorAuthenticationByEmail(\App\Model\Administrator\Administrator $administrator)
 * @method enableTwoFactorAuthenticationByGoogleAuthenticator(\App\Model\Administrator\Administrator $administrator)
 * @method disableTwoFactorAuthentication(\App\Model\Administrator\Administrator $administrator)
 * @method renewGoogleAuthSecret(\App\Model\Administrator\Administrator $administrator)
 * @method string getQrCodeDataUri(\App\Model\Administrator\Administrator $administrator)
 * @method bool isGoogleAuthenticatorCodeValid(\App\Model\Administrator\Administrator $administrator, string $code)
 * @method generateAndSendEmail(\App\Model\Administrator\Administrator $administrator)
 */
class AdministratorTwoFactorAuthenticationFacade extends BaseAdministratorTwoFactorAuthenticationFacade
{
}
