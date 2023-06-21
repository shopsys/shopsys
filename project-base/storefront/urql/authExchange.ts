import { CombinedError, makeOperation, Operation, OperationContext, OperationResult, TypedDocumentNode } from 'urql';
import { DocumentNode } from 'graphql';
import { RefreshTokensDocumentApi } from 'graphql/generated';
import { getTokensFromCookies, removeTokensFromCookies, setTokensToCookie } from 'helpers/auth/tokens';
import { GetServerSidePropsContext } from 'next';
import { TokenType } from 'urql/types';

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
const addAuthToOperation = (params: { authState: TokenType | null; operation: Operation }): Operation => {
    if (!params.authState || isRefreshTokenMutation(params.operation)) {
        return params.operation;
    }

    const fetchOptions =
        typeof params.operation.context.fetchOptions === 'function'
            ? params.operation.context.fetchOptions()
            : params.operation.context.fetchOptions || {};

    return makeOperation(params.operation.kind, params.operation, {
        ...params.operation.context,
        fetchOptions: {
            ...fetchOptions,
            headers: {
                ...fetchOptions.headers,
                'X-Auth-Token': 'Bearer ' + params.authState.accessToken,
            },
        },
    });
};

/**
 * Check whether error returned from API is an authentication error
 */
const didAuthError = (params: { error: CombinedError }): boolean => {
    return params.error.response?.status === 401;
};

const doTryRefreshToken = async (
    refreshToken: string,
    mutate: <Data = any, Variables extends Record<string, unknown> = Record<string, unknown>>(
        query: DocumentNode | TypedDocumentNode<Data, Variables> | string,
        variables?: Variables,
        context?: Partial<OperationContext>,
    ) => Promise<OperationResult<Data>>,
    context?: GetServerSidePropsContext,
): Promise<TokenType | null> => {
    try {
        const result = await mutate(RefreshTokensDocumentApi, { refreshToken });

        const { data } = result;

        if (data?.RefreshTokens !== undefined) {
            setTokensToCookie(data.RefreshTokens.accessToken, data.RefreshTokens.refreshToken, context);

            return {
                accessToken: data.RefreshTokens.accessToken,
                refreshToken: data.RefreshTokens.refreshToken,
            };
        }
    } catch (e) {
        // eslint-disable-next-line no-console
        console.error(e);
    }

    removeTokensFromCookies(context);
    return null;
};

/**
 * Factory for getAuth function, so it's possible to pass context
 * Initial requests with refreshToken only are refreshed immediately
 * Subsequent requests are refreshed when necessary
 */
const createGetAuth = (context?: GetServerSidePropsContext) => {
    return async (params: {
        authState: TokenType | null;
        mutate: <Data = any, Variables extends Record<string, unknown> = Record<string, unknown>>(
            query: DocumentNode | TypedDocumentNode<Data, Variables> | string,
            variables?: Variables,
            context?: Partial<OperationContext>,
        ) => Promise<OperationResult<Data>>;
    }): Promise<TokenType | null> => {
        const { accessToken, refreshToken } = getTokensFromCookies(context);

        if (!params.authState) {
            try {
                if (refreshToken === undefined) {
                    return null;
                }

                if (accessToken === undefined) {
                    return doTryRefreshToken(refreshToken, params.mutate, context);
                }

                return { accessToken, refreshToken };
            } catch (e) {
                // eslint-disable-next-line no-console
                console.error(e);
            }

            return null;
        }

        if (refreshToken !== undefined && params.authState.refreshToken !== refreshToken) {
            return doTryRefreshToken(refreshToken, params.mutate, context);
        }

        return doTryRefreshToken(params.authState.refreshToken, params.mutate, context);
    };
};

type GetAuthExchangeOptionsReturnType = {
    addAuthToOperation: (params: { authState: TokenType | null; operation: Operation }) => Operation;
    didAuthError: (params: { error: CombinedError }) => boolean;
    getAuth: (params: {
        authState: TokenType | null;
        mutate: <Data = any, Variables extends Record<string, unknown> = Record<string, unknown>>(
            query: DocumentNode | TypedDocumentNode<Data, Variables> | string,
            variables?: Variables,
            context?: Partial<OperationContext>,
        ) => Promise<OperationResult<Data>>;
    }) => Promise<TokenType | null>;
};

export const getAuthExchangeOptions = (context?: GetServerSidePropsContext): GetAuthExchangeOptionsReturnType => ({
    addAuthToOperation,
    didAuthError,
    getAuth: createGetAuth(context),
});
