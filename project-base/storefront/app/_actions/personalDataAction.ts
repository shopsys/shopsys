'use server';

import { createMutation } from 'app/_urql/urql-dto';
import 'graphql/requests/newsletterSubscription/mutations/NewsletterSubscribeMutation.ssr';
import {
    PersonalDataRequestMutationDocument,
    TypePersonalDataRequestMutation,
    TypePersonalDataRequestMutationVariables,
} from 'graphql/requests/personalData/mutations/PersonalDataRequestMutation.ssr';
import { CombinedError } from 'urql';

type ExportPersonalDataActionResult = {
    error: CombinedError | undefined;
    data: TypePersonalDataRequestMutation | undefined;
};

export const personalDataAction = async (
    variables: TypePersonalDataRequestMutationVariables,
): Promise<ExportPersonalDataActionResult> => {
    const response = await createMutation<TypePersonalDataRequestMutation, TypePersonalDataRequestMutationVariables>(
        PersonalDataRequestMutationDocument,
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
        error: undefined,
        data: response.data,
    };
};
