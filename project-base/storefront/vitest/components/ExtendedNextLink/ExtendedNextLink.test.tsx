import { render, waitFor } from '@testing-library/react';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { DomainConfigProvider } from 'components/providers/DomainConfigProvider';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { CustomerUserAreaEnum } from 'types/customer';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { describe, expect, test, vi } from 'vitest';

vi.mock('next/router', () => ({
    useRouter: () => ({
        push: vi.fn(),
        prefetch: vi.fn().mockResolvedValue(undefined),
        pathname: '/',
        query: {},
        asPath: '/',
    }),
}));

const MOCKED_DOMAIN_CONFIG: DomainConfigType = {
    url: '',
    currencyCode: '',
    defaultLocale: '',
    domainId: 0,
    fallbackTimezone: '',
    isLuigisBoxActive: false,
    mapSetting: {
        latitude: 0,
        longitude: 0,
        zoom: 0,
    },
    publicGraphqlEndpoint: '',
    type: CustomerUserAreaEnum.B2C,
};

describe('ExtendedNextLink snapshot tests', () => {
    test('render ExtendedNextLink with static type', async () => {
        const component = render(
            <DomainConfigProvider domainConfig={MOCKED_DOMAIN_CONFIG}>
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
            <DomainConfigProvider domainConfig={MOCKED_DOMAIN_CONFIG}>
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
            <DomainConfigProvider domainConfig={MOCKED_DOMAIN_CONFIG}>
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
            <DomainConfigProvider domainConfig={MOCKED_DOMAIN_CONFIG}>
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
