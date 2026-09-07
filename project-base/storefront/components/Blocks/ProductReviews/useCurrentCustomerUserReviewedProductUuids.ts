import { useCurrentCustomerUserQuery } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { TypeCustomerUserProductReviewFragment } from 'graphql/requests/productReviews/fragments/CustomerUserProductReviewFragment.generated';
import { useCurrentCustomerUserProductReviewsQuery } from 'graphql/requests/productReviews/queries/CurrentCustomerUserProductReviewsQuery.generated';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { mapConnectionEdges } from 'utils/mappers/connection';

export const CURRENT_CUSTOMER_USER_REVIEWS_LIMIT = 50;

type CurrentCustomerUserReviewedProductUuids = {
    isLoading: boolean;
    reviewedProductUuids: Set<string>;
};

export const useCurrentCustomerUserReviewedProductUuids = (): CurrentCustomerUserReviewedProductUuids => {
    const [{ data: currentCustomerUserData, fetching: isCurrentCustomerUserFetching }] = useCurrentCustomerUserQuery();
    const isUserLoggedIn = !!currentCustomerUserData?.currentCustomerUser;
    const [{ data: settingsData }] = useSettingsQuery({ requestPolicy: 'cache-only' });
    const areProductReviewsEnabled = settingsData?.settings?.productReviewsEnabled === true;

    const [{ data: currentCustomerUserProductReviewsData, fetching: areOwnReviewsFetching }] =
        useCurrentCustomerUserProductReviewsQuery({
            variables: { first: CURRENT_CUSTOMER_USER_REVIEWS_LIMIT },
            pause: !isUserLoggedIn || !areProductReviewsEnabled,
            requestPolicy: 'cache-and-network',
        });

    const ownReviews =
        mapConnectionEdges<TypeCustomerUserProductReviewFragment>(
            currentCustomerUserProductReviewsData?.currentCustomerUserProductReviews.edges ?? undefined,
        ) ?? [];

    return {
        isLoading: isCurrentCustomerUserFetching || (isUserLoggedIn && areOwnReviewsFetching),
        reviewedProductUuids: new Set(
            ownReviews
                .map((review) => review.productUuid)
                .filter((reviewedProductUuid): reviewedProductUuid is string => reviewedProductUuid !== null),
        ),
    };
};
