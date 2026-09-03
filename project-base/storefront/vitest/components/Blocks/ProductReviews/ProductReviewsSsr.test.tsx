import { fireEvent, render, screen, waitFor } from '@testing-library/react';
import { TooltipProvider } from 'components/Basic/Tooltip/Tooltip';
import { ProductDetailInfo } from 'components/Pages/ProductDetail/ProductDetailInfo';
import { ProductDetailReviewsSection } from 'components/Pages/ProductDetail/ProductDetailSections/ProductDetailReviewsSection';
import { DomainConfigProvider } from 'components/providers/DomainConfigProvider';
import { TypeProductReviewConnectionFragment } from 'graphql/requests/productReviews/fragments/ProductReviewConnectionFragment.generated';
import {
    TypeProductReviewsQuery,
    TypeProductReviewsQueryVariables,
} from 'graphql/requests/productReviews/queries/ProductReviewsQuery.generated';
import { TypeProductReviewOrderingModeEnum } from 'graphql/types';
import { renderToString } from 'react-dom/server';
// biome-ignore lint/style/noRestrictedImports: Isolated exchanges exercise the SSR cache without production networking.
import { Client, createClient, Exchange, getOperationName, Operation, Provider, ssrExchange } from 'urql';
import { describe, expect, test, vi } from 'vitest';
import { defaultTestDomainConfig } from 'vitest/helpers/mockPublicConfig';
import { filter, fromValue, mergeMap, never, pipe } from 'wonka';

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string, values?: Record<string, unknown>) =>
            key.replace(/{{ (\w+) }}/g, (_match, name: string) => String(values?.[name] ?? name)),
    }),
}));

vi.mock('graphql/requests/settings/queries/SettingsQuery.generated', () => ({
    useSettingsQuery: () => [
        {
            data: {
                settings: { productReviewsEnabled: true, displayTimezone: 'UTC', productReviewPolicyArticleUrl: null },
            },
        },
    ],
}));

const createReviewConnection = ({
    productUuid,
    first = 5,
    after,
    orderingMode = TypeProductReviewOrderingModeEnum.Newest,
}: TypeProductReviewsQueryVariables): TypeProductReviewConnectionFragment => {
    const offset = after ? Number(after) : 0;
    const count = Math.min(first ?? 5, 16 - offset);

    return {
        totalCount: 16,
        orderingMode: orderingMode ?? TypeProductReviewOrderingModeEnum.Newest,
        summary: { __typename: 'ProductReviewsSummary', averageRating: 4.5, totalCount: 16, ratingCounts: [] },
        pageInfo: {
            __typename: 'PageInfo',
            endCursor: String(offset + count),
            hasNextPage: offset + count < 16,
            hasPreviousPage: offset > 0,
        },
        edges: Array.from({ length: count }, (_, index) => ({
            cursor: String(offset + index + 1),
            node: {
                __typename: 'ProductReview',
                uuid: `${productUuid}-${orderingMode}-${offset + index + 1}`,
                productName: 'Product',
                reviewerName: `Reviewer ${offset + index + 1}`,
                rating: 5,
                text: `${productUuid} ${orderingMode} review ${offset + index + 1}`,
                createdAt: '2026-08-25T08:00:00+00:00',
                isVerifiedPurchase: false,
                responseText: null,
                responseCreatedAt: null,
                images: [],
            },
        })),
    };
};

const createReviews = (variables: TypeProductReviewsQueryVariables): TypeProductReviewsQuery => ({
    product: { reviews: createReviewConnection(variables) },
});

const getQueryName = (operation: Operation) =>
    'definitions' in operation.query ? getOperationName(operation.query) : undefined;

const createTestClient = (isClient: boolean) => {
    const operations: Operation[] = [];
    const ssr = ssrExchange({ isClient });
    const exchange: Exchange = () => (operations$) =>
        pipe(
            operations$,
            filter((operation) => operation.kind === 'query'),
            mergeMap((operation) => {
                operations.push(operation);

                const name = getQueryName(operation);
                if (name === 'CurrentCustomerUserProductReviewsQuery') {
                    return never;
                }

                return fromValue({
                    operation,
                    data:
                        name === 'ProductReviewsQuery'
                            ? createReviews(operation.variables as TypeProductReviewsQueryVariables)
                            : { currentCustomerUser: { __typename: 'CurrentRegularCustomerUser', uuid: 'customer' } },
                });
            }),
        );

    const client = createClient({ url: 'http://localhost/graphql', exchanges: [ssr, exchange] });

    return { client, operations };
};

const firstProductReviews = createReviewConnection({ productUuid: 'first-product' });
const secondProductReviews = createReviewConnection({ productUuid: 'second-product' });

const productReviewsPage = (client: Client, productUuid = 'first-product') => (
    <Provider value={client}>
        <DomainConfigProvider domainConfig={defaultTestDomainConfig}>
            <TooltipProvider>
                <ProductDetailInfo catalogNumber="123" reviewsSummary={firstProductReviews.summary} />
                <ProductDetailReviewsSection
                    initialProductReviews={productUuid === 'first-product' ? firstProductReviews : secondProductReviews}
                    productName="Product"
                    productUuid={productUuid}
                    sectionRef={{ current: null }}
                />
            </TooltipProvider>
        </DomainConfigProvider>
    </Provider>
);

describe('product reviews SSR', () => {
    test('renders the first five product reviews and the rating badge without additional queries', () => {
        const { client, operations } = createTestClient(false);

        const html = renderToString(productReviewsPage(client));
        const document = new DOMParser().parseFromString(html, 'text/html');

        expect(document.querySelectorAll('#reviews p.whitespace-pre-line')).toHaveLength(5);
        expect(document.body.textContent).toContain('first-product NEWEST review 5');
        expect(document.body.textContent).not.toContain('first-product NEWEST review 6');
        const ratingLink = document.querySelector('a[href="#reviews"]');
        expect(ratingLink?.textContent).toBe('16 reviews');
        expect(ratingLink?.parentElement?.textContent).toContain('4.5');
        expect(ratingLink?.parentElement?.querySelectorAll('svg').length).toBeGreaterThanOrEqual(5);
        expect(document.body.textContent).not.toContain('Write a review');
        expect(document.querySelectorAll('#reviews [role="status"]')).toHaveLength(1);
        expect(operations).toEqual([]);
    });

    test('hydrates without refetching and keeps public reviews visible while customer reviews load', async () => {
        const server = createTestClient(false);
        const container = document.createElement('div');
        container.innerHTML = renderToString(productReviewsPage(server.client));
        document.body.appendChild(container);
        const browser = createTestClient(true);
        const onRecoverableError = vi.fn();

        const { rerender } = render(productReviewsPage(browser.client), {
            container,
            hydrate: true,
            onRecoverableError,
        });

        expect(screen.getByText('first-product NEWEST review 5')).toBeInTheDocument();
        expect(screen.queryByRole('button', { name: 'Write a review' })).not.toBeInTheDocument();
        expect(screen.getByRole('status')).toBeInTheDocument();
        expect(new Set(browser.operations.map(getQueryName))).toEqual(
            new Set(['CurrentCustomerUserQuery', 'CurrentCustomerUserProductReviewsQuery']),
        );
        expect(onRecoverableError).not.toHaveBeenCalled();

        fireEvent.click(screen.getByRole('button', { name: 'Show 10 more reviews' }));

        await waitFor(() => expect(container.querySelectorAll('#reviews p.whitespace-pre-line')).toHaveLength(15));
        expect(browser.operations.at(-1)?.variables).toMatchObject({ first: 10, after: '5' });

        fireEvent.click(screen.getAllByRole('button', { name: 'Highest rated' })[0]);

        await waitFor(() => expect(screen.getByText('first-product HIGHEST_RATING review 1')).toBeInTheDocument());
        expect(container.querySelectorAll('#reviews p.whitespace-pre-line')).toHaveLength(5);

        fireEvent.click(screen.getAllByRole('button', { name: 'Newest' })[0]);

        await waitFor(() => expect(screen.getByText('first-product NEWEST review 1')).toBeInTheDocument());
        expect(container.querySelectorAll('#reviews p.whitespace-pre-line')).toHaveLength(5);

        rerender(productReviewsPage(browser.client, 'second-product'));

        await waitFor(() => expect(screen.getByText('second-product NEWEST review 1')).toBeInTheDocument());
        expect(screen.queryByText(/first-product .* review/)).not.toBeInTheDocument();
    });
});
