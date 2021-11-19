import {
    CombinedError,
    makeOperation,
    Operation,
    OperationContext,
    OperationResult,
    TypedDocumentNode,
} from '@urql/core';
import { removeTokensFromCookies, setTokensToCookie } from 'utils/Auth/TokensFromCookies';
import { DocumentNode } from 'graphql';
import { GetServerSidePropsContext } from 'next';
import { parseCookies } from 'nookies';

type TokenType = {
    accessToken: string;
    refreshToken: string;
};

type GetAuthExchangeOptionsReturnType = {
    addAuthToOperation: (params: { authState: TokenType | null; operation: Operation }) => Operation;
    willAuthError: (params: { authState: TokenType | null }) => boolean;
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

const getAuthExchangeOptions = (context?: GetServerSidePropsContext): GetAuthExchangeOptionsReturnType => ({
    addAuthToOperation: (params: { authState: TokenType | null; operation: Operation }): Operation => {
        if (!params.authState) {
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
                    Authorization: 'Bearer ' + params.authState.accessToken,
                },
            },
        });
    },
    willAuthError: (params: { authState: TokenType | null }): boolean => {
        return !params.authState;
    },
    didAuthError: (params: { error: CombinedError }): boolean => {
        return params.error.response.status === 401;
    },
    getAuth: async (params: {
        authState: TokenType | null;
        // eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
        mutate: <Data = any, Variables extends Record<string, unknown> = Record<string, unknown>>(
            query: DocumentNode | TypedDocumentNode<Data, Variables> | string,
            variables?: Variables,
            context?: Partial<OperationContext>,
        ) => Promise<OperationResult<Data>>;
    }): Promise<TokenType | null> => {
        // for initial launch, fetch the auth state from local storage
        if (!params.authState) {
            try {
                const cookies = parseCookies(context);
                const accessToken = cookies.accessToken ?? undefined;
                const refreshToken = cookies.refreshToken ?? undefined;
                if (accessToken !== undefined && refreshToken !== undefined) {
                    return { accessToken, refreshToken };
                }
            } catch (e) {
                // eslint-disable-next-line no-console
                console.error(e);
            }

            return null;
        }

        /*
         * the following code gets executed when an auth error has occurred
         * we should refresh the token if possible and return a new auth state
         * If refresh fails, we should log out
         */
        const refreshMutation = `mutation RefreshTokens($refreshToken: String!) {
        RefreshTokens(input: {refreshToken: $refreshToken}) {
            accessToken
            refreshToken
        }` as const;
        try {
            const result = await params.mutate<{ RefreshTokens: TokenType }, { refreshToken: string }>(
                refreshMutation,
                {
                    refreshToken: params.authState?.refreshToken,
                },
            );

            if (result.data?.RefreshTokens !== undefined) {
                setTokensToCookie(
                    result.data.RefreshTokens.accessToken,
                    result.data.RefreshTokens.refreshToken,
                    context,
                );

                return {
                    accessToken: result.data.RefreshTokens.accessToken,
                    refreshToken: result.data.RefreshTokens.refreshToken,
                };
            }
        } catch (e) {
            // eslint-disable-next-line no-console
            console.error(e);
        }
        // otherwise, if refresh fails, log clear storage and log out
        removeTokensFromCookies();
        return null;
    },
});

export default getAuthExchangeOptions;
