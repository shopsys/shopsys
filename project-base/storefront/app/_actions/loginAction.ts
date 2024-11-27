'use server';

import { createMutation } from 'app/_urql/urql-dto';
import { setCookie } from 'cookies-next';
import {
    LoginMutationDocument,
    TypeLoginMutation,
    TypeLoginMutationVariables,
} from 'graphql/requests/auth/mutations/LoginMutation.ssr';
import { headers, cookies } from 'next/headers';
import { CombinedError } from 'urql';
import { getIsHttps, getProtocolFromServer } from 'utils/requestProtocol';

type LoginActionResult = {
    error: CombinedError | undefined;
    showCartMergeInfo: boolean;
};

export async function loginAction(
    variables: TypeLoginMutationVariables,
    rewriteUrl?: string,
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
        const protocol = getIsHttps(getProtocolFromServer(headers().get('host')!));

        setCookie('accessToken', accessToken, {
            cookies,
            path: '/',
            secure: protocol,
        });

        setCookie('refreshToken', refreshToken, {
            cookies,
            maxAge: 3600 * 24 * 14,
            path: '/',
            secure: protocol,
        });

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
