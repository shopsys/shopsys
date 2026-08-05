import { TypeProductReviewsQuery } from 'graphql/requests/productReviews/queries/ProductReviewsQuery.generated';

type ProductReviewConnectionType = TypeProductReviewsQuery['productReviews'];

export const mergeProductReviewConnections = (
    currentConnection: ProductReviewConnectionType,
    nextConnection: ProductReviewConnectionType,
): ProductReviewConnectionType => {
    const existingReviewUuids = new Set(
        currentConnection.edges?.map((edge) => edge?.node?.uuid).filter((uuid): uuid is string => uuid !== undefined) ??
            [],
    );
    const nextEdgesWithoutDuplicates =
        nextConnection.edges?.filter((edge) => {
            const uuid = edge?.node?.uuid;

            return uuid === undefined || !existingReviewUuids.has(uuid);
        }) ?? [];

    return {
        ...nextConnection,
        edges: [...(currentConnection.edges ?? []), ...nextEdgesWithoutDuplicates],
    };
};
