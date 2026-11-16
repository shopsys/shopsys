import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { getGtmChangeCartItemEvent } from 'gtm/factories/getGtmChangeCartItemEvent';
import { GtmCartInfoType } from 'gtm/types/objects';
import { getGtmPriceBasedOnVisibility } from 'gtm/utils/getGtmPriceBasedOnVisibility';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';

export const onGtmRestoreCartGiftEventHandler = (
    restoredGiftCartItem: TypeCartItemFragment,
    currencyCode: string,
    listIndex: number | undefined,
    gtmProductListName: GtmProductListNameType,
    domainUrl: string,
    arePricesHidden: boolean,
    gtmCartInfo?: GtmCartInfoType | null,
): void => {
    const unitPriceWithoutVat = getGtmPriceBasedOnVisibility(restoredGiftCartItem.product.giftPrice.priceWithoutVat);
    const unitPriceWithVat = getGtmPriceBasedOnVisibility(restoredGiftCartItem.product.giftPrice.priceWithVat);

    gtmSafePushEvent(
        getGtmChangeCartItemEvent(
            GtmEventType.add_to_cart,
            restoredGiftCartItem,
            listIndex,
            restoredGiftCartItem.quantity,
            currencyCode,
            unitPriceWithoutVat === null ? null : unitPriceWithoutVat * restoredGiftCartItem.quantity,
            unitPriceWithVat === null ? null : unitPriceWithVat * restoredGiftCartItem.quantity,
            gtmProductListName,
            domainUrl,
            arePricesHidden,
            [],
            gtmCartInfo,
        ),
    );
};
