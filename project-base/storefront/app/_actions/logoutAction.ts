'use server';

import { createMutation } from 'app/_urql/urql-dto';
import {
    LogoutMutationDocument,
    TypeLogoutMutation,
    TypeLogoutMutationVariables,
} from 'graphql/requests/auth/mutations/LogoutMutation.ssr';
import { CombinedError } from 'urql';

type LogoutActionResult = {
    error: CombinedError | undefined;
};

export const logoutAction = async (): Promise<LogoutActionResult> => {
    const response = await createMutation<TypeLogoutMutation, TypeLogoutMutationVariables>(LogoutMutationDocument, {});

    if (response.error) {
        return {
            error: {
                name: response.error.name,
                message: response.error.message,
                graphQLErrors: response.error.graphQLErrors,
            },
        };
    }

    return {
        error: undefined,
    };
};
