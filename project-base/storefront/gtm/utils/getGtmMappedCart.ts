import { getGtmPriceBasedOnVisibility } from './getGtmPriceBasedOnVisibility';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypePromoCode } from 'graphql/types';
import { mapGtmCartItemType } from 'gtm/mappers/mapGtmCartItemType';
import { GtmCartInfoType } from 'gtm/types/objects';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { getStringWithoutLeadingSlash } from 'utils/parsing/stringWIthoutSlash';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const getGtmMappedCart = (
    cart: TypeCartFragment,
    promoCodes: TypePromoCode[],
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
        valueWithoutVat: getGtmPriceBasedOnVisibility(cart.totalItemsPrice.priceWithoutVat),
        valueWithVat: getGtmPriceBasedOnVisibility(cart.totalItemsPrice.priceWithVat),
        products,
    };

    mappedCart.promoCodes = promoCodes.map(({ code }) => code);

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
