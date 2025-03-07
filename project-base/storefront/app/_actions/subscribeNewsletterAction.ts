'use server';

import { createMutation } from 'app/_urql/urql-dto';
import {
    NewsletterSubscribeMutationDocument,
    TypeNewsletterSubscribeMutation,
    TypeNewsletterSubscribeMutationVariables,
} from 'graphql/requests/newsletterSubscription/mutations/NewsletterSubscribeMutation.ssr';
import { CombinedError } from 'urql';

type subscribeNewsletterActionResult = {
    error: CombinedError | undefined;
};

export const subscribeNewsletterAction = async (
    variables: TypeNewsletterSubscribeMutationVariables,
): Promise<subscribeNewsletterActionResult> => {
    const response = await createMutation<TypeNewsletterSubscribeMutation, TypeNewsletterSubscribeMutationVariables>(
        NewsletterSubscribeMutationDocument,
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
