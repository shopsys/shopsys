import { AuthConfig, AuthUtilities } from '@urql/exchange-auth';
import { RefreshTokensDocumentApi } from 'graphql/generated';
import { getTokensFromCookies, removeTokensFromCookies, setTokensToCookie } from 'helpers/auth/tokens';
import { GetServerSidePropsContext, NextPageContext, PreviewData } from 'next';
import { ParsedUrlQuery } from 'querystring';
import { CombinedError, makeOperation, Operation } from 'urql';

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
const addAuthToOperation = (
    operation: Operation,
    context?: GetServerSidePropsContext<ParsedUrlQuery, PreviewData> | NextPageContext | undefined,
): Operation => {
    const { accessToken } = getTokensFromCookies(context);

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

const doTryRefreshToken = async (
    refreshToken: string,
    mutate: AuthUtilities['mutate'],
    context?: GetServerSidePropsContext | NextPageContext,
): Promise<void> => {
    const { data: refreshTokenData } = await mutate(RefreshTokensDocumentApi, { refreshToken });
    if (!refreshTokenData?.RefreshTokens) {
        removeTokensFromCookies(context);

        return;
    }

    setTokensToCookie(refreshTokenData.RefreshTokens.accessToken, refreshTokenData.RefreshTokens.refreshToken, context);
};

const refreshAuth = async (
    authUtilities: AuthUtilities,
    context?: GetServerSidePropsContext<ParsedUrlQuery, PreviewData> | NextPageContext | undefined,
): Promise<void> => {
    const { refreshToken } = getTokensFromCookies(context);
    try {
        if (!refreshToken) {
            return;
        }

        await doTryRefreshToken(refreshToken, authUtilities.mutate, context);
    } catch (e) {
        // eslint-disable-next-line no-console
        console.error(e);
    }
};

export const getAuthExchangeOptions =
    (context?: GetServerSidePropsContext | NextPageContext) =>
    async (authUtilities: AuthUtilities): Promise<AuthConfig> => ({
        addAuthToOperation: (operation) => addAuthToOperation(operation, context),
        didAuthError,
        refreshAuth: () => refreshAuth(authUtilities, context),
    });
