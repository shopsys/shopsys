import { AuthConfig, AuthUtilities } from '@urql/exchange-auth';
import {
    RefreshTokensDocument,
    TypeRefreshTokens,
    TypeRefreshTokensVariables,
} from 'graphql/requests/auth/mutations/RefreshTokensMutation.generated';
import { CombinedError, makeOperation, Operation } from 'urql';
import { getTokensFromCookiesServer } from 'utils/auth/getTokensFromCookiesServer';
import { removeTokensFromCookiesServer } from 'utils/auth/removeTokensFromCookiesServer';
import { setTokensToCookiesServer } from 'utils/auth/setTokensToCookiesServer';

const isRefreshTokenMutation = (operation: Operation) => {
    return (
        operation.kind === 'mutation' &&
        operation.query.definitions.some((def) => {
            if ('name' in def) {
                return def.name?.value === 'RefreshTokens';
            }

            return false;
        })
    );
};

/**
 * Add access token to each request if authState is valid
 * Access token is not added to the RefreshTokens mutation (allows refreshing tokens with invalid access token)
 */
const addAuthToOperation = (operation: Operation, accessToken: string | undefined): Operation => {
    if (!accessToken || isRefreshTokenMutation(operation)) {
        return operation;
    }

    const fetchOptions =
        typeof operation.context.fetchOptions === 'function'
            ? operation.context.fetchOptions()
            : operation.context.fetchOptions || {};

    return makeOperation(operation.kind, operation, {
        ...operation.context,
        fetchOptions: {
            ...fetchOptions,
            headers: {
                ...fetchOptions.headers,
                'X-Auth-Token': 'Bearer ' + accessToken,
            },
        },
    });
};

/**
 * Check whether error returned from API is an authentication error
 */
const didAuthError = (error: CombinedError): boolean => {
    return error.response?.status === 401;
};

const doTryRefreshToken = async (refreshToken: string, mutate: AuthUtilities['mutate']): Promise<void> => {
    const { data: refreshTokenData } = await mutate<TypeRefreshTokens, TypeRefreshTokensVariables>(
        RefreshTokensDocument,
        { refreshToken },
    );

    if (!refreshTokenData?.RefreshTokens) {
        removeTokensFromCookiesServer();

        if (typeof window !== 'undefined') {
            window.location.reload();
        }

        return;
    }

    setTokensToCookiesServer(refreshTokenData.RefreshTokens.accessToken, refreshTokenData.RefreshTokens.refreshToken);
};

const refreshAuth = async (authUtilities: AuthUtilities): Promise<void> => {
    const { refreshToken } = await getTokensFromCookiesServer();
    try {
        if (!refreshToken) {
            if (typeof window !== 'undefined') {
                window.location.reload();
            }

            return;
        }

        await doTryRefreshToken(refreshToken, authUtilities.mutate);
    } catch (e) {
        // eslint-disable-next-line no-console
        console.error(e);
    }
};

export const getAuthExchangeOptions =
    () =>
    async (authUtilities: AuthUtilities): Promise<AuthConfig> => {
        const { accessToken } = await getTokensFromCookiesServer();

        return {
            addAuthToOperation: (operation) => addAuthToOperation(operation, accessToken),
            didAuthError,
            refreshAuth: () => refreshAuth(authUtilities),
        };
    };
