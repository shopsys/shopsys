<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\OAuth;

use Shopsys\FrameworkBundle\Component\ArrayUtils\ArrayHelper;
use Symfony\Component\HttpFoundation\Request;

class McpOAuthAuthorizationRequestData
{
    public ?string $clientId = null;

    public ?string $codeChallenge = null;

    public ?string $redirectUri = null;

    public ?string $state = null;

    public static function createFromRequest(Request $request): self
    {
        $authorizationRequestData = new self();
        $query = $request->query->all();
        $authorizationRequestData->clientId = ArrayHelper::getStringOrNull($query, 'client_id');
        $authorizationRequestData->codeChallenge = ArrayHelper::getStringOrNull($query, 'code_challenge');
        $authorizationRequestData->redirectUri = ArrayHelper::getStringOrNull($query, 'redirect_uri');
        $authorizationRequestData->state = ArrayHelper::getStringOrNull($query, 'state');

        return $authorizationRequestData;
    }
}
