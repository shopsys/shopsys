import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { mapGtmProductInterface } from 'gtm/mappers/mapGtmProductInterface';
import { GtmChangeProductListItemEventType } from 'gtm/types/events';
import { GtmCartItemType } from 'gtm/types/objects';
import { getGtmPriceBasedOnVisibility } from 'gtm/utils/getGtmPriceBasedOnVisibility';
import { ProductInterfaceType } from 'types/product';

export const getGtmChangeProductListItemEvent = (
    event:
        | GtmEventType.add_to_wishlist
        | GtmEventType.remove_from_wishlist
        | GtmEventType.add_to_comparison
        | GtmEventType.remove_from_comparison,
    product: ProductInterfaceType,
    listIndex: number | undefined,
    currencyCodeCode: string,
    gtmProductListName: GtmProductListNameType,
    domainUrl: string,
    arePricesHidden: boolean,
): GtmChangeProductListItemEventType => ({
    event,
    ecommerce: {
        listName: gtmProductListName,
        currencyCode: currencyCodeCode,
        valueWithoutVat: getGtmPriceBasedOnVisibility(product.price.priceWithoutVat),
        valueWithVat: getGtmPriceBasedOnVisibility(product.price.priceWithVat),
        products: [mapGtmProductListItemType(product, domainUrl, listIndex)],
        arePricesHidden,
    },
    _clear: true,
});

const mapGtmProductListItemType = (
    product: ProductInterfaceType,
    domainUrl: string,
    listIndex?: number,
): GtmCartItemType => {
    const mappedProductListItem: GtmCartItemType = {
        ...mapGtmProductInterface(product, domainUrl),
        quantity: 1,
    };

    if (listIndex !== undefined) {
        mappedProductListItem.listIndex = listIndex + 1;
    }

    return mappedProductListItem;
};
