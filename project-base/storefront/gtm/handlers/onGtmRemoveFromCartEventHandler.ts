import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { getGtmChangeCartItemEvent } from 'gtm/factories/getGtmChangeCartItemEvent';
import { GtmCartInfoType } from 'gtm/types/objects';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { mapPriceForCalculations } from 'utils/mappers/price';

export const onGtmRemoveFromCartEventHandler = (
    removedCartItem: TypeCartItemFragment,
    currencyCode: string,
    listIndex: number,
    gtmProductListName: GtmProductListNameType,
    domainUrl: string,
    gtmCartInfo?: GtmCartInfoType | null,
): void => {
    const eventValueWithoutVat =
        mapPriceForCalculations(removedCartItem.product.price.priceWithoutVat) * removedCartItem.quantity;
    const eventValueWithVat =
        mapPriceForCalculations(removedCartItem.product.price.priceWithVat) * removedCartItem.quantity;

    gtmSafePushEvent(
        getGtmChangeCartItemEvent(
            GtmEventType.remove_from_cart,
            removedCartItem,
            listIndex,
            removedCartItem.quantity,
            currencyCode,
            eventValueWithoutVat,
            eventValueWithVat,
            gtmProductListName,
            domainUrl,
            gtmCartInfo,
        ),
    );
};
