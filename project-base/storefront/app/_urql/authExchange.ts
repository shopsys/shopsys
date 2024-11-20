import { AuthConfig } from '@urql/exchange-auth';
import { CombinedError, makeOperation, Operation } from 'urql';
import { getTokensRSC } from 'utils/auth/getTokensFromRSC';

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

const refreshAuth = async (): Promise<void> => {
    // cannot write to cookies from server
    // only from Server Actions or Route Hanlders
    // https://nextjs.org/docs/14/app/api-reference/functions/cookies
    // eslint-disable-next-line no-console
    console.log('skip refreshAuth on server');
};

export const getAuthExchangeOptions = () => async (): Promise<AuthConfig> => {
    const { accessToken } = await getTokensRSC();

    return {
        addAuthToOperation: (operation) => addAuthToOperation(operation, accessToken),
        didAuthError,
        refreshAuth,
    };
};
