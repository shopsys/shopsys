<?php

declare(strict_types=1);

namespace App\Model\Administrator;

use Shopsys\FrameworkBundle\Model\Administrator\AdministratorTwoFactorAuthenticationFacade as BaseAdministratorTwoFactorAuthenticationFacade;

/**
 * @method void enableTwoFactorAuthenticationByEmail(\App\Model\Administrator\Administrator $administrator)
 * @method void enableTwoFactorAuthenticationByGoogleAuthenticator(\App\Model\Administrator\Administrator $administrator)
 * @method void disableTwoFactorAuthentication(\App\Model\Administrator\Administrator $administrator)
 * @method void renewGoogleAuthSecret(\App\Model\Administrator\Administrator $administrator)
 * @method string getQrCodeDataUri(\App\Model\Administrator\Administrator $administrator)
 * @method bool isGoogleAuthenticatorCodeValid(\App\Model\Administrator\Administrator $administrator, string $code)
 * @method void generateAndSendEmail(\App\Model\Administrator\Administrator $administrator)
 */
class AdministratorTwoFactorAuthenticationFacade extends BaseAdministratorTwoFactorAuthenticationFacade
{
}
