import { render, waitFor } from '@testing-library/react';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { DomainConfigProvider } from 'components/providers/DomainConfigProvider';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { describe, expect, test, vi } from 'vitest';
import { defaultTestDomainConfig } from 'vitest/helpers/mockPublicConfig';

vi.mock('next/router', () => ({
    useRouter: () => ({
        push: vi.fn(),
        prefetch: vi.fn().mockResolvedValue(undefined),
        pathname: '/',
        query: {},
        asPath: '/',
    }),
}));

describe('ExtendedNextLink snapshot tests', () => {
    test('render ExtendedNextLink with static type', async () => {
        const component = render(
            <DomainConfigProvider domainConfig={defaultTestDomainConfig}>
                <ExtendedNextLink href="/test-href">
                    <div>
                        <span>link text</span>
                    </div>
                </ExtendedNextLink>
            </DomainConfigProvider>,
        );

        await waitFor(() => {
            expect(component.container).toBeInTheDocument();
        });

        expect(component).toMatchFileSnapshot('snap-1.test.tsx.snap');
    });

    test('render ExtendedNextLink with static type and `as` prop', async () => {
        const component = render(
            <DomainConfigProvider domainConfig={defaultTestDomainConfig}>
                <ExtendedNextLink as="/nice-test-href" href="/test-href">
                    <div>
                        <span>link text</span>
                    </div>
                </ExtendedNextLink>
            </DomainConfigProvider>,
        );

        await waitFor(() => {
            expect(component.container).toBeInTheDocument();
        });

        expect(component).toMatchFileSnapshot('snap-2.test.tsx.snap');
    });

    test('render ExtendedNextLink with a friendly page type', async () => {
        const component = render(
            <DomainConfigProvider domainConfig={defaultTestDomainConfig}>
                <ExtendedNextLink href="/test-category" type="category">
                    <div>
                        <span>link text</span>
                    </div>
                </ExtendedNextLink>
            </DomainConfigProvider>,
        );

        await waitFor(() => {
            expect(component.container).toBeInTheDocument();
        });

        expect(component).toMatchFileSnapshot('snap-3.test.tsx.snap');
    });

    test('render ExtendedNextLink with a friendly page type and URL query', async () => {
        const component = render(
            <DomainConfigProvider domainConfig={defaultTestDomainConfig}>
                <ExtendedNextLink
                    href="/test-category"
                    queryParams={{ sort: TypeProductOrderingModeEnum.PriceAsc }}
                    type="category"
                >
                    <div>
                        <span>link text</span>
                    </div>
                </ExtendedNextLink>
            </DomainConfigProvider>,
        );

        await waitFor(() => {
            expect(component.container).toBeInTheDocument();
        });

        expect(component).toMatchFileSnapshot('snap-4.test.tsx.snap');
    });
});
