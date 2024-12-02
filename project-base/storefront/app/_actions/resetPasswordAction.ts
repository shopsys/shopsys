'use server';

import { createMutation } from 'app/_urql/urql-dto';
import {
    PasswordRecoveryMutationDocument,
    TypePasswordRecoveryMutation,
    TypePasswordRecoveryMutationVariables,
} from 'graphql/requests/passwordRecovery/mutations/PasswordRecoveryMutation.ssr';
import { CombinedError } from 'urql';

type resetPasswordActionResult = {
    error: CombinedError | undefined;
};

export async function resetPasswordAction(
    variables: TypePasswordRecoveryMutationVariables,
): Promise<resetPasswordActionResult> {
    const response = await createMutation<TypePasswordRecoveryMutation, TypePasswordRecoveryMutationVariables>(
        PasswordRecoveryMutationDocument,
        variables,
    );

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
}
