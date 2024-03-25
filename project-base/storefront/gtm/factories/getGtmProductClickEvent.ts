import { ListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { SimpleProductFragment } from 'graphql/requests/products/fragments/SimpleProductFragment.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { mapGtmListedProductType } from 'gtm/mappers/mapGtmListedProductType';
import { GtmProductClickEventType } from 'gtm/types/events';

export const getGtmProductClickEvent = (
    product: ListedProductFragment | SimpleProductFragment,
    gtmProductListName: GtmProductListNameType,
    listIndex: number,
    domainUrl: string,
): GtmProductClickEventType => ({
    event: GtmEventType.product_click,
    ecommerce: {
        listName: gtmProductListName,
        products: [mapGtmListedProductType(product, listIndex, domainUrl)],
    },
    _clear: true,
});
