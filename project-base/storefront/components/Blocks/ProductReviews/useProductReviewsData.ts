import { mergeProductReviewConnections } from 'components/Blocks/ProductReviews/mergeProductReviewConnections';
import { TypeProductReviewFragment } from 'graphql/requests/productReviews/fragments/ProductReviewFragment.generated';
import {
    ProductReviewsQueryDocument,
    TypeProductReviewsQuery,
    TypeProductReviewsQueryVariables,
} from 'graphql/requests/productReviews/queries/ProductReviewsQuery.generated';
import { TypeProductReviewOrderingModeEnum } from 'graphql/types';
import { useCallback, useEffect, useRef, useState } from 'react';
import { useClient, useQuery } from 'urql';
import { mapConnectionEdges } from 'utils/mappers/connection';

const PRODUCT_REVIEWS_INITIAL_PAGE_SIZE = 5;
export const PRODUCT_REVIEWS_LOAD_MORE_PAGE_SIZE = 10;

export const useProductReviewsData = (productUuid: string) => {
    const client = useClient();
    const [orderingMode, setOrderingMode] = useState(TypeProductReviewOrderingModeEnum.Newest);
    const [isLoadingMoreReviews, setIsLoadingMoreReviews] = useState(false);

    const queryVariables: TypeProductReviewsQueryVariables = {
        productUuid,
        orderingMode,
        first: PRODUCT_REVIEWS_INITIAL_PAGE_SIZE,
        after: null,
    };
    const queryKey = JSON.stringify(queryVariables);
    const queryKeyRef = useRef(queryKey);

    const [{ data, fetching: areProductReviewsFetching }] = useQuery<
        TypeProductReviewsQuery,
        TypeProductReviewsQueryVariables
    >({
        query: ProductReviewsQueryDocument,
        variables: queryVariables,
    });

    const [productReviews, setProductReviews] = useState<TypeProductReviewsQuery['productReviews'] | null>(
        data?.productReviews ?? null,
    );

    useEffect(() => {
        queryKeyRef.current = queryKey;
        setIsLoadingMoreReviews(false);
    }, [queryKey]);

    useEffect(() => {
        if (data?.productReviews) {
            setProductReviews(data.productReviews);
        }
    }, [data]);

    const reviews = mapConnectionEdges<TypeProductReviewFragment>(productReviews?.edges ?? undefined) ?? [];
    const hasMoreReviews = (productReviews?.totalCount ?? 0) > reviews.length;

    const loadMoreReviews = useCallback(async () => {
        if (
            productReviews === null ||
            !hasMoreReviews ||
            productReviews.pageInfo.endCursor === null ||
            areProductReviewsFetching ||
            isLoadingMoreReviews
        ) {
            return;
        }

        const requestedQueryKey = queryKey;
        setIsLoadingMoreReviews(true);

        try {
            const reviewsResponse = await client
                .query<TypeProductReviewsQuery, TypeProductReviewsQueryVariables>(ProductReviewsQueryDocument, {
                    ...queryVariables,
                    first: PRODUCT_REVIEWS_LOAD_MORE_PAGE_SIZE,
                    after: productReviews.pageInfo.endCursor,
                })
                .toPromise();

            const nextProductReviews = reviewsResponse.data?.productReviews;

            if (queryKeyRef.current !== requestedQueryKey || !nextProductReviews) {
                return;
            }

            setProductReviews((currentReviews) =>
                currentReviews === null
                    ? nextProductReviews
                    : mergeProductReviewConnections(currentReviews, nextProductReviews),
            );
        } finally {
            if (queryKeyRef.current === requestedQueryKey) {
                setIsLoadingMoreReviews(false);
            }
        }
    }, [client, areProductReviewsFetching, hasMoreReviews, isLoadingMoreReviews, productReviews, queryKey]);

    return {
        activeOrderingMode: productReviews?.orderingMode ?? orderingMode,
        areProductReviewsFetching: areProductReviewsFetching && productReviews === null,
        hasMoreReviews,
        isLoadingMoreReviews,
        loadMoreReviews,
        reviews,
        setOrderingMode,
        summary: productReviews?.summary,
        totalCount: productReviews?.totalCount ?? 0,
    };
};
