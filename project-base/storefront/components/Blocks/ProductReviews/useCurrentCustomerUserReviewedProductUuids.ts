import { useCurrentCustomerUserQuery } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { useCurrentCustomerUserReviewedProductUuidsQuery } from 'graphql/requests/productReviews/queries/CurrentCustomerUserReviewedProductUuidsQuery.generated';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';

export const CURRENT_CUSTOMER_USER_REVIEWS_LIMIT = 50;

type CurrentCustomerUserReviewedProductUuids = {
    isLoading: boolean;
    reviewedProductUuids: Set<string>;
};

/**
 * Uuids of products the logged in customer has already reviewed (regardless of the review status).
 * Serves as a UX hint only — the duplicate review rule is enforced by the CreateProductReview mutation.
 */
export const useCurrentCustomerUserReviewedProductUuids = (): CurrentCustomerUserReviewedProductUuids => {
    const [{ data: currentCustomerUserData, fetching: isCurrentCustomerUserFetching }] = useCurrentCustomerUserQuery();
    const isUserLoggedIn = !!currentCustomerUserData?.currentCustomerUser;
    const [{ data: settingsData }] = useSettingsQuery({ requestPolicy: 'cache-only' });
    const areProductReviewsEnabled = settingsData?.settings?.productReviewsEnabled === true;

    const [{ data: currentCustomerUserReviewedProductUuidsData, fetching: areOwnReviewsFetching }] =
        useCurrentCustomerUserReviewedProductUuidsQuery({
            variables: { first: CURRENT_CUSTOMER_USER_REVIEWS_LIMIT },
            pause: !isUserLoggedIn || !areProductReviewsEnabled,
            requestPolicy: 'cache-and-network',
        });

    const edges = currentCustomerUserReviewedProductUuidsData?.currentCustomerUserProductReviews.edges ?? [];

    return {
        isLoading: isCurrentCustomerUserFetching || (isUserLoggedIn && areOwnReviewsFetching),
        reviewedProductUuids: new Set(
            edges
                .map((edge) => edge?.node?.productUuid)
                .filter((reviewedProductUuid): reviewedProductUuid is string => !!reviewedProductUuid),
        ),
    };
};
