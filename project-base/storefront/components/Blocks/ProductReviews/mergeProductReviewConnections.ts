import { TypeProductReviewConnectionFragment } from 'graphql/requests/productReviews/fragments/ProductReviewConnectionFragment.generated';

type ProductReviewConnectionType = TypeProductReviewConnectionFragment;

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
