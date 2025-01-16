'use server';

import { createMutation } from 'app/_urql/urql-dto';
import {
    CreateWatchdogMutationDocument,
    TypeCreateWatchdogMutation,
    TypeCreateWatchdogMutationVariables,
} from 'graphql/requests/watchDog/mutations/CreateWatchdogMutation.ssr';
import { CombinedError } from 'urql';

type createWatchDogActionResult = {
    error: CombinedError | undefined;
};

export async function createWatchDogAction(
    variables: TypeCreateWatchdogMutationVariables,
): Promise<createWatchDogActionResult> {
    const response = await createMutation<TypeCreateWatchdogMutation, TypeCreateWatchdogMutationVariables>(
        CreateWatchdogMutationDocument,
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

    if (response.data) {
        return {
            error: undefined,
        };
    }

    return {
        error: undefined,
    };
}
