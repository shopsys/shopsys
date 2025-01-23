'use server';

import { createMutation } from 'app/_urql/urql-dto';
import {
    AddProductToListMutationDocument,
    TypeAddProductToListMutation,
    TypeAddProductToListMutationVariables,
} from 'graphql/requests/productLists/mutations/AddProductToListMutation.ssr';
import { CombinedError } from 'urql';

type addProductToListActionResult = {
    data: TypeAddProductToListMutation | undefined;
    error: CombinedError | undefined;
};

export async function addProductToListAction(
    variables: TypeAddProductToListMutationVariables,
): Promise<addProductToListActionResult> {
    const response = await createMutation<TypeAddProductToListMutation, TypeAddProductToListMutationVariables>(
        AddProductToListMutationDocument,
        variables,
    );

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
