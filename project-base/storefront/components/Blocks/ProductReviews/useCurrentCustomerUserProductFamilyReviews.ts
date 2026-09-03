import { useCurrentCustomerUserQuery } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { TypeCustomerUserProductReviewFragment } from 'graphql/requests/productReviews/fragments/CustomerUserProductReviewFragment.generated';
import { useCurrentCustomerUserProductReviewsQuery } from 'graphql/requests/productReviews/queries/CurrentCustomerUserProductReviewsQuery.generated';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { TypeProductReviewStatusEnum } from 'graphql/types';
import { useEffect, useState } from 'react';
import { mapConnectionEdges } from 'utils/mappers/connection';

const OWN_REVIEWS_OF_PRODUCT_LIMIT = 50;

type CurrentCustomerUserProductFamilyReviews = {
    isLoading: boolean;
    pendingOwnReviews: TypeCustomerUserProductReviewFragment[];
    reviewedProductReviewUuid: string | null;
    reviewedProductUuids: Set<string>;
};

/**
 * Reviews the logged in customer wrote for any product of the given product family
 * (the backend resolves the uuid to the whole main variant family).
 */
export const useCurrentCustomerUserProductFamilyReviews = (
    productUuid: string,
): CurrentCustomerUserProductFamilyReviews => {
    const [isMounted, setIsMounted] = useState(false);

    useEffect(() => {
        setIsMounted(true);
    }, []);

    const [{ data: currentCustomerUserData, fetching: isCurrentCustomerUserFetching }] = useCurrentCustomerUserQuery({
        pause: !isMounted,
    });
    const isUserLoggedIn = !!currentCustomerUserData?.currentCustomerUser;
    const [{ data: settingsData }] = useSettingsQuery({ requestPolicy: 'cache-only' });
    const areProductReviewsEnabled = settingsData?.settings?.productReviewsEnabled === true;

    const [{ data: currentCustomerUserProductReviewsData, fetching: areOwnReviewsFetching }] =
        useCurrentCustomerUserProductReviewsQuery({
            variables: { productUuid, first: OWN_REVIEWS_OF_PRODUCT_LIMIT },
            pause: !isMounted || !isUserLoggedIn || !areProductReviewsEnabled,
        });

    const ownReviews =
        mapConnectionEdges<TypeCustomerUserProductReviewFragment>(
            isMounted ? currentCustomerUserProductReviewsData?.currentCustomerUserProductReviews.edges : undefined,
        ) ?? [];

    return {
        isLoading: !isMounted || isCurrentCustomerUserFetching || (isUserLoggedIn && areOwnReviewsFetching),
        pendingOwnReviews: ownReviews.filter((review) => review.status === TypeProductReviewStatusEnum.Pending),
        reviewedProductReviewUuid:
            ownReviews.find((review) => review.productUuid === productUuid)?.uuid ?? ownReviews[0]?.uuid ?? null,
        reviewedProductUuids: new Set(
            ownReviews
                .map((review) => review.productUuid)
                .filter((reviewedProductUuid): reviewedProductUuid is string => reviewedProductUuid !== null),
        ),
    };
};
