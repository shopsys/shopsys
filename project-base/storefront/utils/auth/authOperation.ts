import { DocumentNode } from 'graphql';
import { AuthContext } from 'types/auth';
import { makeOperation, Operation } from 'urql';
import {
    getAccessTokenFromCookies,
    getRefreshTokenFromCookies,
    hasRefreshTokenInCookies,
} from 'utils/auth/getTokensFromCookies';
import { DomainConfigType } from 'utils/domain/domainConfig';

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
export const addAuthToOperation = (
    operation: Operation,
    domainConfig: DomainConfigType,
    context?: AuthContext,
): Operation => {
    const accessToken = getAccessTokenFromCookies(domainConfig, context);

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
                'X-Auth-Token': `Bearer ${accessToken}`,
            },
        },
    });
};

export const willAuthError = (domainConfig: DomainConfigType, context?: AuthContext): boolean => {
    const accessToken = getAccessTokenFromCookies(domainConfig, context);
    const hasRefreshToken = context
        ? !!getRefreshTokenFromCookies(domainConfig, context)
        : hasRefreshTokenInCookies(domainConfig);

    // If we have a refresh token but no access token, we should refresh
    // This handles the case where access token expired but backend returns 200 with null instead of 401
    return hasRefreshToken && !accessToken;
};
