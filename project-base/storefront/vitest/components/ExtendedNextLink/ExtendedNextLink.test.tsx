import { render } from '@testing-library/react';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { describe, expect, test } from 'vitest';

describe('ExtendedNextLink snapshot tests', () => {
    test('render ExtendedNextLink with static type', () => {
        const component = render(
            <ExtendedNextLink href="/test-href">
                <div>
                    <span>link text</span>
                </div>
            </ExtendedNextLink>,
        );

        expect(component).toMatchFileSnapshot('snap-1.test.tsx.snap');
    });

    test('render ExtendedNextLink with static type and `as` prop', () => {
        const component = render(
            <ExtendedNextLink as="/nice-test-href" href="/test-href">
                <div>
                    <span>link text</span>
                </div>
            </ExtendedNextLink>,
        );

        expect(component).toMatchFileSnapshot('snap-2.test.tsx.snap');
    });

    test('render ExtendedNextLink with a friendly page type', () => {
        const component = render(
            <ExtendedNextLink href="/test-category" type="category">
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
                href="/test-category"
                queryParams={{ sort: TypeProductOrderingModeEnum.PriceAsc }}
                type="category"
            >
                <div>
                    <span>link text</span>
                </div>
            </ExtendedNextLink>,
        );

        expect(component).toMatchFileSnapshot('snap-4.test.tsx.snap');
    });
});
