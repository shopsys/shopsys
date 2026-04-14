import { CombinedError } from 'urql';

const authErrorUserCodes = ['expired-token', 'invalid-token'] as const;

export const isAuthError = (error: CombinedError | undefined): boolean => {
    if (!error) {
        return false;
    }

    if (error.response?.status === 401) {
        return true;
    }

    return error.graphQLErrors.some((graphQlError) =>
        authErrorUserCodes.some((userCode) => graphQlError.extensions.userCode === userCode),
    );
};
