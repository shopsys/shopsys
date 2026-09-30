import { render, screen } from '@testing-library/react';
import { GrapesJsParser } from 'components/Basic/UserText/GrapesJsParser';
import type { TypeProductsByCatnums } from 'graphql/requests/products/queries/ProductsByCatnumsQuery.generated';
// biome-ignore lint/style/noRestrictedImports: An isolated exchange observes real hook requests without production networking.
import { createClient, type Exchange, type Operation, Provider } from 'urql';
import { describe, expect, test, vi } from 'vitest';
import { filter, map, pipe } from 'wonka';

vi.mock('components/Basic/UserText/UserText', () => ({
    UserText: ({ htmlContent }: { htmlContent: string }) => <div>{htmlContent}</div>,
}));

vi.mock('components/Basic/UserText/GrapesJsProducts', () => ({
    GrapesJsProducts: ({ allFetchedProducts }: { allFetchedProducts?: TypeProductsByCatnums }) => (
        <div>{allFetchedProducts?.productsByCatnums.map((product) => product.catalogNumber).join(',')}</div>
    ),
}));

const createArticle = () => {
    const operations: Operation[] = [];
    const exchange: Exchange = () => (operations$) =>
        pipe(
            operations$,
            filter((operation) => operation.kind === 'query'),
            map((operation) => {
                operations.push(operation);

                return {
                    operation,
                    stale: false,
                    hasNext: false,
                    data: {
                        productsByCatnums: (operation.variables?.catnums ?? []).map((catalogNumber: string) => ({
                            catalogNumber,
                        })),
                    },
                };
            }),
        );
    const client = createClient({ url: 'http://localhost/graphql', exchanges: [exchange] });
    const article = (text: string) => (
        <Provider value={client}>
            <GrapesJsParser text={text} />
        </Provider>
    );

    return { operations, article };
};

describe('GrapesJsParser product requests', () => {
    test('renders ordinary article text without fetching products', () => {
        const { operations, article } = createArticle();

        render(article('Article without embedded products'));

        expect(screen.getByText('Article without embedded products')).toBeInTheDocument();
        expect(operations).toEqual([]);
    });

    test('fetches embedded products and passes their data to the product block', async () => {
        const { operations, article } = createArticle();

        render(article('|||[gjc-comp-ProductList&#61;9177759,5964035]|||'));

        expect(await screen.findByText('9177759,5964035')).toBeInTheDocument();
        expect(operations.at(-1)?.variables).toEqual({ catnums: ['9177759', '5964035'] });
    });

    test('resumes fetching when products are added and pauses when they are removed', async () => {
        const { operations, article } = createArticle();
        const { rerender } = render(article('Ordinary text'));
        expect(operations).toEqual([]);

        rerender(article('|||[gjc-comp-ProductList&#61;9177759]|||'));

        expect(await screen.findByText('9177759')).toBeInTheDocument();
        expect(operations.at(-1)?.variables).toEqual({ catnums: ['9177759'] });
        const requestCountWithProducts = operations.length;

        rerender(article('Ordinary text'));

        expect(screen.getByText('Ordinary text')).toBeInTheDocument();
        expect(screen.queryByText('9177759')).not.toBeInTheDocument();
        expect(operations).toHaveLength(requestCountWithProducts);
    });
});
