import { CartInput } from 'connectors/cart/types';
import { GetServerSidePropsContext } from 'next';
import nookies from 'nookies';

export const updateCartInputCookie = (updatedData: CartInput, ssrContext?: GetServerSidePropsContext): CartInput => {
    const cookies = nookies.get(ssrContext);
    const cartInputCookie = !('cartInput' in cookies) ? initCartInputCookie() : JSON.parse(cookies.cartInput);
    const updatedCartInputCookie = {
        ...cartInputCookie,
        ...updatedData,
    };

    setCartInputCookie(updatedCartInputCookie, ssrContext);
    return updatedCartInputCookie;
};

export const getCartInputCookie = (ssrContext?: GetServerSidePropsContext): CartInput => {
    const cookies = nookies.get(ssrContext);
    if (!('cartInput' in cookies)) {
        const newCartInputCookie = initCartInputCookie();
        setCartInputCookie(newCartInputCookie, ssrContext);
        return newCartInputCookie;
    }

    return JSON.parse(cookies.cartInput);
};

export const initCartInputCookie = (): CartInput => {
    return {
        cartUuid: null,
        transport: null,
        payment: null,
        promoCode: null,
    };
};

const setCartInputCookie = (cookieContent: CartInput, ssrContext?: GetServerSidePropsContext) => {
    nookies.set(ssrContext, 'cartInput', JSON.stringify(cookieContent), { path: '/' });
};
