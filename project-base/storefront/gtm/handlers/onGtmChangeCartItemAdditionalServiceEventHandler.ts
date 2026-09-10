import { TypeAdditionalServiceFragment } from 'graphql/requests/additionalServices/fragments/AdditionalServiceFragment.generated';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { getGtmChangeCartItemAdditionalServiceEvent } from 'gtm/factories/getGtmChangeCartItemAdditionalServiceEvent';
import { mapGtmServiceCartItem } from 'gtm/mappers/mapGtmServiceCartItems';
import { getGtmMappedCart } from 'gtm/utils/getGtmMappedCart';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { DomainConfigType } from 'utils/domain/domainConfig';

export const onGtmChangeCartItemAdditionalServiceEventHandler = (
    isServiceAdded: boolean,
    additionalService: TypeAdditionalServiceFragment,
    sourceCartItem: TypeCartItemFragment,
    updatedCart: TypeCartFragment,
    gtmProductListName: GtmProductListNameType,
    domainConfig: DomainConfigType,
    isUserLoggedIn: boolean,
    arePricesHidden: boolean,
): void => {
    const additionalServiceCartItem = mapGtmServiceCartItem(
        additionalService,
        [sourceCartItem.product.id],
        sourceCartItem.quantity,
    );

    const eventValueWithoutVat =
        additionalServiceCartItem.priceWithoutVat === null
            ? null
            : additionalServiceCartItem.priceWithoutVat * sourceCartItem.quantity;
    const eventValueWithVat =
        additionalServiceCartItem.priceWithVat === null
            ? null
            : additionalServiceCartItem.priceWithVat * sourceCartItem.quantity;

    gtmSafePushEvent(
        getGtmChangeCartItemAdditionalServiceEvent(
            isServiceAdded ? GtmEventType.add_to_cart : GtmEventType.remove_from_cart,
            additionalServiceCartItem,
            gtmProductListName,
            domainConfig.currencyCode,
            eventValueWithoutVat,
            eventValueWithVat,
            arePricesHidden,
            getGtmMappedCart(updatedCart, updatedCart.promoCodes, isUserLoggedIn, domainConfig, updatedCart.uuid),
        ),
    );
};
