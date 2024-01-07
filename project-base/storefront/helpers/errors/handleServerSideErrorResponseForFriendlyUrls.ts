import { GraphQLError } from 'graphql';
import { IncomingMessage, ServerResponse } from 'http';

export const handleServerSideErrorResponseForFriendlyUrls = (
    graphQLErrors: GraphQLError[] | undefined,
    data: unknown,
    res: ServerResponse<IncomingMessage>,
) => {
    if (graphQLErrors?.[0]?.extensions.code === 500) {
        throw new Error('Internal Server Error');
    }

    if (!data && !(res.statusCode === 503)) {
        return {
            notFound: true as const,
        };
    }

    return null;
};
