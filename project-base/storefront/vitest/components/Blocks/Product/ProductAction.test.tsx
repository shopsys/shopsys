import { render, screen } from '@testing-library/react';
import { PRODUCT_VARIANTS_ID, ProductAction } from 'components/Blocks/Product/ProductAction';
import type { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import type { ReactNode } from 'react';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Forms/Button/LinkButton', () => ({
    LinkButton: ({
        'aria-label': ariaLabel,
        children,
        href,
    }: {
        'aria-label': string;
        children: ReactNode;
        href: string;
    }) => (
        <a aria-label={ariaLabel} href={href}>
            {children}
        </a>
    ),
}));

vi.mock('components/providers/AuthorizationProvider', () => ({
    useAuthorization: () => ({ canCreateOrder: true }),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string, options?: { productName?: string }) =>
            options?.productName ? key.replace('{{ productName }}', options.productName) : key,
    }),
}));

const mainVariant = {
    __typename: 'MainVariant',
    fullName: 'Television Philips [M]',
    isCurrentlyOutOfStock: false,
    isInquiryType: false,
    isMainVariant: true,
    isSellingDenied: false,
    slug: '/television-philips-m',
    uuid: 'b973951d-a6b3-4a24-ad83-00fb9f312b4c',
} as TypeListedProductFragment;

describe('ProductAction', () => {
    test('links the main variant action directly to the variants section', () => {
        render(
            <ProductAction
                gtmMessageOrigin={GtmMessageOriginType.other}
                gtmProductListName={GtmProductListNameType.other}
                listIndex={0}
                product={mainVariant}
            />,
        );

        expect(
            screen.getByRole('link', {
                name: 'Go to page with product variants of Television Philips [M]',
            }),
        ).toHaveAttribute('href', `/television-philips-m#${PRODUCT_VARIANTS_ID}`);
    });
});
