import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { getGtmChangeProductListItemEvent } from 'gtm/factories/getGtmChangeProductListItemEvent';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { ProductInterfaceType } from 'types/product';
import { DomainConfigType } from 'utils/domain/domainConfig';

export const onGtmChangeProductListItemEventHandler = (
    event:
        | GtmEventType.add_to_wishlist
        | GtmEventType.remove_from_wishlist
        | GtmEventType.add_to_comparison
        | GtmEventType.remove_from_comparison,
    product: ProductInterfaceType,
    domainConfig: DomainConfigType,
    listIndex: number | undefined,
    gtmProductListName: GtmProductListNameType,
    arePricesHidden: boolean,
): void => {
    gtmSafePushEvent(
        getGtmChangeProductListItemEvent(
            event,
            product,
            listIndex,
            domainConfig.currencyCode,
            gtmProductListName,
            domainConfig.url,
            arePricesHidden,
        ),
    );
};
