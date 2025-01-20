'use server';

import { createMutation } from 'app/_urql/urql-dto';
import {
    CreateInquiryMutationDocument,
    TypeCreateInquiryMutation,
    TypeCreateInquiryMutationVariables,
} from 'graphql/requests/inquiry/mutations/CreateInquiryMutation.ssr';
import { CombinedError } from 'urql';

type CreateInquiryActionResult = {
    error: CombinedError | undefined;
};

export const createInquiryAction = async (
    variables: TypeCreateInquiryMutationVariables,
): Promise<CreateInquiryActionResult> => {
    const response = await createMutation<TypeCreateInquiryMutation, TypeCreateInquiryMutationVariables>(
        CreateInquiryMutationDocument,
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
};
