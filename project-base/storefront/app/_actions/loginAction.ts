'use server';

import { createMutation } from 'app/_urql/urql-dto';
import { setTokensToCookies } from 'app/_utils/setTokensToCookies';
import {
    LoginMutationDocument,
    TypeLoginMutation,
    TypeLoginMutationVariables,
} from 'graphql/requests/auth/mutations/LoginMutation.ssr';
import { CombinedError } from 'urql';

type LoginActionResult = {
    error: CombinedError | undefined;
    showCartMergeInfo: boolean;
};

export async function loginAction(
    variables: TypeLoginMutationVariables,
    rewriteUrl?: string, // TODO: when login outside of login page
): Promise<LoginActionResult> {
    const response = await createMutation<TypeLoginMutation, TypeLoginMutationVariables>(
        LoginMutationDocument,
        variables,
    );

    if (response.error) {
        return {
            error: {
                name: response.error.name,
                message: response.error.message,
                graphQLErrors: response.error.graphQLErrors,
            },
            showCartMergeInfo: false,
        };
    }

    if (response.data) {
        const accessToken = response.data.Login.tokens.accessToken;
        const refreshToken = response.data.Login.tokens.refreshToken;

        setTokensToCookies(accessToken, refreshToken);

        return {
            error: undefined,
            showCartMergeInfo: response.data.Login.showCartMergeInfo,
        };
    }

    return {
        error: undefined,
        showCartMergeInfo: false,
    };
}
