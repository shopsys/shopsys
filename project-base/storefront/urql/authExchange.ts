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
import { clearAuthCookies, getAuthMutationFetcher } from 'utils/auth/authMutationFetcher';
import {
    getAccessTokenFromCookies,
    getRefreshTokenFromCookies,
    hasRefreshTokenInCookies,
} from 'utils/auth/getTokensFromCookies';
import { removeAccessTokenFromCookies, removeTokensFromCookies } from 'utils/auth/removeTokensFromCookies';
import { setTokensToCookies } from 'utils/auth/setTokensToCookies';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { isAuthError } from 'utils/errors/isAuthError';

const HTTP_ONLY_REFRESH_TOKEN_PLACEHOLDER = '';
const AUTH_REFRESH_TIMEOUT = 10_000;

const withAuthRefreshLock = async (domainId: number, refresh: () => Promise<void>): Promise<void> => {
    if (typeof window === 'undefined' || typeof navigator === 'undefined' || !navigator.locks?.request) {
        await refresh();

        return;
    }

    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), AUTH_REFRESH_TIMEOUT);
    let lockAcquired = false;

    try {
        await navigator.locks.request(`shopsys-auth-refresh-${domainId}`, { signal: controller.signal }, async () => {
            lockAcquired = true;
            clearTimeout(timeout);
            await refresh();
        });
    } catch (error) {
        if (lockAcquired) {
            throw error;
        }

        // Restricted browser contexts or a suspended lock holder must not block authentication indefinitely.
        await refresh();
    } finally {
        clearTimeout(timeout);
    }
};

const withAuthRefreshTimeout = async (refresh: (signal: AbortSignal) => Promise<void>): Promise<void> => {
    const controller = new AbortController();
    let timeout: ReturnType<typeof setTimeout> | undefined;

    try {
        await Promise.race([
            refresh(controller.signal),
            new Promise<never>((_resolve, reject) => {
                timeout = setTimeout(() => {
                    controller.abort();
                    reject(new Error('Authentication refresh timed out'));
                }, AUTH_REFRESH_TIMEOUT);
            }),
        ]);
    } finally {
        clearTimeout(timeout);
    }
};

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
    signal?: AbortSignal,
): Promise<void> => {
    const { data: refreshTokenData, error } = await mutate<TypeRefreshTokens, TypeRefreshTokensVariables>(
        RefreshTokensDocument,
        { refreshToken },
        context
            ? undefined
            : {
                  fetch: (input, init) => getAuthMutationFetcher(domainConfig)(input, { ...init, signal }),
              },
    );

    if (signal?.aborted) {
        return;
    }

    if (error?.networkError) {
        throw error.networkError;
    }

    if (!refreshTokenData?.RefreshTokens) {
        if (context) {
            removeTokensFromCookies(domainConfig, context);
        } else {
            removeAccessTokenFromCookies(domainConfig);
        }

        if (typeof window !== 'undefined') {
            window.location.reload();
        }

        return;
    }

    if (context) {
        setTokensToCookies(
            refreshTokenData.RefreshTokens.accessToken,
            refreshTokenData.RefreshTokens.refreshToken,
            domainConfig,
            context,
        );
    }
};

const doRefreshAuth = async (
    authUtilities: AuthUtilities,
    domainConfig: DomainConfigType,
    context?: GetServerSidePropsContext | NextPageContext,
    signal?: AbortSignal,
): Promise<void> => {
    const refreshToken = context
        ? getRefreshTokenFromCookies(domainConfig, context)
        : hasRefreshTokenInCookies(domainConfig)
          ? HTTP_ONLY_REFRESH_TOKEN_PLACEHOLDER
          : undefined;

    if (refreshToken === undefined) {
        if (context) {
            removeTokensFromCookies(domainConfig, context);
        } else {
            removeAccessTokenFromCookies(domainConfig);
            await clearAuthCookies(domainConfig, signal);
        }

        if (typeof window !== 'undefined' && !signal?.aborted) {
            window.location.reload();
        }

        return;
    }

    await doTryRefreshToken(refreshToken, authUtilities.mutate, domainConfig, context, signal);
};

const getAccessTokenBeforeRefresh = (
    domainConfig: DomainConfigType,
    rejectedOperation?: Operation,
): string | undefined => {
    if (!rejectedOperation) {
        return getAccessTokenFromCookies(domainConfig);
    }

    const fetchOptions =
        typeof rejectedOperation.context.fetchOptions === 'function'
            ? rejectedOperation.context.fetchOptions()
            : rejectedOperation.context.fetchOptions;

    // An auth error may arrive after another tab has already rotated the cookie.
    return new Headers(fetchOptions?.headers).get('X-Auth-Token')?.replace(/^Bearer /, '');
};

const refreshAuth = async (
    authUtilities: AuthUtilities,
    domainConfig: DomainConfigType,
    context?: GetServerSidePropsContext | NextPageContext,
    rejectedOperation?: Operation,
): Promise<void> => {
    try {
        if (context) {
            await doRefreshAuth(authUtilities, domainConfig, context);

            return;
        }

        const previousAccessToken = getAccessTokenBeforeRefresh(domainConfig, rejectedOperation);

        await withAuthRefreshLock(domainConfig.domainId, async () => {
            const currentAccessToken = getAccessTokenFromCookies(domainConfig);

            // The auth endpoint writes shared cookies before the other tab releases its lock.
            if (currentAccessToken && currentAccessToken !== previousAccessToken) {
                return;
            }

            await withAuthRefreshTimeout((signal) => doRefreshAuth(authUtilities, domainConfig, undefined, signal));
        });
    } catch (e) {
        // biome-ignore lint/suspicious/noConsole: intentional error logging in development
        console.error(e);
        // URQL must fail queued requests rather than retry them with the rejected token and log the user out.
        throw e;
    }
};

const willAuthError = (
    domainConfig: DomainConfigType,
    context?: GetServerSidePropsContext | NextPageContext,
): boolean => {
    const accessToken = getAccessTokenFromCookies(domainConfig, context);
    const hasRefreshToken = context
        ? !!getRefreshTokenFromCookies(domainConfig, context)
        : hasRefreshTokenInCookies(domainConfig);

    // If we have a refresh token but no access token, we should refresh
    // This handles the case where access token expired but backend returns 200 with null instead of 401
    return hasRefreshToken && !accessToken;
};

export const getAuthExchangeOptions =
    (domainConfig: DomainConfigType, context?: GetServerSidePropsContext | NextPageContext) =>
    async (authUtilities: AuthUtilities): Promise<AuthConfig> => {
        let rejectedOperation: Operation | undefined;

        return {
            addAuthToOperation: (operation) => addAuthToOperation(operation, domainConfig, context),
            didAuthError: (error, operation) => {
                if (!didAuthError(error)) {
                    return false;
                }

                rejectedOperation = operation;

                return true;
            },
            willAuthError: () => willAuthError(domainConfig, context),
            refreshAuth: () => {
                const operation = rejectedOperation;
                rejectedOperation = undefined;

                return refreshAuth(authUtilities, domainConfig, context, operation);
            },
        };
    };
