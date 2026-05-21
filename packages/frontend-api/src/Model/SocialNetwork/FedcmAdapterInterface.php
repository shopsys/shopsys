<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\SocialNetwork;

use Hybridauth\User\Profile;

interface FedcmAdapterInterface
{
    /**
     * @return string LoginTypeEnum value (e.g. 'google', 'seznam')
     */
    public function getLoginType(): string;

    /**
     * Verifies a credential delivered by the browser through the FedCM flow and returns the HybridAuth Profile.
     *
     * The credential payload depends on the implementation:
     *  - Google: JWT id_token
     *  - Seznam: OAuth authorization code
     *
     * When `$expectedNonce` is provided and the credential format supports it (JWT id_token), the adapter MUST
     * verify that the credential's nonce claim matches and reject the credential otherwise. Adapters whose
     * credential format does not carry a nonce (e.g. OAuth authorization code) MAY ignore the parameter.
     */
    public function getUserProfileFromFedcmCredential(string $credential, ?string $expectedNonce = null): Profile;

    /**
     * Returns the IdP-specific default values for the FedCM `params` object that the storefront passes to
     * navigator.credentials.get(). The provider class is the authoritative source for what its own IdP requires;
     * project YAML config can still override or extend these via `fedcm.params`.
     *
     * @return array<string, string>
     */
    public static function getDefaultFedcmParams(): array;
}
