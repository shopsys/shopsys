import { ReviewedProductVariantType } from 'components/Blocks/ProductReviews/productReviewTypes';
import { useCurrentCustomerUserProductFamilyReviews } from 'components/Blocks/ProductReviews/useCurrentCustomerUserProductFamilyReviews';
import { useOpenCreateProductReviewPopup } from 'components/Blocks/ProductReviews/useOpenCreateProductReviewPopup';
import { useProductReviewOrderPrefillQuery } from 'graphql/requests/productReviews/queries/ProductReviewOrderPrefillQuery.generated';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { useRouter } from 'next/router';
import { useEffect, useRef } from 'react';
import { UrlQueries } from 'types/urlQueries';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'utils/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import {
    WRITE_REVIEW_ORDER_HASH_QUERY_PARAMETER_NAME,
    WRITE_REVIEW_PRODUCT_QUERY_PARAMETER_NAME,
} from 'utils/queryParamNames';
import { pushQueries } from 'utils/queryParams/pushQueries';

/**
 * Opens the create review popup when the product detail page is visited via the special
 * "write a review" URL from the order detail (or a future e-mail campaign).
 */
export const useOpenReviewPopupFromUrl = (
    productUuid: string,
    productName: string,
    variants?: ReviewedProductVariantType[],
): void => {
    const router = useRouter();
    const openCreateProductReviewPopup = useOpenCreateProductReviewPopup();
    const isUserLoggedIn = useIsUserLoggedIn();
    const wasWriteReviewRequestHandledRef = useRef(false);
    const reviewedProductUuid = getStringFromUrlQuery(router.query[WRITE_REVIEW_PRODUCT_QUERY_PARAMETER_NAME]);
    const orderUrlHash = getStringFromUrlQuery(router.query[WRITE_REVIEW_ORDER_HASH_QUERY_PARAMETER_NAME]);
    const { isLoading: isReviewAvailabilityLoading, reviewedProductUuids } =
        useCurrentCustomerUserProductFamilyReviews(productUuid);
    const requestedProductUuid = reviewedProductUuid;
    const isRequestedProductOnThisPage =
        requestedProductUuid === productUuid ||
        variants?.some((variant) => variant.uuid === requestedProductUuid) === true;
    const hasAlreadyReviewedRequestedProduct = reviewedProductUuids.has(requestedProductUuid);

    const [{ data: settingsData }] = useSettingsQuery({ requestPolicy: 'cache-only' });
    const areProductReviewsEnabled = settingsData?.settings?.productReviewsEnabled === true;

    const isGuestPrefillNeeded = !isUserLoggedIn && orderUrlHash !== '';
    const [{ data: orderPrefillData, fetching: isOrderPrefillFetching }] = useProductReviewOrderPrefillQuery({
        variables: { urlHash: orderUrlHash },
        pause: !areProductReviewsEnabled || reviewedProductUuid === '' || !isGuestPrefillNeeded,
    });

    useEffect(() => {
        if (
            wasWriteReviewRequestHandledRef.current ||
            !areProductReviewsEnabled ||
            requestedProductUuid === '' ||
            isReviewAvailabilityLoading ||
            (isGuestPrefillNeeded && isOrderPrefillFetching)
        ) {
            return;
        }

        wasWriteReviewRequestHandledRef.current = true;
        // drop the write-review parameters from the URL so closing the popup and refreshing does not reopen it
        const queryWithoutWriteReviewParameters: UrlQueries = {
            ...(getQueryWithoutSlugTypeParameterFromParsedUrlQuery(router.query) as UrlQueries),
            [WRITE_REVIEW_PRODUCT_QUERY_PARAMETER_NAME]: undefined,
            [WRITE_REVIEW_ORDER_HASH_QUERY_PARAMETER_NAME]: undefined,
        };
        pushQueries(router, queryWithoutWriteReviewParameters);

        if (!isRequestedProductOnThisPage || hasAlreadyReviewedRequestedProduct) {
            return;
        }

        const prefillOrder = orderPrefillData?.order;

        void openCreateProductReviewPopup({
            orderUrlHash: orderUrlHash || undefined,
            productName,
            variants,
            productUuid: requestedProductUuid,
            guestPrefill: prefillOrder
                ? {
                      firstName: prefillOrder.firstName ?? '',
                      lastName: prefillOrder.lastName ?? '',
                      email: prefillOrder.email,
                  }
                : undefined,
        });
    }, [
        areProductReviewsEnabled,
        hasAlreadyReviewedRequestedProduct,
        isGuestPrefillNeeded,
        isOrderPrefillFetching,
        isRequestedProductOnThisPage,
        isReviewAvailabilityLoading,
        orderPrefillData,
        orderUrlHash,
        productName,
        requestedProductUuid,
        openCreateProductReviewPopup,
        variants,
    ]);
};
