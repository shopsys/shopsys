import { render, screen } from '@testing-library/react';
import { MenuIconic } from 'components/Layout/Header/MenuIconic/MenuIconic';
import type { ReactNode } from 'react';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const { useProductListCountMock } = vi.hoisted(() => ({
    useProductListCountMock: vi.fn(),
}));

vi.mock('components/Basic/Tooltip/Tooltip', () => ({
    Tooltip: ({ children }: { children: ReactNode }) => children,
}));

vi.mock('components/Layout/Header/MenuIconic/MenuIconicItemUserUnauthenticated', () => ({
    MenuIconicItemUserUnauthenticated: () => <button type="button">Log in</button>,
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ url: 'https://example.com' }),
}));

vi.mock('utils/auth/useIsUserLoggedIn', () => ({
    useIsUserLoggedIn: () => false,
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

vi.mock('utils/productLists/useProductListCount', () => ({
    useProductListCount: useProductListCountMock,
}));

vi.mock('utils/staticUrls/getInternationalizedStaticUrls', () => ({
    getInternationalizedStaticUrls: (paths: string[]) => paths,
}));

describe('MenuIconic', () => {
    beforeEach(() => {
        useProductListCountMock.mockReturnValue(0);
    });

    test('keeps compact icon links accessible by name', () => {
        render(<MenuIconic isCompact />);

        expect(screen.getByRole('link', { name: 'Go to stores page' })).toHaveAttribute('href', '/stores');
        expect(screen.getByRole('link', { name: 'Go to comparison page' })).toHaveAttribute(
            'href',
            '/product-comparison',
        );
        expect(screen.getByRole('link', { name: 'Go to wishlist page' })).toHaveAttribute('href', '/wishlist');
    });
});
