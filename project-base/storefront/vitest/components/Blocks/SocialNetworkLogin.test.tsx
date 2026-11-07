import type { UrlObject } from 'node:url';
import { act } from '@testing-library/react';
import { SocialNetworkLogin } from 'components/Blocks/SocialNetworkLogin/SocialNetworkLogin';
import { TypeLoginTypeEnum } from 'graphql/types';
import { formatUrl } from 'next/dist/shared/lib/router/utils/format-url';
import { useEffect, useState } from 'react';
import { hydrateRoot, Root } from 'react-dom/client';
import { renderToString } from 'react-dom/server';
import { expect, test, vi } from 'vitest';

const state = vi.hoisted(() => ({
    cartUuid: null as string | null,
    productListUuids: {} as Record<string, string>,
}));
vi.mock('store/usePersistStore', () => ({
    usePersistStore: (selector: (store: typeof state) => unknown) => selector(state),
}));
vi.mock('components/Blocks/SocialNetworkLogin/SocialNetworkLoginLink', () => ({
    SocialNetworkLoginLink: ({ href }: { href: UrlObject }) => <a href={formatUrl(href)}>Google</a>,
}));

const LoginLinks = () => {
    const [isClientMounted, setIsClientMounted] = useState(false);
    useEffect(() => setIsClientMounted(true), []);
    return (
        <SocialNetworkLogin
            isClientMounted={isClientMounted}
            lastLoginType={null}
            shouldOverwriteCustomerUserCart
            socialNetworks={[TypeLoginTypeEnum.Google]}
        />
    );
};

test('hydrates social login links before adding the persisted guest cart and lists', async () => {
    const error = vi.spyOn(console, 'error').mockImplementation(() => undefined);
    const recoverableError = vi.fn();
    state.cartUuid = null;
    state.productListUuids = {};
    const serverHtml = renderToString(<LoginLinks />);
    expect(serverHtml).not.toContain('cartUuid');
    expect(serverHtml).not.toContain('productListUuids');

    state.cartUuid = 'guest-cart';
    state.productListUuids = { wishlist: 'wishlist-1', comparison: 'comparison-1' };
    const container = document.createElement('div');
    container.innerHTML = serverHtml;
    document.body.append(container);
    let root: Root | undefined;
    try {
        await act(async () => {
            root = hydrateRoot(container, <LoginLinks />, { onRecoverableError: recoverableError });
        });
        const url = new URL(container.querySelector('a')!.href);
        expect(url.pathname).toBe('/social-network/login/google');
        expect(url.searchParams.get('cartUuid')).toBe('guest-cart');
        expect(url.searchParams.get('productListUuids')).toBe('wishlist-1,comparison-1');
        expect(url.searchParams.get('shouldOverwriteCustomerUserCart')).toBe('true');
        expect(recoverableError).not.toHaveBeenCalled();
        expect(error).not.toHaveBeenCalled();
    } finally {
        await act(async () => root?.unmount());
        container.remove();
        state.cartUuid = null;
        state.productListUuids = {};
    }
});
