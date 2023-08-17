import { render } from '@testing-library/react';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ProductOrderingModeEnumApi } from 'graphql/requests/types';
import { describe, expect, test } from 'vitest';

describe('ExtendedNextLink snapshot tests', () => {
    test('render ExtendedNextLink with static type', () => {
        const component = render(
            <ExtendedNextLink type="static" href="/test-href">
                <div>
                    <span>link text</span>
                </div>
            </ExtendedNextLink>,
        );

        expect(component).toMatchFileSnapshot('snap-1.test.tsx.snap');
    });

    test('render ExtendedNextLink with static type and `as` prop', () => {
        const component = render(
            <ExtendedNextLink type="static" href="/test-href" as="/nice-test-href">
                <div>
                    <span>link text</span>
                </div>
            </ExtendedNextLink>,
        );

        expect(component).toMatchFileSnapshot('snap-2.test.tsx.snap');
    });

    test('render ExtendedNextLink with a friendly page type', () => {
        const component = render(
            <ExtendedNextLink type="category" href="/test-category">
                <div>
                    <span>link text</span>
                </div>
            </ExtendedNextLink>,
        );

        expect(component).toMatchFileSnapshot('snap-3.test.tsx.snap');
    });

    test('render ExtendedNextLink with a friendly page type and URL query', () => {
        const component = render(
            <ExtendedNextLink
                type="category"
                href="/test-category"
                queryParams={{ sort: ProductOrderingModeEnumApi.PriceAscApi }}
            >
                <div>
                    <span>link text</span>
                </div>
            </ExtendedNextLink>,
        );

        expect(component).toMatchFileSnapshot('snap-4.test.tsx.snap');
    });
});
