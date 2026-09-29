import { TypeCompactProductFragment } from 'graphql/requests/products/fragments/CompactProductFragment.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { getGtmProductClickEvent } from 'gtm/factories/getGtmProductClickEvent';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';

export const onGtmProductClickEventHandler = (
    product: TypeCompactProductFragment,
    gtmProductListName: GtmProductListNameType,
    index: number,
    domainUrl: string,
    arePricesHidden: boolean,
): void => {
    gtmSafePushEvent(getGtmProductClickEvent(product, gtmProductListName, index, domainUrl, arePricesHidden));
};
