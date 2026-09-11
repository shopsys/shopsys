<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\OAuth;

use Shopsys\FrameworkBundle\Component\ArrayUtils\ArrayHelper;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
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
        $authorizationRequestData->clientId = ExtendedClassNameResolver::resolve(ArrayHelper::class)::getStringOrNull($query, 'client_id');
        $authorizationRequestData->codeChallenge = ExtendedClassNameResolver::resolve(ArrayHelper::class)::getStringOrNull($query, 'code_challenge');
        $authorizationRequestData->redirectUri = ExtendedClassNameResolver::resolve(ArrayHelper::class)::getStringOrNull($query, 'redirect_uri');
        $authorizationRequestData->state = ExtendedClassNameResolver::resolve(ArrayHelper::class)::getStringOrNull($query, 'state');

        return $authorizationRequestData;
    }
}
