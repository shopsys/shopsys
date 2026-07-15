import { getPublicConfigProperty, getServerConfigProperty } from 'envConfig';
import { DocumentNode, print } from 'graphql';
import { LoginMutationDocument } from 'graphql/requests/auth/mutations/LoginMutation.generated';
import { LoginViaExchangeTokenMutationDocument } from 'graphql/requests/auth/mutations/LoginViaExchangeTokenMutation.generated';
import { LogoutMutationDocument } from 'graphql/requests/auth/mutations/LogoutMutation.generated';
import { RefreshTokensDocument } from 'graphql/requests/auth/mutations/RefreshTokensMutation.generated';
import { RecoverPasswordMutationDocument } from 'graphql/requests/passwordRecovery/mutations/RecoverPasswordMutation.generated';
import { RegistrationByOrderMutationDocument } from 'graphql/requests/registration/mutations/RegistrationByOrderMutation.generated';
import { RegistrationMutationDocument } from 'graphql/requests/registration/mutations/RegistrationMutation.generated';
import { NextApiRequest, NextApiResponse } from 'next';
import { AUTH_DOMAIN_ID_HEADER } from 'utils/auth/authConstants';
import { getAccessTokenFromCookies, getRefreshTokenFromCookies } from 'utils/auth/getTokensFromCookies';
import { removeTokensFromCookies } from 'utils/auth/removeTokensFromCookies';
import { setTokensToCookies } from 'utils/auth/setTokensToCookies';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { getExplicitPathDomainLocaleOrDefault, getInternalGraphqlEndpoint } from 'utils/domain/domainUtils';
import { getIpAddressFromRequest } from 'utils/serverSide/getIpAddressFromRequest';

enum AuthMutationOperationName {
    Login = 'LoginMutation',
    LoginViaExchangeToken = 'LoginViaExchangeTokenMutation',
    Logout = 'LogoutMutation',
    RecoverPassword = 'RecoverPasswordMutation',
    RefreshTokens = 'RefreshTokens',
    Registration = 'RegistrationMutation',
    RegistrationByOrder = 'RegistrationByOrderMutation',
}

const AUTH_MUTATION_DOCUMENTS = {
    [AuthMutationOperationName.Login]: LoginMutationDocument,
    [AuthMutationOperationName.LoginViaExchangeToken]: LoginViaExchangeTokenMutationDocument,
    [AuthMutationOperationName.Logout]: LogoutMutationDocument,
    [AuthMutationOperationName.RecoverPassword]: RecoverPasswordMutationDocument,
    [AuthMutationOperationName.RefreshTokens]: RefreshTokensDocument,
    [AuthMutationOperationName.RegistrationByOrder]: RegistrationByOrderMutationDocument,
    [AuthMutationOperationName.Registration]: RegistrationMutationDocument,
} satisfies Record<AuthMutationOperationName, DocumentNode>;

type AuthMutationRequest = {
    operationName: AuthMutationOperationName;
    variables: Record<string, unknown>;
};

type Tokens = {
    accessToken: string;
    refreshToken: string;
};

const getFirstHeaderValue = (headerValue: string | string[] | undefined): string | undefined => {
    const value = Array.isArray(headerValue) ? headerValue[0] : headerValue;

    return value?.split(',')[0]?.trim() || undefined;
};

const getRequestHost = (request: NextApiRequest): string | undefined => {
    return getFirstHeaderValue(request.headers['x-forwarded-host']) ?? getFirstHeaderValue(request.headers.host);
};

const isSameOriginRequest = (request: NextApiRequest): boolean => {
    const origin = getFirstHeaderValue(request.headers.origin);
    const requestHost = getRequestHost(request);
    const forwardedProtocol = getFirstHeaderValue(request.headers['x-forwarded-proto']);
    const isEncrypted = !!request.socket && 'encrypted' in request.socket && request.socket.encrypted === true;
    const requestProtocol = forwardedProtocol === 'https' || (!forwardedProtocol && isEncrypted) ? 'https' : 'http';

    if (!origin || !requestHost || !URL.canParse(origin)) {
        return false;
    }

    return new URL(origin).origin === `${requestProtocol}://${requestHost}`;
};

const getDomainConfig = (request: NextApiRequest): DomainConfigType | undefined => {
    const rawDomainId = getFirstHeaderValue(request.headers[AUTH_DOMAIN_ID_HEADER.toLowerCase()]);
    const domainId = Number(rawDomainId);
    const requestHost = getRequestHost(request)?.replace(/:3000$/, ':8000');

    if (!Number.isInteger(domainId) || !requestHost) {
        return undefined;
    }

    return getPublicConfigProperty('domains').find(
        (domainConfig) =>
            domainConfig.domainId === domainId &&
            new URL(domainConfig.url).host.replace(/:3000$/, ':8000') === requestHost,
    );
};

const isRecord = (value: unknown): value is Record<string, unknown> => {
    return typeof value === 'object' && value !== null && !Array.isArray(value);
};

const isAuthMutationOperationName = (value: unknown): value is AuthMutationOperationName => {
    return typeof value === 'string' && value in AUTH_MUTATION_DOCUMENTS;
};

const getAuthMutationRequest = (body: unknown): AuthMutationRequest | undefined => {
    let parsedBody = body;

    if (typeof body === 'string') {
        try {
            parsedBody = JSON.parse(body) as unknown;
        } catch {
            return undefined;
        }
    }

    if (!isRecord(parsedBody) || !isAuthMutationOperationName(parsedBody.operationName)) {
        return undefined;
    }

    return {
        operationName: parsedBody.operationName,
        variables: isRecord(parsedBody.variables) ? parsedBody.variables : {},
    };
};

const getNestedRecord = (record: Record<string, unknown>, key: string): Record<string, unknown> | undefined => {
    const value = record[key];

    return isRecord(value) ? value : undefined;
};

const getTokens = (
    responseBody: Record<string, unknown>,
    operationName: AuthMutationOperationName,
): Tokens | undefined => {
    const data = getNestedRecord(responseBody, 'data');

    if (!data) {
        return undefined;
    }

    let tokens: Record<string, unknown> | undefined;

    if (operationName === AuthMutationOperationName.Login) {
        const loginResult = getNestedRecord(data, 'Login');
        tokens = loginResult ? getNestedRecord(loginResult, 'tokens') : undefined;
    } else if (operationName === AuthMutationOperationName.Registration) {
        const registrationResult = getNestedRecord(data, 'Register');
        tokens = registrationResult ? getNestedRecord(registrationResult, 'tokens') : undefined;
    } else if (operationName === AuthMutationOperationName.RegistrationByOrder) {
        const registrationResult = getNestedRecord(data, 'RegisterByOrder');
        tokens = registrationResult ? getNestedRecord(registrationResult, 'tokens') : undefined;
    } else if (operationName === AuthMutationOperationName.RecoverPassword) {
        const recoverPasswordResult = getNestedRecord(data, 'RecoverPassword');
        tokens = recoverPasswordResult ? getNestedRecord(recoverPasswordResult, 'tokens') : undefined;
    } else if (operationName === AuthMutationOperationName.LoginViaExchangeToken) {
        tokens = getNestedRecord(data, 'LoginViaExchangeToken');
    } else if (operationName === AuthMutationOperationName.RefreshTokens) {
        tokens = getNestedRecord(data, 'RefreshTokens');
    }

    if (typeof tokens?.accessToken !== 'string' || typeof tokens.refreshToken !== 'string') {
        return undefined;
    }

    const result = {
        accessToken: tokens.accessToken,
        refreshToken: tokens.refreshToken,
    };

    tokens.refreshToken = '';

    return result;
};

const setNoStoreHeaders = (response: NextApiResponse): void => {
    response.setHeader('Cache-Control', 'no-store');
    response.setHeader('Vary', 'Origin');
};

const validateRequest = (request: NextApiRequest, response: NextApiResponse): DomainConfigType | undefined => {
    setNoStoreHeaders(response);

    if (request.method !== 'POST') {
        response.setHeader('Allow', 'POST');
        response.status(405).json({ error: 'Method not allowed' });
        return undefined;
    }

    if (!isSameOriginRequest(request)) {
        response.status(403).json({ error: 'Invalid request origin' });
        return undefined;
    }

    const domainConfig = getDomainConfig(request);

    if (!domainConfig) {
        response.status(400).json({ error: 'Invalid domain' });
        return undefined;
    }

    return domainConfig;
};

const getGraphqlEndpoint = (domainConfig: DomainConfigType): string => {
    const locale = getExplicitPathDomainLocaleOrDefault(domainConfig.url);

    return (
        getInternalGraphqlEndpoint(getServerConfigProperty('internalGraphqlEndpoint'), locale) ??
        domainConfig.publicGraphqlEndpoint
    );
};

const getGraphqlHeaders = (
    domainConfig: DomainConfigType,
    accessToken?: string,
    clientIpAddress?: string,
): Record<string, string> => {
    const publicGraphqlEndpoint = new URL(domainConfig.publicGraphqlEndpoint);

    return {
        'Content-Type': 'application/json',
        OriginalHost: publicGraphqlEndpoint.host,
        [AUTH_DOMAIN_ID_HEADER]: domainConfig.domainId.toString(),
        'X-Forwarded-Proto': publicGraphqlEndpoint.protocol === 'https:' ? 'on' : 'off',
        ...(clientIpAddress ? { 'X-Forwarded-For': clientIpAddress } : {}),
        ...(accessToken ? { 'X-Auth-Token': `Bearer ${accessToken}` } : {}),
    };
};

const getVariables = (
    authMutationRequest: AuthMutationRequest,
    domainConfig: DomainConfigType,
    context: { req: NextApiRequest; res: NextApiResponse },
): Record<string, unknown> | undefined => {
    if (authMutationRequest.operationName !== AuthMutationOperationName.RefreshTokens) {
        return authMutationRequest.variables;
    }

    const refreshToken = getRefreshTokenFromCookies(domainConfig, context);

    return refreshToken ? { refreshToken } : undefined;
};

export const handleAuthMutation = async (request: NextApiRequest, response: NextApiResponse): Promise<void> => {
    const domainConfig = validateRequest(request, response);

    if (!domainConfig) {
        return;
    }

    const authMutationRequest = getAuthMutationRequest(request.body);

    if (!authMutationRequest) {
        response.status(400).json({ error: 'Invalid authentication operation' });
        return;
    }

    const context = { req: request, res: response };
    const variables = getVariables(authMutationRequest, domainConfig, context);

    if (!variables) {
        removeTokensFromCookies(domainConfig, context);
        response.status(401).json({ errors: [{ message: 'Refresh token is not available.' }] });
        return;
    }

    const accessToken =
        authMutationRequest.operationName === AuthMutationOperationName.RefreshTokens
            ? undefined
            : getAccessTokenFromCookies(domainConfig, context);
    const clientIpAddress = getIpAddressFromRequest(request);
    const graphqlResponse = await fetch(getGraphqlEndpoint(domainConfig), {
        body: JSON.stringify({
            operationName: authMutationRequest.operationName,
            query: print(AUTH_MUTATION_DOCUMENTS[authMutationRequest.operationName]),
            variables,
        }),
        headers: getGraphqlHeaders(domainConfig, accessToken, clientIpAddress),
        method: 'POST',
    });

    let responseBody: unknown;

    try {
        responseBody = (await graphqlResponse.json()) as unknown;
    } catch {
        response.status(502).json({ error: 'Invalid response from Frontend API' });
        return;
    }

    if (!isRecord(responseBody)) {
        response.status(502).json({ error: 'Invalid response from Frontend API' });
        return;
    }

    const tokens = getTokens(responseBody, authMutationRequest.operationName);

    if (tokens) {
        setTokensToCookies(tokens.accessToken, tokens.refreshToken, domainConfig, context);
    } else if (authMutationRequest.operationName === AuthMutationOperationName.RefreshTokens) {
        removeTokensFromCookies(domainConfig, context);
    } else if (
        authMutationRequest.operationName === AuthMutationOperationName.Logout &&
        getNestedRecord(responseBody, 'data')?.Logout === true
    ) {
        removeTokensFromCookies(domainConfig, context);
    }

    response.status(graphqlResponse.status).json(responseBody);
};

export const handleClearAuthCookies = (request: NextApiRequest, response: NextApiResponse): void => {
    const domainConfig = validateRequest(request, response);

    if (!domainConfig) {
        return;
    }

    removeTokensFromCookies(domainConfig, { req: request, res: response });
    response.status(204).end();
};
