import { AuthUtilities } from '@urql/exchange-auth';
import {
    RefreshTokensDocument,
    TypeRefreshTokens,
    TypeRefreshTokensVariables,
} from 'graphql/requests/auth/mutations/RefreshTokensMutation.generated';
import { AuthContext } from 'types/auth';
import { Operation } from 'urql';
import { clearAuthCookies, getAuthMutationFetcher } from 'utils/auth/authMutationFetcher';
import {
    getAccessTokenFromCookies,
    getRefreshTokenFromCookies,
    hasRefreshTokenInCookies,
} from 'utils/auth/getTokensFromCookies';
import { removeAccessTokenFromCookies, removeTokensFromCookies } from 'utils/auth/removeTokensFromCookies';
import { setTokensToCookies } from 'utils/auth/setTokensToCookies';
import { withAuthLock } from 'utils/auth/withAuthLock';
import { DomainConfigType } from 'utils/domain/domainConfig';

const HTTP_ONLY_REFRESH_TOKEN_PLACEHOLDER = '';

const refreshTokens = async (
    mutate: AuthUtilities['mutate'],
    domainConfig: DomainConfigType,
    context?: AuthContext,
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

    const { data, error } = await mutate<TypeRefreshTokens, TypeRefreshTokensVariables>(
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

    const tokens = data?.RefreshTokens;

    if (!tokens) {
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
        setTokensToCookies(tokens.accessToken, tokens.refreshToken, domainConfig, context);
    }
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

export const refreshAuth = async (
    authUtilities: AuthUtilities,
    domainConfig: DomainConfigType,
    context?: AuthContext,
    rejectedOperation?: Operation,
): Promise<void> => {
    try {
        if (context) {
            await refreshTokens(authUtilities.mutate, domainConfig, context);

            return;
        }

        const previousAccessToken = getAccessTokenBeforeRefresh(domainConfig, rejectedOperation);

        await withAuthLock(
            domainConfig.domainId,
            async (signal) => {
                const currentAccessToken = getAccessTokenFromCookies(domainConfig);

                // The auth endpoint writes shared cookies before the other tab releases its lock.
                if (currentAccessToken && currentAccessToken !== previousAccessToken) {
                    return;
                }

                await refreshTokens(authUtilities.mutate, domainConfig, undefined, signal);
            },
            'Authentication refresh timed out',
        );
    } catch (e) {
        // biome-ignore lint/suspicious/noConsole: intentional error logging in development
        console.error(e);
        // URQL must fail queued requests rather than retry them with the rejected token and log the user out.
        throw e;
    }
};
