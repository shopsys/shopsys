'use server';

import { createMutation } from 'app/_urql/urql-dto';
import {
    RemoveProductFromListMutationDocument,
    TypeRemoveProductFromListMutation,
    TypeRemoveProductFromListMutationVariables,
} from 'graphql/requests/productLists/mutations/RemoveProductFromListMutation.ssr';
import { CombinedError } from 'urql';

type removeProductFromListActionResult = {
    data: TypeRemoveProductFromListMutation | undefined;
    error: CombinedError | undefined;
};

export async function removeProductFromListAction(
    variables: TypeRemoveProductFromListMutationVariables,
): Promise<removeProductFromListActionResult> {
    const response = await createMutation<
        TypeRemoveProductFromListMutation,
        TypeRemoveProductFromListMutationVariables
    >(RemoveProductFromListMutationDocument, variables);

    if (response.error) {
        return {
            data: undefined,
            error: {
                name: response.error.name,
                message: response.error.message,
                graphQLErrors: response.error.graphQLErrors,
            },
        };
    }

    return {
        data: response.data,
        error: undefined,
    };
}
