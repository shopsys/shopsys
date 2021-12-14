import { CartInput } from 'types/cart';
import { GetServerSidePropsContext } from 'next';
import { hasTokenInCookie } from 'utils/Auth/TokensFromCookies';
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
        const shouldInitCart = hasTokenInCookie(ssrContext);
        const newCartInputCookie = initCartInputCookie(shouldInitCart);
        setCartInputCookie(newCartInputCookie, ssrContext);
        return newCartInputCookie;
    }

    return JSON.parse(cookies.cartInput);
};

export const initCartInputCookie = (shouldInitCart = false): CartInput => {
    return {
        cartUuid: null,
        isCartEmpty: !shouldInitCart,
        transport: null,
        payment: null,
        promoCode: null,
    };
};

const setCartInputCookie = (cookieContent: CartInput, ssrContext?: GetServerSidePropsContext) => {
    nookies.set(ssrContext, 'cartInput', JSON.stringify(cookieContent), { path: '/' });
};
