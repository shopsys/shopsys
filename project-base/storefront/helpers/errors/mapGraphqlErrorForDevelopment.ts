import { GraphQLError } from 'graphql';

export const mapGraphqlErrorForDevelopment = (graphqlError: GraphQLError) => ({
    message: graphqlError.message,
    location: graphqlError.locations,
    path: graphqlError.path,
    extensions: {
        ...graphqlError.extensions,
        trace: Array.isArray(graphqlError.extensions.trace) ? graphqlError.extensions.trace.slice(0, 3) : [],
    },
});
