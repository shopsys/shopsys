import { ListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { SimpleProductFragment } from 'graphql/requests/products/fragments/SimpleProductFragment.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { getGtmProductClickEvent } from 'gtm/factories/getGtmProductClickEvent';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';

export const onGtmProductClickEventHandler = (
    product: ListedProductFragment | SimpleProductFragment,
    gtmProductListName: GtmProductListNameType,
    index: number,
    domainUrl: string,
): void => {
    gtmSafePushEvent(getGtmProductClickEvent(product, gtmProductListName, index, domainUrl));
};
