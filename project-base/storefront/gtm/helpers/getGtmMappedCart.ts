import { CartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { mapGtmCartItemType } from 'gtm/mappers/mapGtmCartItemType';
import { GtmCartInfoType } from 'gtm/types/objects';
import { DomainConfigType } from 'helpers/domain/domainConfig';
import { getStringWithoutLeadingSlash } from 'helpers/parsing/stringWIthoutSlash';
import { getInternationalizedStaticUrls } from 'helpers/staticUrls/getInternationalizedStaticUrls';

export const getGtmMappedCart = (
    cart: CartFragment,
    promoCode: string | null,
    isUserLoggedIn: boolean,
    domain: DomainConfigType,
    cartUuid: string | null,
): GtmCartInfoType => {
    const products = cart.items.length
        ? cart.items.map((cartItem, index) => mapGtmCartItemType(cartItem, domain.url, index))
        : undefined;

    const abandonedCartUrl = getAbandonedCartUrl(isUserLoggedIn, domain, cartUuid);

    const mappedCart: GtmCartInfoType = {
        abandonedCartUrl,
        currencyCode: domain.currencyCode,
        valueWithoutVat: parseFloat(cart.totalItemsPrice.priceWithoutVat),
        valueWithVat: parseFloat(cart.totalItemsPrice.priceWithVat),
        products,
    };

    if (promoCode) {
        mappedCart.promoCodes = [promoCode];
    }

    return mappedCart;
};

const getAbandonedCartUrl = (isUserLoggedIn: boolean, domain: DomainConfigType, cartUuid: string | null) => {
    if (isUserLoggedIn) {
        const [loginRelativeUrl, cartRelativeUrl] = getInternationalizedStaticUrls(['/login', '/cart'], domain.url);

        return domain.url + getStringWithoutLeadingSlash(loginRelativeUrl) + '?r=' + cartRelativeUrl;
    }

    const [abandonedCartRelativeUrl] = getInternationalizedStaticUrls(
        [{ url: '/abandoned-cart/:cartUuid', param: cartUuid }],
        domain.url,
    );

    return domain.url + getStringWithoutLeadingSlash(abandonedCartRelativeUrl);
};
