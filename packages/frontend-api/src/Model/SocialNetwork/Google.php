<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\SocialNetwork;

use Hybridauth\Data;
use Hybridauth\Exception\InvalidAccessTokenException;
use Hybridauth\Exception\InvalidApplicationCredentialsException;
use Hybridauth\Provider\Google as BaseGoogle;
use Hybridauth\User\Profile;
use Override;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\LoginTypeEnum;

class Google extends BaseGoogle implements FedcmAdapterInterface
{
    protected const string TOKENINFO_URL = 'https://oauth2.googleapis.com/tokeninfo';

    /**
     * @var string[]
     */
    protected const array VALID_ISSUERS = ['accounts.google.com', 'https://accounts.google.com'];

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getLoginType(): string
    {
        return LoginTypeEnum::GOOGLE;
    }

    /**
     * {@inheritdoc}
     *
     * Google's FedCM login_url fallback (used when the user is not signed in to Google in the browser) is built
     * from the `params` payload — without `scope` it constructs an incomplete OAuth URL and surfaces
     * "missing parameter: response_type" to the user.
     */
    #[Override]
    public static function getDefaultFedcmParams(): array
    {
        return [
            'scope' => 'openid email profile',
            'response_type' => 'code',
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getUserProfileFromFedcmCredential(string $credential, ?string $expectedNonce = null): Profile
    {
        $expectedClientId = $this->config->filter('keys')->get('id');

        if (!is_string($expectedClientId) || $expectedClientId === '') {
            throw new InvalidApplicationCredentialsException('Google client ID is not configured');
        }

        $this->httpClient->request(self::TOKENINFO_URL, 'GET', ['id_token' => $credential]);

        $httpStatus = $this->httpClient->getResponseHttpCode();

        if ($httpStatus !== 200) {
            throw new InvalidAccessTokenException(sprintf('Google tokeninfo endpoint returned HTTP %d', $httpStatus));
        }

        $responseBody = $this->httpClient->getResponseBody();
        $decoded = is_string($responseBody) ? json_decode($responseBody, true) : null;

        if (!is_array($decoded)) {
            throw new InvalidAccessTokenException('Google tokeninfo response is not valid JSON');
        }

        $data = new Data\Collection($decoded);

        if ($data->get('aud') !== $expectedClientId) {
            throw new InvalidAccessTokenException('Google id_token aud claim does not match the configured client ID');
        }

        if (!in_array($data->get('iss'), self::VALID_ISSUERS, true)) {
            throw new InvalidAccessTokenException('Google id_token iss claim is invalid');
        }

        $emailVerified = $data->get('email_verified');

        if ($emailVerified !== 'true' && $emailVerified !== true) {
            throw new InvalidAccessTokenException('Google id_token reports email as not verified');
        }

        if ($expectedNonce !== null && $data->get('nonce') !== $expectedNonce) {
            throw new InvalidAccessTokenException('Google id_token nonce does not match the expected nonce');
        }

        $profile = new Profile();
        $profile->identifier = $data->get('sub');
        $profile->email = $data->get('email');
        $profile->firstName = $data->get('given_name');
        $profile->lastName = $data->get('family_name');
        $profile->displayName = $data->get('name');
        $profile->photoURL = $data->get('picture');

        return $profile;
    }
}
