'use server';

import { createMutation } from 'app/_urql/urql-dto';
import { setTokensToCookies } from 'app/_utils/setTokensToCookies';
import {
    RegistrationMutationDocument,
    TypeRegistrationMutation,
    TypeRegistrationMutationVariables,
} from 'graphql/requests/registration/mutations/RegistrationMutation.ssr';
import { CombinedError } from 'urql';

type RegistrationActionResult = {
    error: CombinedError | undefined;
    showCartMergeInfo: boolean;
};

export async function registrationAction(
    variables: TypeRegistrationMutationVariables,
): Promise<RegistrationActionResult> {
    const response = await createMutation<TypeRegistrationMutation, TypeRegistrationMutationVariables>(
        RegistrationMutationDocument,
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
        const accessToken = response.data.Register.tokens.accessToken;
        const refreshToken = response.data.Register.tokens.refreshToken;

        setTokensToCookies(accessToken, refreshToken);

        return {
            error: undefined,
            showCartMergeInfo: response.data.Register.showCartMergeInfo,
        };
    }

    return {
        error: undefined,
        showCartMergeInfo: false,
    };
}
