import { act, render, screen } from '@testing-library/react';
import { getOperationAST, parse } from 'graphql';
import { TypeCustomerUserProductReviewFragment } from 'graphql/requests/productReviews/fragments/CustomerUserProductReviewFragment.generated';
import {
    CreateProductReviewMutationDocument,
    TypeCreateProductReviewMutation,
    TypeCreateProductReviewMutationVariables,
} from 'graphql/requests/productReviews/mutations/CreateProductReviewMutation.generated';
import { useCurrentCustomerUserProductFamilyReviewsQuery } from 'graphql/requests/productReviews/queries/CurrentCustomerUserProductFamilyReviewsQuery.generated';
import { useCurrentCustomerUserProductReviewsQuery } from 'graphql/requests/productReviews/queries/CurrentCustomerUserProductReviewsQuery.generated';
import { useCurrentCustomerUserReviewedProductUuidsQuery } from 'graphql/requests/productReviews/queries/CurrentCustomerUserReviewedProductUuidsQuery.generated';
import { TypeProductReviewStatusEnum } from 'graphql/types';
// biome-ignore lint/style/noRestrictedImports: Exercise production Graphcache with a controlled network transport.
import { createClient, fetchExchange, Provider } from 'urql';
import { cache } from 'urql/cache/cacheExchange';
import { dedupExchange } from 'urql/dedupExchange';
import { expect, test } from 'vitest';

const review = {
    __typename: 'ProductReview',
    uuid: 'new-review',
    productUuid: 'reviewed-product',
    productName: 'Reviewed product',
    reviewerName: 'Test Customer',
    rating: 5,
    text: 'New review',
    createdAt: '2026-01-01T00:00:00+00:00',
    isVerifiedPurchase: true,
    responseText: null,
    responseCreatedAt: null,
    images: [],
    status: TypeProductReviewStatusEnum.Pending,
    rejectionReason: null,
    rejectedImagesCount: 0,
    product: null,
} satisfies TypeCustomerUserProductReviewFragment;

const Reviews = () => {
    const [{ data: account }] = useCurrentCustomerUserProductReviewsQuery({ variables: { first: 10 } });
    const [{ data: family }] = useCurrentCustomerUserProductFamilyReviewsQuery({
        variables: { first: 50, productUuid: 'reviewed-product' },
    });
    const [{ data: reviewedProducts }] = useCurrentCustomerUserReviewedProductUuidsQuery({ variables: { first: 50 } });

    return (
        <>
            {account && <p>Account: {account.currentCustomerUserProductReviews.totalCount}</p>}
            {family && (
                <p>
                    Family:{' '}
                    {family.currentCustomerUserProductReviews.edges?.map((edge) => edge?.node?.text).join(',') ||
                        'empty'}
                </p>
            )}
            {reviewedProducts && (
                <p>
                    Reviewed:{' '}
                    {reviewedProducts.currentCustomerUserProductReviews.edges
                        ?.map((edge) => edge?.node?.productUuid)
                        .join(',') || 'empty'}
                </p>
            )}
        </>
    );
};

test('refreshes all active review selections after an identity-only mutation response', async () => {
    let created = false;
    const operations: string[] = [];
    const client = createClient({
        url: 'http://localhost/graphql',
        exchanges: [dedupExchange, cache, fetchExchange],
        preferGetMethod: false,
        fetch: async (_url, options) => {
            const request = JSON.parse(String(options?.body));
            const operation = getOperationAST(parse(request.query))!.name!.value;
            operations.push(operation);

            if (operation === 'CreateProductReviewMutation') {
                created = true;

                return Response.json({
                    data: { CreateProductReview: { __typename: 'ProductReview', uuid: review.uuid } },
                });
            }

            if (!operation.startsWith('CurrentCustomerUser')) {
                throw new Error(`Unexpected operation: ${operation}`);
            }

            const node =
                operation === 'CurrentCustomerUserReviewedProductUuidsQuery'
                    ? { __typename: review.__typename, uuid: review.uuid, productUuid: review.productUuid }
                    : operation === 'CurrentCustomerUserProductFamilyReviewsQuery'
                      ? { ...review, product: undefined, rejectionReason: undefined, rejectedImagesCount: undefined }
                      : review;

            return Response.json({
                data: {
                    __typename: 'Query',
                    currentCustomerUserProductReviews: {
                        __typename: 'ProductReviewConnection',
                        ...(operation === 'CurrentCustomerUserProductReviewsQuery' && {
                            totalCount: created ? 1 : 0,
                            pageInfo: {
                                __typename: 'PageInfo',
                                hasNextPage: false,
                                hasPreviousPage: false,
                                endCursor: created ? 'first-review' : null,
                            },
                        }),
                        edges: created ? [{ __typename: 'ProductReviewEdge', cursor: 'first-review', node }] : [],
                    },
                },
            });
        },
    });
    render(
        <Provider value={client}>
            <Reviews />
        </Provider>,
    );
    expect(await screen.findByText('Account: 0')).toBeInTheDocument();
    expect(await screen.findByText('Family: empty')).toBeInTheDocument();
    expect(await screen.findByText('Reviewed: empty')).toBeInTheDocument();

    await act(async () => {
        const result = await client
            .mutation<TypeCreateProductReviewMutation, TypeCreateProductReviewMutationVariables>(
                CreateProductReviewMutationDocument,
                {
                    input: {
                        productUuid: review.productUuid,
                        rating: 5,
                        text: review.text,
                        isAnonymous: false,
                        images: [],
                    },
                },
            )
            .toPromise();
        expect(result.error).toBeUndefined();
        expect(result.data?.CreateProductReview.uuid).toBe(review.uuid);
    });

    expect(await screen.findByText('Account: 1')).toBeInTheDocument();
    expect(await screen.findByText('Family: New review')).toBeInTheDocument();
    expect(await screen.findByText('Reviewed: reviewed-product')).toBeInTheDocument();
    for (const query of [
        'CurrentCustomerUserProductReviewsQuery',
        'CurrentCustomerUserProductFamilyReviewsQuery',
        'CurrentCustomerUserReviewedProductUuidsQuery',
    ]) {
        expect(operations.filter((operation) => operation === query)).toHaveLength(2);
    }
    expect(operations.filter((operation) => operation === 'CreateProductReviewMutation')).toHaveLength(1);
});
