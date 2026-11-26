import { renderHook } from '@testing-library/react';
import { DomainConfigProvider } from 'components/providers/DomainConfigProvider';
import { useCustomerBreadcrumbs } from 'utils/user/useCustomerBreadcrumbs';
import { expect, test, vi } from 'vitest';
import { defaultTestConfig } from 'vitest/helpers/mockPublicConfig';

vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));

test.each([
    [0, '/customer'],
    [1, '/zakaznik'],
] as const)('prepends the overview for domain %i to the order detail trail', (domainIndex, slug) => {
    const orders = { __typename: 'Link', name: 'Orders', slug: '/orders' } as const;
    const detail = { __typename: 'Link', name: 'O123', slug: '' } as const;
    const { result } = renderHook(() => useCustomerBreadcrumbs([orders, detail]), {
        wrapper: ({ children }) => (
            <DomainConfigProvider domainConfig={defaultTestConfig.domains[domainIndex]}>
                {children}
            </DomainConfigProvider>
        ),
    });

    expect(result.current).toEqual([{ __typename: 'Link', name: 'Account overview', slug }, orders, detail]);
});
