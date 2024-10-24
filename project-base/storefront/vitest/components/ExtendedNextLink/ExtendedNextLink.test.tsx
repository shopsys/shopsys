import { render } from '@testing-library/react';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { DomainConfigProvider } from 'components/providers/DomainConfigProvider';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { CustomerUserAreaEnum } from 'types/customer';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { describe, expect, test } from 'vitest';

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
    convertimUuid: null,
};

describe('ExtendedNextLink snapshot tests', () => {
    test('render ExtendedNextLink with static type', () => {
        const component = render(
            <DomainConfigProvider domainConfig={MOCKED_DOMAIN_CONFIG}>
                <ExtendedNextLink href="/test-href">
                    <div>
                        <span>link text</span>
                    </div>
                </ExtendedNextLink>
            </DomainConfigProvider>,
        );

        expect(component).toMatchFileSnapshot('snap-1.test.tsx.snap');
    });

    test('render ExtendedNextLink with static type and `as` prop', () => {
        const component = render(
            <DomainConfigProvider domainConfig={MOCKED_DOMAIN_CONFIG}>
                <ExtendedNextLink as="/nice-test-href" href="/test-href">
                    <div>
                        <span>link text</span>
                    </div>
                </ExtendedNextLink>
            </DomainConfigProvider>,
        );

        expect(component).toMatchFileSnapshot('snap-2.test.tsx.snap');
    });

    test('render ExtendedNextLink with a friendly page type', () => {
        const component = render(
            <DomainConfigProvider domainConfig={MOCKED_DOMAIN_CONFIG}>
                <ExtendedNextLink href="/test-category" type="category">
                    <div>
                        <span>link text</span>
                    </div>
                </ExtendedNextLink>
            </DomainConfigProvider>,
        );

        expect(component).toMatchFileSnapshot('snap-3.test.tsx.snap');
    });

    test('render ExtendedNextLink with a friendly page type and URL query', () => {
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

        expect(component).toMatchFileSnapshot('snap-4.test.tsx.snap');
    });
});
