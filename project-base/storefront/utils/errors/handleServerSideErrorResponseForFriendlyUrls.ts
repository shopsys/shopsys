import { isWithErrorDebugging } from './isWithErrorDebugging';
import { mapGraphqlErrorForDevelopment } from './mapGraphqlErrorForDevelopment';
import { GraphQLError } from 'graphql';
import { IncomingMessage, ServerResponse } from 'http';

export const handleServerSideErrorResponseForFriendlyUrls = (
    graphQLErrors: GraphQLError[] | undefined,
    data: unknown,
    res: ServerResponse<IncomingMessage>,
) => {
    if (graphQLErrors?.some((error) => error.extensions.code === 500)) {
        if (isWithErrorDebugging) {
            throw new Error(JSON.stringify(mapGraphqlErrorForDevelopment(graphQLErrors[0])));
        }

        throw new Error('Internal Server Error');
    }

    if (!data && !(res.statusCode === 503)) {
        return {
            notFound: true as const,
        };
    }

    return null;
};
