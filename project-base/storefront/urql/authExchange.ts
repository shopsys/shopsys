import { ParsedUrlQuery } from 'node:querystring';
import { AuthConfig, AuthUtilities } from '@urql/exchange-auth';
import { DocumentNode } from 'graphql';
import {
    RefreshTokensDocument,
    TypeRefreshTokens,
    TypeRefreshTokensVariables,
} from 'graphql/requests/auth/mutations/RefreshTokensMutation.generated';
import { GetServerSidePropsContext, NextPageContext, PreviewData } from 'next';
import { CombinedError, makeOperation, Operation } from 'urql';
import { getTokensFromCookies } from 'utils/auth/getTokensFromCookies';
import { removeTokensFromCookies } from 'utils/auth/removeTokensFromCookies';
import { setTokensToCookies } from 'utils/auth/setTokensToCookies';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { isAuthError } from 'utils/errors/isAuthError';

const isRefreshTokenMutation = (operation: Operation) => {
    const query = operation.query as DocumentNode;

    return (
        operation.kind === 'mutation' &&
        query.definitions.some((def) => {
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
    domainConfig: DomainConfigType,
    context?: GetServerSidePropsContext<ParsedUrlQuery, PreviewData> | NextPageContext | undefined,
): Operation => {
    const { accessToken, refreshToken } = getTokensFromCookies(domainConfig, context);

    if (!accessToken || !refreshToken || isRefreshTokenMutation(operation)) {
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
                'X-Auth-Token': `Bearer ${accessToken}`,
            },
        },
    });
};

/**
 * Check whether error returned from API is an authentication error
 */
const didAuthError = (error: CombinedError): boolean => {
    return isAuthError(error);
};

const doTryRefreshToken = async (
    refreshToken: string,
    mutate: AuthUtilities['mutate'],
    domainConfig: DomainConfigType,
    context?: GetServerSidePropsContext | NextPageContext,
): Promise<void> => {
    const { data: refreshTokenData } = await mutate<TypeRefreshTokens, TypeRefreshTokensVariables>(
        RefreshTokensDocument,
        { refreshToken },
    );

    if (!refreshTokenData?.RefreshTokens) {
        removeTokensFromCookies(domainConfig, context);

        if (typeof window !== 'undefined') {
            window.location.reload();
        }

        return;
    }

    setTokensToCookies(
        refreshTokenData.RefreshTokens.accessToken,
        refreshTokenData.RefreshTokens.refreshToken,
        domainConfig,
        context,
    );
};

const refreshAuth = async (
    authUtilities: AuthUtilities,
    domainConfig: DomainConfigType,
    context?: GetServerSidePropsContext | NextPageContext,
): Promise<void> => {
    const { refreshToken } = getTokensFromCookies(domainConfig, context);
    try {
        if (!refreshToken) {
            if (typeof window !== 'undefined') {
                window.location.reload();
            }

            return;
        }

        await doTryRefreshToken(refreshToken, authUtilities.mutate, domainConfig, context);
    } catch (e) {
        // biome-ignore lint/suspicious/noConsole: intentional error logging in development
        console.error(e);
    }
};

const willAuthError = (
    domainConfig: DomainConfigType,
    context?: GetServerSidePropsContext | NextPageContext,
): boolean => {
    const { accessToken, refreshToken } = getTokensFromCookies(domainConfig, context);

    // If we have a refresh token but no access token, we should refresh
    // This handles the case where access token expired but backend returns 200 with null instead of 401
    return !!refreshToken && !accessToken;
};

export const getAuthExchangeOptions =
    (domainConfig: DomainConfigType, context?: GetServerSidePropsContext | NextPageContext) =>
    async (authUtilities: AuthUtilities): Promise<AuthConfig> => ({
        addAuthToOperation: (operation) => addAuthToOperation(operation, domainConfig, context),
        didAuthError,
        willAuthError: () => willAuthError(domainConfig, context),
        refreshAuth: () => refreshAuth(authUtilities, domainConfig, context),
    });
