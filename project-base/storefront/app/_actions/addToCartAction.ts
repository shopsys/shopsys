'use server';

import { createMutation } from 'app/_urql/urql-dto';
import {
    AddToCartMutationDocument,
    TypeAddToCartMutation,
    TypeAddToCartMutationVariables,
} from 'graphql/requests/cart/mutations/AddToCartMutation.ssr';
import { CombinedError } from 'urql';

type addToCartActionResult = {
    data: TypeAddToCartMutation | null;
    error: CombinedError | undefined;
};

export const addToCartAction = async (variables: TypeAddToCartMutationVariables): Promise<addToCartActionResult> => {
    const response = await createMutation<TypeAddToCartMutation, TypeAddToCartMutationVariables>(
        AddToCartMutationDocument,
        variables,
    );

    if (response.error) {
        return {
            data: null,
            error: {
                name: response.error.name,
                message: response.error.message,
                graphQLErrors: response.error.graphQLErrors,
            },
        };
    }

    if (response.data) {
        return {
            data: response.data,
            error: undefined,
        };
    }

    return {
        data: null,
        error: undefined,
    };
};
