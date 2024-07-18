<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\SocialNetwork;

use Hybridauth\Data;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Provider\Seznam as BaseSeznam;
use Hybridauth\User;

/**
 * @see https://github.com/hybridauth/hybridauth/pull/1388 copy from this pull request, after accepting this pull request and updating version where are these changes applied, you can delete this file
 */
class Seznam extends BaseSeznam
{
    /**
     * {@inheritdoc}
     */
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
     * @param \Hybridauth\Data\Collection $data
     * @return string|null
     */
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
