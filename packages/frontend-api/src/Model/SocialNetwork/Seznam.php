<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\SocialNetwork;

use Hybridauth\Data;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Provider\Seznam as BaseSeznam;
use Hybridauth\User;
use Override;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\LoginTypeEnum;

/**
 * @see https://github.com/hybridauth/hybridauth/pull/1388 copy from this pull request, after accepting this pull request and updating version where are these changes applied, you can delete this file
 */
class Seznam extends BaseSeznam implements FedcmAdapterInterface
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getUserProfile()
    {
        $response = $this->apiRequest('api/v1/user', 'GET', ['format' => 'json']);

        $data = new Data\Collection($response);

        if (!$data->exists('oauth_user_id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier = $data->get('oauth_user_id');
        $userProfile->email = $this->getEmailFromCollection($data);
        $userProfile->firstName = $data->get('firstname');
        $userProfile->lastName = $data->get('lastname');
        $userProfile->photoURL = $data->get('avatar_url');
        $userProfile->phone = $data->get('contact_phone');

        return $userProfile;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginType(): string
    {
        return LoginTypeEnum::SEZNAM;
    }

    /**
     * {@inheritdoc}
     *
     * Seznam's FedCM flow delivers an OAuth authorization code (no JWT, no nonce claim) — the `$expectedNonce`
     * parameter is therefore intentionally ignored.
     *
     * Per Seznam's developer docs (https://vyvojari.seznam.cz/oauth/fedcm), `redirect_uri` MUST NOT be sent on the
     * token endpoint for FedCM-issued codes — doing so triggers "redirect_uri mismatch". HybridAuth's OAuth2 base
     * adds `redirect_uri` to `tokenExchangeParameters` by default during initialize(), so we strip it before the
     * exchange call.
     */
    #[Override]
    public function getUserProfileFromFedcmCredential(string $credential, ?string $expectedNonce = null): User\Profile
    {
        unset($expectedNonce, $this->tokenExchangeParameters['redirect_uri']);

        $response = $this->exchangeCodeForAccessToken($credential);
        $this->validateAccessTokenExchange($response);

        return $this->getUserProfile();
    }

    protected function getEmailFromCollection(Data\Collection $data): ?string
    {
        $email = $data->get('email');

        if ($email !== null) {
            return $email;
        }

        $username = $data->get('username');
        $domain = $data->get('domain');

        if ($username === null || $domain === null) {
            return null;
        }

        return sprintf('%s@%s', $username, $domain);
    }
}
